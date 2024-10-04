<?php

namespace App\Services;

use App\Dtos\FileDto;
use App\Models\File;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use ZipArchive;

class PritunlGatewayService
{
    private FileService $fileService;
    private string $pritunlApiUrl;
    private string $pritunlXApiKey;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
        $this->pritunlApiUrl = env('PRITUNL_API_URL');
        $this->pritunlXApiKey = env('PRITUNL_X_API_KEY');;
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function callPaymentGateway(string $action, int $userId, int $categoryId): ResponseInterface
    {
        $client = new Client();
        return $client->post($this->pritunlApiUrl . '?action=' . $action, [
            'headers' => [
                'x-api-key' => $this->pritunlXApiKey,
            ],
            'body' => json_encode($this->createRequestParamArray($userId, $categoryId)),
            'verify' => false
        ]);
    }

    /**
     * Get or Create user and Download ovpn file and saved into the file table
     *
     * @param int $userId
     * @param int $categoryId
     * @return array
     * @throws \App\Exceptions\IllegalArgumentException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getOrCreateUserAndReturnFileIds(int $userId, int $categoryId): array
    {
        $fileIds = [];

        //post to pritunl service
        $client = new Client();
        $response = $this->callPaymentGateway('GET_OR_CREAT_USER', $userId, $categoryId);
        $fileUrl = $response->getBody()->getContents();

        $response = $client->get($fileUrl, ['verify' => false]);
        $tempZipPath = config('filesystems.disks.admin.root') . '/' . $userId . '.zip';
        file_put_contents($tempZipPath, $response->getBody());

        // Extract the ZIP file
        $zip = new ZipArchive;
        if ($zip->open($tempZipPath) === TRUE) {
            $folder = Str::uuid();
            $unzipFolder = config('filesystems.disks.admin.root') . '/files/' . $folder . '/';
            $zip->extractTo($unzipFolder);

            $numFiles = $zip->numFiles;

            for ($i = 0; $i < $numFiles; $i++) {
                $fileName = $zip->getNameIndex($i);

                //rename file to userId_categoryId_serverId.ovpn
                $newFileName = $this->createVpnFileName($fileName);
                Storage::disk('admin')->move('/files/' . $folder . '/' . $fileName, '/files/' . $folder . '/' . $newFileName);

                $fileDto = FileDto::createFileDto('files/' . $folder . '/' . $newFileName, File::TYPE_PRIVATE_BUCKET);
                $file = $this->fileService->createFile($fileDto);
                $fileIds[] = $file->id;
            }

            $zip->close();
        } else {
            throw new \Exception('Download Zip file error with url: ' . $fileUrl);
        }

        // Delete the temporary ZIP file
        unlink($tempZipPath);
        return $fileIds;
    }

    //user userId-categoryId for userId

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function enableVpnUser(int $userId, int $categoryId): void
    {
        $this->callPaymentGateway('ENABLE_USER', $userId, $categoryId);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function disableVpnUser(int $userId, int $categoryId): void
    {
        $this->callPaymentGateway('DISABLE_USER', $userId, $categoryId);
    }

    /**
     * @param int $userId
     * @param int $categoryId
     * @return string[]
     */
    public function createRequestParamArray(int $userId, int $categoryId): array
    {
        return [
            'user' => 'u=@@' . $userId . '_' . $categoryId . '@@',
            'category' => 'c=@@' . $categoryId . '@@',
        ];
    }

    /**
     * TO convert "c=@@1@@_u=@@1_2@@_s=@@3@@.ovpn" to 1_2_3.ovpn
     * UserId_CategoryId_ServerId.ovpn
     * @param string $oldName
     * @return string
     */
    public function createVpnFileName(string $oldName): string
    {
        $oldName = Str::replace('.ovpn', '', $oldName);
        $oldName = Str::replace('@', '', $oldName);
        //c=1_u=1_1_s=1.ovpn
        //c=1 u=1 1 s=1
        $params = explode('_', $oldName);
        $user = $params[1];
        $userId = Str::replace('u=', '', $user);

        $category = $params[0];
        $categoryId = Str::replace('c=', '', $category);

        $server = $params[3];
        $serverId = Str::replace('s=', '', $server);

        return $userId . '_' . $categoryId . '_' . $serverId . '.ovpn';
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Services\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    private FileService $fileService;

    public function __construct(FileService $fileService) {
        $this->fileService = $fileService;
    }

    /**
     * @throws \Exception
     */
    public function getPresignedUrl(Request $request): JsonResponse
    {
        $userId = Auth::user()->id;

        $bucketType = $request->input('bucket_type', File::TYPE_PUBLIC_BUCKET);
        $cloudDisk = $this->fileService->getCloudDisk($bucketType);
        $s3Client = $cloudDisk->getClient();
        $bucket = $this->fileService->getCloudBucketName($bucketType);

        $fileNames = $request->input('file_names');
        $urls = [];


        $now = Carbon::now();
        foreach ($fileNames as $fileName) {
            $filePath = $userId . '/' . $now->format('Y-m-d') . '/' . $fileName;

            $command = $s3Client->getCommand('putObject', [
                'Bucket' => $bucket,
                'Key' => $filePath
            ]);

            $s3Request = $s3Client->createPresignedRequest($command, '+20 minutes');
            $urls[$fileName] = $s3Request->getUri();
        }

        return response()->json(['urls' => $urls]);
    }

    /**
     * @throws \Exception
     */
    public function m3u8(int $fileId): Response
    {
        /**
         * @var File $file
         */
        $file = File::find($fileId);


        // Define the S3 disk
        $disk = Storage::disk('s3');

        // Fetch the M3U8 file content from S3
        $content = $disk->get($file->bucket_file_path);

        $lines = explode("\n", $content);

        // Prepare the expiration time for the presigned URLs
        $expiration = now()->addMinutes(60); // URLs are valid for 60 minutes

        // Parse and modify the M3U8 content
        $modifiedLines = [];
        foreach ($lines as $line) {
            if (Str::endsWith(trim($line), '.ts')) {
                // Generate a presigned URL for each TS file
                $tsUrl = $disk->temporaryUrl(dirname($file->bucket_file_path) . '/' . trim($line), $expiration);
                $modifiedLines[] = $tsUrl;
            } else {
                $modifiedLines[] = $line;
            }
        }
        $modifiedContent = implode("\n", $modifiedLines);

        // Return the modified M3U8 content as a response
        return response($modifiedContent)
            ->header('Content-Type', 'application/x-mpegURL');
    }
}

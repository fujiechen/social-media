<?php

namespace App\Services;

use App\Dtos\LandingTemplateDto;
use App\Models\LandingDomain;
use App\Models\LandingTemplate;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class LandingTemplateService
{
    private FileService $fileService;

    public function __construct(FileService $fileService) {
        $this->fileService = $fileService;
    }

    public function updateOrCreate(LandingTemplateDto $dto): LandingTemplate {
        return DB::transaction(function () use ($dto) {
            $bannerFileId = $this->fileService->getOrCreateFile($dto->bannerFileDto)->id;

            return LandingTemplate::updateOrCreate([
                'id' => $dto->id
            ], [
                'name' => $dto->name,
                'description' => $dto->description,
                'landing_html' => $dto->landingHtml,
                'redirect_type_id' => $dto->redirectTypeId,
                'target_url_id' => $dto->targetUrlId,
                'landing_domain_id' => $dto->landingDomainId,
                'status' => $dto->status,
                'banner_file_id' => $bannerFileId
            ]);
        });
    }

    /**
     * 1. create landing template folder
     * 2. download banner file into the folder
     * 3. generate QR file
     * 4. generate landing html file
     * 5. upload to s3 based on landing domain setup
     */
    public function createLandingUrl(int $landingTemplateId): void {
        /**
         * @var LandingTemplate $landingTemplate
         */
        $landingTemplate = LandingTemplate::find($landingTemplateId);

        $directoryPath = "landing_templates/{$landingTemplateId}";

        // 1. Create landing template folder
        if (Storage::disk('local')->exists($directoryPath)) {
            Storage::disk('local')->deleteDirectory($directoryPath);
        }
        Storage::disk('local')->makeDirectory($directoryPath);

        // 2. Download banner file into the folder
        $bannerUrl = $landingTemplate->bannerFile->url;
        $bannerContents = Http::get($bannerUrl)->body();
        $bannerFileName = basename($bannerUrl);
        Storage::disk('local')->put("{$directoryPath}/{$bannerFileName}", $bannerContents);

        // 3. Generate QR file from SimpleSoftwareIO\QrCode\Facades and save QR image as qr.png
        $qrUrl = $landingTemplate->targetUrl->qrFile->url;
        $qrContents = Http::get($qrUrl)->body();
        $qrFileName = basename($qrUrl);
        Storage::disk('local')->put("{$directoryPath}/{$qrFileName}", $qrContents);


        // 4. Generate landing HTML file from landingTemplate->landing_html and save as index.html
        $landingHtml = $landingTemplate->landing_html;
        $landingHtml = Str::replace('@@banner_file_url@@', $bannerFileName, $landingHtml);
        $landingHtml = Str::replace('@@qr_file_url@@', $qrFileName, $landingHtml);
        $landingHtml = Str::replace('@@target_url@@', $landingTemplate->targetUrl->url, $landingHtml);
        $landingHtml = Str::replace('@@landing_api_url@@', env('LANDING_API_ENDPOINT'), $landingHtml);

        Storage::disk('local')->put("{$directoryPath}/index.html", $landingHtml);

        // 5. Upload to S3 based on landing domain setup, the setup is from LandingDomain model
        $landingDomain = $landingTemplate->landingDomain;
        $s3Config = [
            'driver' => 's3',
            'key' => $landingDomain->access_key,
            'secret' => $landingDomain->secret,
            'region' => $landingDomain->region,
            'bucket' => $landingDomain->bucket,
            'url' => $landingDomain->endpoint_url,
            'endpoint' => $landingDomain->endpoint_url,
            'visibility' => 'public',
        ];

        // Temporarily configure the S3 disk
        Log::info('start uploading landing template ' . $landingTemplateId);
        Config::set('filesystems.disks.dynamic_s3', $s3Config);

        $s3Disk = Storage::disk('dynamic_s3');
        $s3Path = "{$landingTemplateId}";

        // Upload files to S3
        $files = Storage::disk('local')->files($directoryPath);
        foreach ($files as $file) {
            $fileContents = Storage::disk('local')->get($file);
            $s3Disk->put("{$s3Path}/" . basename($file), $fileContents);
        }

        Log::info('completed uploading landing template ' . $landingTemplateId);

        Storage::disk('local')->deleteDirectory($directoryPath);

        $baseUrl = $landingDomain->cdn_url ?? $landingDomain->endpoint_url;
        $landingUrl = $baseUrl . '/' . $landingDomain->bucket.  "/{$s3Path}/index.html";
        $landingTemplate->landing_url = $landingUrl . '?';
        $landingTemplate->status = LandingTemplate::STATUS_ACTIVE;

        LandingTemplate::withoutEvents(function() use ($landingTemplate) {
            $landingTemplate->save();
        });
    }

}

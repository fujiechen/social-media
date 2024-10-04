<?php

namespace App\Dtos;

use App\Models\File;
use App\Utils\DataTransferObject;
use Illuminate\Support\Str;

abstract class FileDto extends DataTransferObject
{
    public ?int $fileId = null;
    public string $bucketType;

    public static function createFileDto(string $uploadPath, string $bucketType): FileDto {
        if (Str::startsWith($uploadPath, 'https')) {
            if ($bucketType == File::TYPE_PUBLIC_BUCKET) {
                $bucketName = config('filesystems.disks.s3-public.bucket');
                $region = config('filesystems.disks.s3-public.region');
            } else {
                $bucketName = config('filesystems.disks.s3.bucket');
                $region = config('filesystems.disks.s3.region');
            }

            $uploadPath = Str::replace('https://', '', $uploadPath);
            $uploadPath = Str::replace('s3.' . $region . '.amazonaws.com/', '', $uploadPath);
            $uploadPath = Str::replace($bucketName . '/', '', $uploadPath);

            return new BucketFileDto([
                'bucketName' => $bucketName,
                'bucketFilePath' => $uploadPath,
                'bucketType' => $bucketType,
            ]);
        } else {
            return new UploadFileDto([
                'fileId' => 0,
                'uploadPath' => $uploadPath,
                'bucketType' => $bucketType,
            ]);
        }
    }
}

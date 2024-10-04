<?php

namespace App\Services;

use App\Dtos\BucketFileDto;
use App\Dtos\FileDto;
use App\Dtos\UploadFileDto;
use App\Exceptions\IllegalArgumentException;
use App\Models\File;
use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileService
{
    /**
     * @throws IllegalArgumentException
     */
    public function createFile(FileDto $dto): File {

        if ($dto instanceof UploadFileDto) {
            $file = File::create([
                'bucket_type' => $dto->bucketType,
                'name' => basename($dto->uploadPath),
                'upload_path' => $dto->uploadPath,
            ]);
        } else if ($dto instanceof BucketFileDto) {
            $file = File::create([
                'name' => $dto->bucketFileName,
                'bucket_type' => $dto->bucketType,
                'bucket_file_path' => $dto->bucketFilePath,
                'bucket_file_name' => $dto->bucketFileName,
                'bucket_name' => $dto->bucketName,
            ]);
        } else {
            throw new IllegalArgumentException('invalid FileDto');
        }

        return $file;
    }

    public function getOrCreateFile(FileDto $dto): ?File
    {
        return DB::transaction(function() use ($dto) {
            $file = null;

            if ($dto instanceof UploadFileDto) {
                $fileName = basename($dto->uploadPath);

                $file = File::firstOrCreate([
                    'upload_path' => $dto->uploadPath,
                ], [
                    'name' => $fileName,
                    'bucket_type' => $dto->bucketType,
                    'upload_path' => $dto->uploadPath
                ]);

            } else if ($dto instanceof BucketFileDto) {
                $file = File::firstOrCreate([
                    'bucket_file_path' => $dto->bucketFilePath,
                ], [
                    'name' => $dto->bucketFileName,
                    'bucket_type' => $dto->bucketType,
                    'bucket_file_path' => $dto->bucketFilePath,
                    'bucket_file_name' => $dto->bucketFileName,
                    'bucket_name' => $dto->bucketName,
                ]);
            }

            return $file;
        });
    }

    /**
     * @throws Exception
     */
    public function getCloudDisk(string $bucketType): Filesystem
    {
        if (File::TYPE_PRIVATE_BUCKET == $bucketType) {
            $s3Disk = Storage::disk('s3');
        } else if (File::TYPE_PUBLIC_BUCKET == $bucketType) {
            $s3Disk = Storage::disk('s3-public');
        } else {
            throw new Exception('Invalid s3 storage setup');
        }

        return $s3Disk;
    }

    /**
     * @throws Exception
     */
    public function getCloudBucketName(string $bucketType): string {
        if (File::TYPE_PRIVATE_BUCKET == $bucketType) {
            $bucketName = config('filesystems.disks.s3.bucket');
        } else if (File::TYPE_PUBLIC_BUCKET == $bucketType) {
            $bucketName = config('filesystems.disks.s3-public.bucket');
        } else {
            throw new Exception('Invalid s3 storage setup');
        }

        return $bucketName;
    }


    public function uploadFileAndRemoveLocal(int $fileId): bool {
        try {
            /**
             * @var File $file
             */
            $file = File::query()->find($fileId);

            $path = $file->upload_path;

            if (empty($path)) {
                return true;
            }

            $now = Carbon::now();
            $contents = Storage::disk('admin')->get($path);

            $bucketFileName = uniqid() . '.' . pathinfo($path, PATHINFO_EXTENSION);
            $bucketFilePath = $now->format('Y-m-d') . '/' . $bucketFileName;

            $fileSystem = $this->getCloudDisk($file->bucket_type);

            if ($file->bucket_type == File::TYPE_PUBLIC_BUCKET) {
                $fileSystem->put($bucketFilePath, $contents, 'public');
            } else if ($file->bucket_type == File::TYPE_PRIVATE_BUCKET) {
                $fileSystem->put($bucketFilePath, $contents, 'private');
            }

            $file->bucket_name = $this->getCloudBucketName($file->bucket_type);
            $file->bucket_file_name = $bucketFileName;
            $file->bucket_file_path = $bucketFilePath;
            $file->upload_path = null;

            $file->save();

            Storage::disk('admin')->delete($path);

            $directory = dirname($path);
            if (Storage::disk('admin')->exists($directory)) {
                $filesCount = count(Storage::disk('admin')->files($directory));
                if ($filesCount === 0) { //we only delete empty(uploaded already) file dir
                    Storage::disk('admin')->deleteDirectory($directory);
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('upload file error: ' . $fileId . ' ' . $e->getTraceAsString());
            return false;
        }

    }

}

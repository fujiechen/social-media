<?php

namespace App\Dtos;

class BucketFileDto extends FileDto
{
    public string $bucketName;
    public string $bucketFileName = '';
    public string $bucketFilePath;
}

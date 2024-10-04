<?php

namespace App\Jobs;

use App\Utils\DataTransferObject;

class ImportFileEvent extends DataTransferObject
{
    public int $userId;
    public int $businessId;
    public string $filePath;
    public array $productPromotionTypeIds = [];
    public string $productPromotionDescription;
}

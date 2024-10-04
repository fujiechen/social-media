<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class TutorialFileDto extends DataTransferObject
{
    public string $name;
    public int $tutorialId = 0;
    public int $fileId = 0;
}

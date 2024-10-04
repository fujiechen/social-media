<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class TutorialDto extends DataTransferObject
{
    public int $tutorialId = 0;
    public string $os;
    public string $name;
    public string $content;
    public array $tutorialFileDtos = [];
}

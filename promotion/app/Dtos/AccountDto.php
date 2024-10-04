<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class AccountDto extends DataTransferObject
{
    public int $id = 0;
    public string $instruction;
    public int $contactId;
    public int $accountTypeId;
    public ?string $nickname = null;
    public ?string $accountNo = null;
    public ?string $accountUrl = null;
    public ?string $adminUsername = null;
    public ?string $adminPassword = null;
    public ?string $profileDescription = null;
    public ?int $landingTemplateId = null;
    public string $status;
    public ?string $fileType = 'upload';
    public ?FileDto $profileAvatarFile = null;
}

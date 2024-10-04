<?php

namespace App\Http\Requests;

use App\Utils\DataTransferObject;

interface HttpRequestInterface
{
    function toDto(): DataTransferObject;
}

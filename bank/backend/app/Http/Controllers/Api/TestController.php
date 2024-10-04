<?php
namespace App\Http\Controllers\Api;

use App\Mail\RegistrationVerificationEmail;
use App\Mail\ResetPasswordEmail;
use App\Models\User;
use App\Services\FileService;
use Illuminate\Support\Facades\Mail;

class TestController extends BaseController
{
    public function index(FileService $fileService) {

        if (env('APP_ENV') != 'local') {
            exit;
        }

    }

}

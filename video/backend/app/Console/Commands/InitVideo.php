<?php

namespace App\Console\Commands;

use App\Dtos\UserDto;
use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class InitVideo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'video:init';

    protected $description = 'Create Admin user';


    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $username = 'admin';
        $password = '';
        $email = 'admin@test.com';
        $accessToken = '';

        User::query()->where('username', '=', $username)->delete();

        /**
         * @var UserService $userService
         */
        $userService = app(UserService::class);
        $userService->createUser(new UserDto([
            'userId' => 1,
            'accessToken' => $accessToken,
            'username' => $username,
            'password' => Hash::make($password),
            'nickname' => 'administrator',
            'email' => $email,
            'roleIds' => [Role::ROLE_ADMINISTRATOR_ID]
        ]));

        $this->info('admin user created');
        $this->info('username: ' . $username);
        $this->info('password: ' . $password);
        $this->info('jwt: ' . $accessToken);
    }
}

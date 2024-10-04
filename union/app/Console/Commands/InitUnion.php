<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class InitUnion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'union:init';

    protected $description = 'Create Admin user';


    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $username = 'admin';
        $password = 'UUCMquaqp2XNUmCoWXtU';
        $email = 'admin@test.com';

        User::query()->where('username', '=', $username)->delete();

        /**
         * @var User $user
         */
        $user = User::create([
            'id' => 1,
            'username' => $username,
            'nickname' => 'Administrator',
            'email' => $email,
            'password' => Hash::make($password),
            'language' => 'en',
            'extras' => [],
        ]);

        $token = JWTAuth::fromUser($user);
        $user->access_token = $token;
        $user->save();

        $this->info('user created');
        $this->info('username: ' . $username);
        $this->info('password: ' . $password);
        $this->info('jwt: ' . $token);
    }
}

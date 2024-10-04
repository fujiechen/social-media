<?php

namespace App\Transformers;

use App\Models\ServerUser;
use App\Services\CategoryUserService;
use Illuminate\Support\Carbon;
use League\Fractal\TransformerAbstract;

class UserServerTransformer extends TransformerAbstract
{
    private FileTransformer $fileTransformer;
    private CategoryUserService $categoryUserService;

    public function __construct(FileTransformer $fileTransformer, CategoryUserService $categoryUserService) {
        $this->fileTransformer = $fileTransformer;
        $this->categoryUserService = $categoryUserService;
    }
    public function transform(ServerUser $serverUser): array
    {
        $categoryId = $serverUser->server->category_id;
        $categoryUser = $this->categoryUserService->findCategoryUser($categoryId, $serverUser->user_id);

        return [
            'id' => $serverUser->id,
            'user_id' => $serverUser->user->id,
            'server_updated_at_formatted' => Carbon::parse($serverUser->server->updated_at)->format('Y-m-d'),
            'category_id' => $serverUser->server->category_id,
            'category_valid_until_at_days' => $categoryUser->valid_until_at_days,
            'category_valid_until_at_formatted' => $categoryUser->valid_until_at_formatted,
            'server_type' => $serverUser->server->type,
            'server_country_code' => $serverUser->server->country_code,
            'server_name' => $serverUser->server->name,
            'server_ip' => $serverUser->server->ip,
            'server_ipsec_shared_key' => $serverUser->server->ipsec_shared_key,
            'radius_username' => $serverUser->radius_username,
            'radius_password' => $serverUser->radius_password,
            'vpn_file' => $this->fileTransformer->transform($serverUser->server->ovpnFile),
        ];
    }
}

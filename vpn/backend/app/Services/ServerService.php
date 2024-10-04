<?php

namespace App\Services;

use App\Dtos\ServerDto;
use App\Models\Server;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ServerService
{
    private FileService $fileService;

    public function __construct(FileService $fileService) {
        $this->fileService = $fileService;
    }

    public function findServerById(int $id): ?Server {
        return Server::find($id);
    }

    public function findServerByIp(string $ip): ?Server {
        return Server::where('ip', '=', $ip)->first();
    }

    public function fetchAllServerQuery(int $categoryId): Builder
    {
        return Server::query()->where('category_id', '=', $categoryId);
    }

    public function updateOrCreateServer(ServerDto $dto): Server {
        return DB::transaction(function() use ($dto) {

            $adminPemFileId = null;
            if (isset($dto->adminPemFileDto)) {
                $adminPemFileId = $this->fileService->getOrCreateFile($dto->adminPemFileDto)->id;
            }

            $ovpnFileId = null;
            if (isset($dto->ovpnFileDto)) {
                $ovpnFileId = $this->fileService->getOrCreateFile($dto->ovpnFileDto)->id;
            }

            /**
             * @var Server $server
             */
            $server = Server::query()->updateOrCreate([
                'id' => $dto->serverId,
            ], [
                'name' => $dto->name,
                'type' => $dto->type,
                'category_id' => $dto->categoryId,
                'country_code' => $dto->countryCode,
                'ip' => $dto->ip,
                'admin_url' => $dto->adminUrl ?? null,
                'admin_username' => $dto->adminUsername ?? null,
                'admin_password' => $dto?->adminPassword ?? null,
                'admin_pem_file_id' => $adminPemFileId ?? null,
                'ovpn_file_id' => $ovpnFileId ?? null,
                'api_url' => $dto->apiUrl ?? null,
                'api_key' => $dto->apiKey ?? null,
                'api_secret' => $dto->apiSecret ?? null,
                'ipsec_shared_key' => $dto->ipSecSharedKey ?? null,
            ]);

            return $server;
        });
    }
}

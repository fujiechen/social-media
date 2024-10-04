<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class OpnsenseGatewayService
{
    private ServerService $serverService;
    private Client $client;

    public function __construct(ServerService $serverService, Client $client)
    {
        $this->serverService = $serverService;
        $this->client = $client;
    }

    public function createUser(int $serverId, int $userId, string $username, string $password): bool
    {
        try {
            $server = $this->serverService->findServerById($serverId);

            $response = $this->client->post($server->api_url . '/api/freeradius/user/addUser', [
                'auth' => [
                    $server->api_key,
                    $server->api_secret,
                ],
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'user' => [
                        'enabled' => "1",
                        'username' => $username,
                        'password' => $password,
                        'description' => 'user_id=@@' . $userId . '@@',
                    ]
                ]),
                'verify' => false
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            return $result['result'] == 'saved';
        } catch (\Exception $e) {
            Log::error('opnsense service failed to create user | ' . $e->getTraceAsString());
            return false;
        }
    }

    public function getUserUuid(int $serverId, int $userId): ?string {
        try {
            $server = $this->serverService->findServerById($serverId);

            $response = $this->client->get($server->api_url . '/api/freeradius/user/searchUser', [
                'auth' => [
                    $server->api_key,
                    $server->api_secret,
                ],
                'verify' => false
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            foreach ($result['rows'] as $row) {
                if ($row['description'] == 'user_id=@@' . $userId . '@@') {
                    return $row['uuid'];
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('opnsense service failed to get user of user_id=' . $userId . '|' . $e->getTraceAsString());
            return null;
        }
    }

    public function resetConfiguration(int $serverId): bool
    {
        try {
            $server = $this->serverService->findServerById($serverId);

            $response = $this->client->post($server->api_url . '/api/freeradius/service/reconfigure', [
                'auth' => [
                    $server->api_key,
                    $server->api_secret,
                ],
                'verify' => false
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            return $result['status'] == 'ok';

        } catch (\Exception $e) {
            Log::error('opnsense service failed to reset config | ' . $e->getTraceAsString());
            return false;
        }
    }

    public function enableUser(int $serverId, string $uuid): bool {
        try {
            $server = $this->serverService->findServerById($serverId);

            $response = $this->client->post($server->api_url . '/api/freeradius/user/setUser/' . $uuid, [
                'auth' => [
                    $server->api_key,
                    $server->api_secret,
                ],
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'user' => [
                        'enabled' => "1",
                    ]
                ]),
                'verify' => false
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            return $result['result'] == 'saved';
        } catch (\Exception $e) {
            Log::error('opnsense service failed to enable user | ' . $e->getTraceAsString());
            return false;
        }
    }

    public function disableUser(int $serverId, string $uuid): bool {
        try {
            $server = $this->serverService->findServerById($serverId);

            $response = $this->client->post($server->api_url . '/api/freeradius/user/setUser/' . $uuid, [
                'auth' => [
                    $server->api_key,
                    $server->api_secret,
                ],
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'user' => [
                        'enabled' => "0",
                    ]
                ]),
                'verify' => false
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            return $result['result'] == 'saved';
        } catch (\Exception $e) {
            Log::error('opnsense service failed to disable user | ' . $e->getTraceAsString());
            return false;
        }
    }
}

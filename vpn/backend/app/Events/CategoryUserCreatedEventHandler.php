<?php

namespace App\Events;

use App\Dtos\ServerUserDto;
use App\Models\CategoryUser;
use App\Models\Server;
use App\Services\OpnsenseGatewayService;
use App\Services\ServerService;
use App\Services\ServerUserService;
use Illuminate\Contracts\Queue\ShouldQueue;

class CategoryUserCreatedEventHandler implements ShouldQueue
{
    private ServerUserService $serverUserService;
    private OpnsenseGatewayService $opnsenseGatewayService;
    private ServerService $serverService;

    public function __construct(ServerUserService $serverUserService,
                                OpnsenseGatewayService $opnsenseGatewayService,
                                ServerService $serverService)
    {
        $this->serverUserService = $serverUserService;
        $this->opnsenseGatewayService = $opnsenseGatewayService;
        $this->serverService = $serverService;
    }

    /**
     * Update or Create all ServerUsers from the same category
     *
     * @param CategoryUserCreatedEvent $categoryUserCreatedEvent
     * @return void
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(CategoryUserCreatedEvent $categoryUserCreatedEvent): void {
        /**
         * VPN Service to update or create user and get a link to save server user
         *  - pritunl service (abandoned) : fileName format from Pritunl: category_user_server.ovpn
         *  - opnsense service:
         *     - create a radius user from api
         *     - get radius user uuid
         *     - restart radius config
         */
        $this->processOpnSenseService($categoryUserCreatedEvent);
    }


    /**
     * - create a radius user from api
     * - get radius user uuid
     * - restart radius config
     *
     * @param CategoryUserCreatedEvent $categoryUserCreatedEvent
     * @return void
     */
    private function processOpnSenseService(CategoryUserCreatedEvent $categoryUserCreatedEvent): void {
        $categoryId = $categoryUserCreatedEvent->categoryUser->category_id;
        $userId = $categoryUserCreatedEvent->categoryUser->user_id;
        $categoryUser = $categoryUserCreatedEvent->categoryUser;
        $servers = $this->serverService->fetchAllServerQuery($categoryId)->get();

        /**
         * @var Server $server
         */
        foreach ($servers as $server) {
            $radiusUsername = $this->serverUserService->generateRadiusUsername($categoryId, $server->id, $userId);
            $radiusPassword = $this->serverUserService->generateRadiusPassword();

            $radiusUuid = $this->opnsenseGatewayService->getUserUuid($server->id, $userId);

            if (!empty($radiusUuid)) { // if duplicate user, skip
                continue;
            }

            $vpnUserCreated = $this->opnsenseGatewayService->createUser($server->id, $userId, $radiusUsername, $radiusPassword);

            if (!$vpnUserCreated) {
                continue;
            }

            $radiusUuid = $this->opnsenseGatewayService->getUserUuid($server->id, $userId);
            if (empty($radiusUuid)) {
                continue;
            }

            $restarted = $this->opnsenseGatewayService->resetConfiguration($server->id);
            if (!$restarted) {
                continue;
            }

            $this->serverUserService->updateOrCreateServerUser(new ServerUserDto([
                'serverId' => $server->id,
                'userId' => $userId,
                'radiusUuid' => $radiusUuid,
                'radiusUsername' => $radiusUsername,
                'radiusPassword' => $radiusPassword,
            ]));
        }

        CategoryUser::withoutEvents(function () use ($categoryUser) {
            $categoryUser->vpn_server_synced = true;
            $categoryUser->save();
        });
    }
}

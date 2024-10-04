<?php

namespace App\Events;

use App\Models\CategoryUser;
use App\Services\OpnsenseGatewayService;
use App\Services\ServerService;
use App\Services\ServerUserService;
use Illuminate\Contracts\Queue\ShouldQueue;

class CategoryUserUpdatedEventHandler implements ShouldQueue
{
    private ServerUserService $serverUserService;
    private ServerService $serverService;
    private OpnsenseGatewayService $opnsenseGatewayService;


    public function __construct(ServerUserService $serverUserService,
                                ServerService $serverService,
                                OpnsenseGatewayService $opnsenseGatewayService)
    {
        $this->serverUserService = $serverUserService;
        $this->serverService = $serverService;
        $this->opnsenseGatewayService = $opnsenseGatewayService;
    }

    /**
     * Update all ServerUsers from the same category
     *
     * @param CategoryUserUpdatedEvent $categoryUserUpdatedEvent
     * @return void
     */
    public function handle(CategoryUserUpdatedEvent $categoryUserUpdatedEvent): void {
        try {
            $categoryUser = $categoryUserUpdatedEvent->categoryUser;
            $this->processOpnsenseService($categoryUserUpdatedEvent);
            CategoryUser::withoutEvents(function () use ($categoryUser) {
                $categoryUser->vpn_server_synced = true;
                $categoryUser->save();
            });
        } catch (\Exception $e) {
            CategoryUser::withoutEvents(function () use ($categoryUser) {
                $categoryUser->vpn_server_synced = false;
                $categoryUser->save();
            });
        }
    }

    private function processOpnsenseService(CategoryUserUpdatedEvent $categoryUserUpdatedEvent): void {
        $categoryId = $categoryUserUpdatedEvent->categoryUser->category_id;
        $userId = $categoryUserUpdatedEvent->categoryUser->user_id;
        $categoryUser = $categoryUserUpdatedEvent->categoryUser;

        $servers = $this->serverService->fetchAllServerQuery($categoryId)->get();
        foreach ($servers as $server) {
            $serverUser = $this->serverUserService->findServerUser($server->id, $userId);
            if (empty($serverUser)) {
                continue;
            }

            if ($categoryUser->isExpired()) {
                $updatedUser = $this->opnsenseGatewayService->disableUser($server->id, $serverUser->radius_uuid);
            } else {
                $updatedUser = $this->opnsenseGatewayService->enableUser($server->id, $serverUser->radius_uuid);
            }

            if ($updatedUser) {
                $this->opnsenseGatewayService->resetConfiguration($server->id);
            }
        }
    }
}

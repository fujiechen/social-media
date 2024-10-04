<?php

use App\Dtos\UserDto;
use App\Models\Role;
use App\Services\UserMenuService;
use App\Services\UserService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * @var UserMenuService $userMenuService
         */
        $userMenuService = app(UserMenuService::class);

        $userMenuService->createMenuGroup('User Manager');
        $userMenuService->createMenuAndPermissionToMenuGroup('User Manager','Users', 'user/', 'user/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('User Manager','Subscriptions', 'userSubscription/', 'userSubscription/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('User Manager','Shares', 'userShare/', 'userShare/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('User Manager','Referrals', 'userReferral/', 'userReferral/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('User Manager','Histories', 'userHistory/', 'userHistory/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('User Manager','Favorites', 'userFavorite/', 'userFavorite/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('User Manager','Likes', 'userLike/', 'userLike/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('User Manager','Comments', 'userComment/', 'userComment/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('User Manager','Activities', 'userActivity/', 'userActivity/*');

        $userMenuService->createMenuGroup('Ecommerce Manager');
        $userMenuService->createMenuAndPermissionToMenuGroup('Ecommerce Manager','Products', 'product/', 'product/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Ecommerce Manager','Orders', 'order/', 'order/*');

        $userMenuService->createMenuGroup('Media Manager');
        $userMenuService->createMenuAndPermissionToMenuGroup('Media Manager','Medias', 'media/', 'media/*');

        $userMenuService->createMenuGroup('Library Manager');
        $userMenuService->createMenuAndPermissionToMenuGroup('Library Manager','Videos', 'video/', 'video/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Library Manager','Series', 'series/', 'series/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Library Manager','Albums', 'album/', 'album/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Library Manager','Tags', 'tag/', 'tag/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Library Manager','Actors', 'actor/', 'actor/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Library Manager','Categories', 'category/', 'category/*');

        $userMenuService->createMenuGroup('Resource Manager');
        $userMenuService->createMenuAndPermissionToMenuGroup('Resource Manager','Resources', 'resource/', 'resource/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Resource Manager','Resource Videos', 'resourceVideo/', 'resourceVideo/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Resource Manager','Resource Tags', 'resourceTag/', 'resourceTag/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Resource Manager','Resource Actors', 'resourceActor/', 'resourceActor/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Resource Manager','Resource Categories', 'resourceCategory/', 'resourceCategory/*');

        $userMenuService->createMenuGroup('Storage & Queue');
        $userMenuService->createMenuAndPermissionToMenuGroup('Storage & Queue','Media Queues', 'mediaQueue/', 'mediaQueue/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Storage & Queue','Video Queues', 'videoQueue/', 'videoQueue/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Storage & Queue','Files', 'file/', 'file/*');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
};

<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\UserService;
use Dcat\Admin\Models\Menu;
use App\Models\Role;
use App\Models\UserPermission;
use App\Services\UserMenuService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetPortalMenuAndPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vpn:reset-menu-permission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset Portal Menu and Permissions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): void
    {
        DB::table('user_role_menus')->truncate();
        DB::table('user_role_permissions')->truncate();

        UserPermission::truncate();
        UserPermission::insert([
            [
                'id'          => 1,
                'name'        => 'Auth management',
                'slug'        => 'auth-management',
                'http_method' => '',
                'http_path'   => '',
                'parent_id'   => 0,
                'order'       => 1,
                'created_at'  => time(),
                'updated_at' => time(),
            ],
            [
                'id'          => 2,
                'name'        => 'Users',
                'slug'        => 'users',
                'http_method' => '',
                'http_path'   => '/auth/users*',
                'parent_id'   => 1,
                'order'       => 2,
                'created_at'  => time(),
                'updated_at' => time(),
            ],
            [
                'id'          => 3,
                'name'        => 'Roles',
                'slug'        => 'roles',
                'http_method' => '',
                'http_path'   => '/auth/roles*',
                'parent_id'   => 1,
                'order'       => 3,
                'created_at'  => time(),
                'updated_at' => time(),
            ],
            [
                'id'          => 4,
                'name'        => 'Permissions',
                'slug'        => 'permissions',
                'http_method' => '',
                'http_path'   => '/auth/permissions*',
                'parent_id'   => 1,
                'order'       => 4,
                'created_at'  => time(),
                'updated_at' => time(),
            ],
            [
                'id'          => 5,
                'name'        => 'Menu',
                'slug'        => 'menu',
                'http_method' => '',
                'http_path'   => '/auth/menu*',
                'parent_id'   => 1,
                'order'       => 5,
                'created_at'  => time(),
                'updated_at' => time(),
            ],
            [
                'id'          => 6,
                'name'        => 'Extension',
                'slug'        => 'extension',
                'http_method' => '',
                'http_path'   => '/auth/extensions*',
                'parent_id'   => 1,
                'order'       => 6,
                'created_at'  => time(),
                'updated_at' => time(),
            ],
        ]);

        // add default menus.
        Menu::truncate();
        Menu::insert([
            [
                'parent_id'     => 0,
                'order'         => 2,
                'title'         => 'Admin',
                'icon'          => 'feather icon-settings',
                'uri'           => '',
                'created_at'  => time(),
                'updated_at' => time(),
                'show' => 0,
            ],
            [
                'parent_id'     => 1,
                'order'         => 3,
                'title'         => 'Users',
                'icon'          => '',
                'uri'           => 'auth/users',
                'created_at'  => time(),
                'updated_at' => time(),
                'show' => 0,
            ],
            [
                'parent_id'     => 1,
                'order'         => 4,
                'title'         => 'Roles',
                'icon'          => '',
                'uri'           => 'auth/roles',
                'created_at'  => time(),
                'updated_at' => time(),
                'show' => 0,
            ],
            [
                'parent_id'     => 1,
                'order'         => 5,
                'title'         => 'Permission',
                'icon'          => '',
                'uri'           => 'auth/permissions',
                'created_at'  => time(),
                'updated_at' => time(),
                'show' => 0,
            ],
            [
                'parent_id'     => 1,
                'order'         => 6,
                'title'         => 'Menu',
                'icon'          => '',
                'uri'           => 'auth/menu',
                'created_at'  => time(),
                'updated_at' => time(),
                'show' => 0,
            ],
            [
                'parent_id'     => 1,
                'order'         => 7,
                'title'         => 'Extensions',
                'icon'          => '',
                'uri'           => 'auth/extensions',
                'created_at'  => time(),
                'updated_at' => time(),
                'show' => 0,
            ],
        ]);

        (new Menu())->flushCache();

        /**
         * @var UserMenuService $userMenuService
         */
        $userMenuService = app(UserMenuService::class);

        $userMenuService->createMenuGroup('User Manager');
        $userMenuService->createMenuAndPermissionToMenuGroup('User Manager','Profiles', 'user/', 'user/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('User Manager','Shares', 'userShare/', 'userShare/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('User Manager','Referrals', 'userReferral/', 'userReferral/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('User Manager','User Servers', 'serverUser', 'serverUser/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('User Manager','User Categories', 'categoryUser', 'categoryUser/*');

        $userMenuService->createMenuGroup('Financial Manager');
        $userMenuService->createMenuAndPermissionToMenuGroup('Financial Manager','Categories', 'category/', 'category/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Financial Manager','Products', 'product/', 'product/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Financial Manager','Orders', 'order/', 'order/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Financial Manager','Payouts', 'userPayout/', 'userPayout/*');

        $userMenuService->createMenuGroup('Storage & Queue');
        $userMenuService->createMenuAndPermissionToMenuGroup('Storage & Queue','Files', 'file/', 'file/*');

        $userMenuService->createMenuGroup('Configuration');
        $userMenuService->createMenuAndPermissionToMenuGroup('Configuration','Meta', 'meta/', 'meta/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Configuration','Servers', 'server/', 'server/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Configuration','Tutorials', 'tutorial/', 'tutorial/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Configuration','Apps', 'app/', 'app/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Configuration','App Categories', 'appCategory/', 'appCategory/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Configuration','Activities', 'userActivity/', 'userActivity/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Configuration','Jobs', 'job/', 'job/*');
    }
}

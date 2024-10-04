<?php

namespace App\Console\Commands;

use App\Models\UserPermission;
use App\Services\UserMenuService;
use Dcat\Admin\Models\Menu;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetPortalMenuAndPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'promotion:reset-menu-permission';

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

        $userMenuService->createMenuGroup('用户管理');
        $userMenuService->createMenuAndPermissionToMenuGroup('用户管理','用户信息', 'user/', 'user/*');

        $userMenuService->createMenuGroup('设置');
        $userMenuService->createMenuAndPermissionToMenuGroup('设置','推广网址', 'targetUrl/', 'targetUrl/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('设置','推广平台', 'accountType/', 'accountType/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('设置','跳转类型', 'redirectType/', 'redirectType/*');


        $userMenuService->createMenuGroup('推广管理');
        $userMenuService->createMenuAndPermissionToMenuGroup('推广管理','手机邮箱', 'contact/', 'contact/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('推广管理','内容灵感', 'contentType/', 'contentType/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('推广管理','账户管理', 'account/', 'account/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('推广管理','推广发帖', 'post/', 'post/*');


        $userMenuService->createMenuGroup('落地页管理');
        $userMenuService->createMenuAndPermissionToMenuGroup('落地页管理','落地页模版', 'landingTemplate/', 'landingTemplate/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('落地页管理','落地页域名', 'landingDomain/', 'landingDomain/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('落地页管理','落地页统计', 'landing/', 'landing/*');

        $userMenuService->createMenuGroup('存储及队列');
        $userMenuService->createMenuAndPermissionToMenuGroup('存储及队列','存储文件', 'file/', 'file/*');
    }
}

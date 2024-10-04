<?php

namespace App\Console\Commands;

use App\Models\Role;
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
    protected $signature = 'bank:reset-menu-permission';

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
    public function handle()
    {
        DB::table('user_role_menus')->truncate();
        DB::table('user_role_permissions')->truncate();

        UserPermission::truncate();
        UserPermission::insert([
            [
                'id'          => 1,
                'name'        => '权限管理',
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
                'name'        => '用户',
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
                'name'        => '角色',
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
                'name'        => '权限',
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
                'name'        => '菜单',
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
                'name'        => '插件',
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
                'title'         => '管理员',
                'icon'          => 'feather icon-settings',
                'uri'           => '',
                'created_at'  => time(),
                'updated_at' => time(),
                'show' => 0,
            ],
            [
                'parent_id'     => 1,
                'order'         => 3,
                'title'         => '用户',
                'icon'          => '',
                'uri'           => 'auth/users',
                'created_at'  => time(),
                'updated_at' => time(),
                'show' => 0,
            ],
            [
                'parent_id'     => 1,
                'order'         => 4,
                'title'         => '角色',
                'icon'          => '',
                'uri'           => 'auth/roles',
                'created_at'  => time(),
                'updated_at' => time(),
                'show' => 0,
            ],
            [
                'parent_id'     => 1,
                'order'         => 5,
                'title'         => '权限',
                'icon'          => '',
                'uri'           => 'auth/permissions',
                'created_at'  => time(),
                'updated_at' => time(),
                'show' => 0,
            ],
            [
                'parent_id'     => 1,
                'order'         => 6,
                'title'         => '菜单',
                'icon'          => '',
                'uri'           => 'auth/menu',
                'created_at'  => time(),
                'updated_at' => time(),
                'show' => 0,
            ],
            [
                'parent_id'     => 1,
                'order'         => 7,
                'title'         => '插件',
                'icon'          => '',
                'uri'           => 'auth/extensions',
                'created_at'  => time(),
                'updated_at' => time(),
                'show' => 0,
            ],
        ]);

        foreach(UserPermission::all() as $permission) {
            DB::table('user_role_permissions')->insert([
                [
                    'role_id' => Role::ADMINISTRATOR_ID,
                    'permission_id' => $permission->id,
                    'created_at'  => time(),
                    'updated_at' => time(),
                ],
            ]);
        }

        (new Menu())->flushCache();

        /**
         * @var UserMenuService $userMenuService
         */
        $userMenuService = app(UserMenuService::class);

        $userMenuService->createMenuGroup('设置');
        $userMenuService->createMenuAndPermissionToMenuGroup('设置','配置', 'setting/', 'setting/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('设置','币种', 'currency/', 'currency/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('设置','汇率', 'currencyRate/', 'currencyRate/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('设置','支付接口', 'paymentGateway/', 'paymentGateway/*');

        $userMenuService->createMenuGroup('用户管理');
        $userMenuService->createMenuAndPermissionToMenuGroup('用户管理','用户', 'user/', 'user/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('用户管理','账户', 'userAccount/', 'userAccount/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('用户管理','行为记录', 'userActivity/', 'userActivity/*');
//        $userMenuService->createMenuAndPermissionToMenuGroup('用户管理','提现', 'userWithdrawAccount/', 'userWithdrawAccount/*');
//        $userMenuService->createMenuAndPermissionToMenuGroup('用户管理','地址', 'userAddress/', 'userAddress/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('用户管理','交易记录', 'userTransaction/', 'userTransaction/*');


        $userMenuService->createMenuGroup('产品管理');
        $userMenuService->createMenuAndPermissionToMenuGroup('产品管理','分类', 'productCategory/', 'productCategory/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('产品管理','产品', 'product/', 'product/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('产品管理','每日波动', 'productRate/', 'productRate/*');

        $userMenuService->createMenuGroup('金融管理');
        $userMenuService->createMenuAndPermissionToMenuGroup('金融管理','订单支付', 'order/payment', 'order/payment/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('金融管理','充值订单', 'order/deposit', 'order/deposit/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('金融管理','购买订单', 'order/purchase', 'order/purchase/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('金融管理','提现订单', 'order/withdraw', 'order/withdraw/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('金融管理','换汇订单', 'order/exchange', 'order/exchange/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('金融管理','转账订单', 'order/transfer', 'order/transfer/*');
//        $userMenuService->createMenuAndPermissionToMenuGroup('金融管理','投资', 'userProduct/', 'userProduct/*');
    }
}

<?php

namespace App\Console\Commands;

use Dcat\Admin\Models\Menu;
use App\Models\UserPermission;
use App\Services\UserMenuService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetPortalMenuAndPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'video:reset-menu-permission';

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

        (new Menu())->flushCache();

        /**
         * @var UserMenuService $userMenuService
         */
        $userMenuService = app(UserMenuService::class);

        $userMenuService->createMenuGroup('设置');
        $userMenuService->createMenuAndPermissionToMenuGroup('设置','配置', 'meta/', 'meta/*');

        $userMenuService->createMenuGroup('用户管理');
        $userMenuService->createMenuAndPermissionToMenuGroup('用户管理','用户', 'user/', 'user/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('用户管理','订阅', 'userSubscription/', 'userSubscription/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('用户管理','分享', 'userShare/', 'userShare/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('用户管理','推广', 'userReferral/', 'userReferral/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('用户管理','历史', 'userHistory/', 'userHistory/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('用户管理','收藏', 'userFavorite/', 'userFavorite/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('用户管理','点赞', 'userLike/', 'userLike/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('用户管理','评论', 'userComment/', 'userComment/*');


        $userMenuService->createMenuGroup('电商管理');
        $userMenuService->createMenuAndPermissionToMenuGroup('电商管理','产品', 'product/', 'product/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('电商管理','订单', 'order/', 'order/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('电商管理','支出', 'userPayout/', 'userPayout/*');

        $userMenuService->createMenuGroup('媒体管理');
        $userMenuService->createMenuAndPermissionToMenuGroup('媒体管理','媒体', 'media/', 'media/*');

        $userMenuService->createMenuGroup('资料库管理');
        $userMenuService->createMenuAndPermissionToMenuGroup('资料库管理','资料库视频', 'video/', 'video/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('资料库管理','资料库合集', 'series/', 'series/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('资料库管理','资料库图册', 'album/', 'album/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('资料库管理','资料库标签', 'tag/', 'tag/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('资料库管理','资料库演员', 'actor/', 'actor/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('资料库管理','资料库分类', 'category/', 'category/*');

        $userMenuService->createMenuGroup('数据源管理');
        $userMenuService->createMenuAndPermissionToMenuGroup('数据源管理','数据源资源', 'resource/', 'resource/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('数据源管理','数据源视频', 'resourceVideo/', 'resourceVideo/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('数据源管理','数据源图册', 'resourceAlbum/', 'resourceAlbum/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('数据源管理','数据源标签', 'resourceTag/', 'resourceTag/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('数据源管理','数据源演员', 'resourceActor/', 'resourceActor/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('数据源管理','数据源分类', 'resourceCategory/', 'resourceCategory/*');

        $userMenuService->createMenuGroup('存储及队列');
        $userMenuService->createMenuAndPermissionToMenuGroup('存储及队列','媒体爬虫', 'mediaQueue/', 'mediaQueue/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('存储及队列','合集爬虫', 'seriesQueue/', 'seriesQueue/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('存储及队列','视频爬虫', 'videoQueue/', 'videoQueue/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('存储及队列','图册爬虫', 'albumQueue/', 'albumQueue/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('存储及队列','播放列表爬虫', 'playlistQueue/', 'playlistQueue/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('存储及队列','存储文件', 'file/', 'file/*');

        $userMenuService->createMenuGroup('运行维护');
        $userMenuService->createMenuAndPermissionToMenuGroup('运行维护','用户记录', 'userActivity/', 'userActivity/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('运行维护','服务器运行队列', 'job/', 'job/*');
    }
}

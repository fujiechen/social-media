<?php

namespace App\Services;

use App\Models\UserMenu;
use App\Models\UserPermission;
use App\Models\Role;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserMenuService
{
    public function createMenuGroup(string $uniqueMenuGroupName, array $roleIds = [Role::ADMINISTRATOR_ID]): void
    {
        $menu = UserMenu::create([
            'parent_id' => 0,
            'title' => $uniqueMenuGroupName,
            'show' => 1,
        ]);

        $permission = UserPermission::create([
            'name'        => $uniqueMenuGroupName,
            'slug'        => Str::replace(' ', '-', Str::lower($uniqueMenuGroupName)),
            'parent_id'   => 0,
            'created_at'  => Carbon::now(),
        ]);

        foreach($roleIds as $roleId) {
            //add role permissions
            DB::table('user_role_permissions')->insert([
                ['role_id' => $roleId, 'permission_id' => $permission->id, 'updated_at' => Carbon::now()->timestamp, 'created_at' => Carbon::now()->timestamp],
            ]);

            //add role menus
            DB::table('user_role_menus')->insert([
                ['role_id' => $roleId, 'menu_id' => $menu->id, 'updated_at' => Carbon::now()->timestamp, 'created_at' => Carbon::now()->timestamp],
            ]);
        }

    }

    public function createMenuAndPermissionToMenuGroup(string $uniqueMenuGroupName, $name, $menuUri, $permissionPath, array $roleIds = [Role::ADMINISTRATOR_ID]): void
    {
        $createdAt = date('Y-m-d H:i:s');

        $permission = UserPermission::create([
            'name'        => $name,
            'slug'        => $name,
            'http_method' => '',
            'http_path'   => $permissionPath,
            'parent_id'   => 0,
            'created_at'  => $createdAt,
        ]);

        //add role permissions
        foreach($roleIds as $roleId) {
            DB::table('user_role_permissions')->insert([
                ['role_id' => $roleId, 'permission_id' => $permission->id, 'updated_at' => Carbon::now()->timestamp, 'created_at' => Carbon::now()->timestamp],
            ]);
        }

        $adminMenu = UserMenu::where('title', $uniqueMenuGroupName)->first();
        UserMenu::create([
            'parent_id' => $adminMenu->id,
            'title' => $name,
            'uri' => $menuUri,
            'show' => 1,
        ]);

    }

    public function createRolePermission($name, $permissionPath, array $roleIds = [Role::ADMINISTRATOR_ID]) {
        $createdAt = date('Y-m-d H:i:s');

        //add permissions
        $permission = UserPermission::create([
            'name'        => $name,
            'slug'        => $name,
            'http_method' => '',
            'http_path'   => $permissionPath,
            'parent_id'   => 0,
            'created_at'  => $createdAt,
        ]);

        //add role permissions
        foreach($roleIds as $roleId) {
            DB::table('user_role_permissions')->insert([
                ['role_id' => $roleId, 'permission_id' => $permission->id, 'updated_at' => Carbon::now(), 'created_at' => Carbon::now()],
            ]);
        }
    }

    public function createRoleMenuAndPermission($name, $menuUri, $permissionPath, array $roleIds = [Role::ADMINISTRATOR_ID]) {
        $now = time();

        //add menus
        $menu = UserMenu::create([
            'parent_id' => 0,
            'title' => $name,
            'uri' => $menuUri,
            'show' => 1,
        ]);

        //add permissions
        $permission = UserPermission::create([
            'name'        => $name,
            'slug'        => $name,
            'http_method' => '',
            'http_path'   => $permissionPath,
            'parent_id'   => 0,
            'created_at'  => $now,
        ]);

        foreach($roleIds as $roleId) {
            //add role permissions
            DB::table('user_role_permissions')->insert([
                ['role_id' => $roleId, 'permission_id' => $permission->id, 'updated_at' => Carbon::now(), 'created_at' => Carbon::now()],
            ]);

            //add role menus
            DB::table('user_role_menus')->insert([
                ['role_id' => $roleId, 'menu_id' => $menu->id, 'updated_at' => $now, 'created_at' => $now],
            ]);
        }
    }
}

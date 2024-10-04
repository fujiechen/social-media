<?php

namespace App\Models;

use Dcat\Admin\Models\Permission;

class UserPermission extends Permission
{
    protected $fillable = ['parent_id', 'name', 'slug', 'http_method', 'http_path'];

    const ADMIN_MENU_TITLE = '系统管理';
    const ADMIN_MENU_SLUG = 'admin-menu';
}

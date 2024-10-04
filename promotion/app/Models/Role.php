<?php

namespace App\Models;

use Dcat\Admin\Models\Role as DcatRole;

class Role extends DcatRole
{
    public $table = 'role';

    const ROLE_ADMINISTRATOR_ID = 1;
    const ROLE_ADMINISTRATOR_NAME = '管理员';
    const ROLE_ADMINISTRATOR_SLUG = 'administrator';

    const ROLE_USER_ID = 2;
    const ROLE_USER_NAME = '用户';
    const ROLE_USER_SLUG = 'user';

}

<?php

namespace App\Models;

use Dcat\Admin\Models\Role as DcatRole;

class Role extends DcatRole
{
    const ROLE_ADMINISTRATOR_ID = 1;
    const ROLE_ADMINISTRATOR_NAME = '管理员';
    const ROLE_ADMINISTRATOR_SLUG = 'administrator';

    const ROLE_USER_ID = 2;
    const ROLE_USER_NAME = '注册用户';
    const ROLE_USER_SLUG = 'user';

    const ROLE_AGENT_ID = 3;
    const ROLE_AGENT_NAME = '商户';
    const ROLE_AGENT_SLUG = 'business';
}

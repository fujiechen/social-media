<?php

namespace App\Models;

use Dcat\Admin\Models\Role as DcatRole;

class Role extends DcatRole
{
    public $table = 'role';

    const ROLE_ADMINISTRATOR_ID = 1;
    const ROLE_ADMINISTRATOR_NAME = '管理员';
    const ROLE_ADMINISTRATOR_SLUG = 'administrator';

    const ROLE_VISITOR_ID = 2;
    const ROLE_VISITOR_NAME = '游客';
    const ROLE_VISITOR_SLUG = 'visitor';

    const ROLE_USER_ID = 3;
    const ROLE_USER_NAME = '普通用户';
    const ROLE_USER_SLUG = 'user';

    const ROLE_MEMBERSHIP_ID = 4;
    const ROLE_MEMBERSHIP_NAME = 'VIP';
    const ROLE_MEMBERSHIP_SLUG = 'membership';

    const ROLE_AGENT_ID = 5;
    const ROLE_AGENT_NAME = '推广';
    const ROLE_AGENT_SLUG = 'agent';


    public static function roleIdToName(int $id) {
        switch ($id) {
            case self::ROLE_ADMINISTRATOR_ID: return self::ROLE_ADMINISTRATOR_NAME;
            case self::ROLE_VISITOR_ID: return self::ROLE_VISITOR_NAME;
            case self::ROLE_USER_ID: return self::ROLE_USER_NAME;
            case self::ROLE_MEMBERSHIP_ID: return self::ROLE_MEMBERSHIP_NAME;
            case self::ROLE_AGENT_ID: return self::ROLE_AGENT_NAME;
        }
    }
}

<?php

namespace App\Admin\Components\Tables;

use App\Models\Role;
use App\Models\User;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Illuminate\Contracts\Support\Renderable;

class UserTable extends LazyRenderable implements Renderable
{
    public function grid(): Grid
    {
        return Grid::make(
            User::query()
                ->select('users.*')
                ->join('user_role_users', 'users.id', '=', 'user_role_users.user_id')
                ->whereIn('user_role_users.role_id', [Role::ROLE_ADMINISTRATOR_ID, Role::ROLE_USER_ID])
                ->groupBy('users.id'),
            function (Grid $grid) {
                $grid->disableCreateButton();
                $grid->disableRefreshButton();
                $grid->disableActions();

                $grid->quickSearch(['username', 'nickname']);

                $grid->column('id', 'Id');
                $grid->column('username', 'Username');
                $grid->column('role_names', 'Roles')->label();
                $grid->column('nickname', 'Nickname');
                $grid->column('created_at_formatted', 'Created');
            });
    }
}

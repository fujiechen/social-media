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
                ->whereIn('user_role_users.role_id', [Role::ROLE_USER_ID, Role::ROLE_MEMBERSHIP_ID])
                ->groupBy('users.id'),
            function (Grid $grid) {
                $grid->disableCreateButton();
                $grid->disableRefreshButton();
                $grid->disableActions();

                $grid->quickSearch(['username', 'nickname']);

                $grid->column('id');
                $grid->column('username');
                $grid->column('role_names', admin_trans_field('role'))->label();
                $grid->column('nickname');
                $grid->column('created_at');
            });
    }
}

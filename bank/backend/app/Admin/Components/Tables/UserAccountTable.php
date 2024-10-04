<?php

namespace App\Admin\Components\Tables;

use App\Models\Role;
use App\Models\User;
use App\Models\UserAccount;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Illuminate\Contracts\Support\Renderable;

class UserAccountTable extends LazyRenderable implements Renderable
{
    public function grid(): Grid
    {
        return Grid::make(
            UserAccount::query(),
            function (Grid $grid) {
                $grid->disableCreateButton();
                $grid->disableRefreshButton();
                $grid->disableActions();

                $grid->quickSearch(['currency.name', 'user.nickname']);

                $grid->column('id', 'Id');
                $grid->column('user.nickname', 'User');
                $grid->column('currency.name', 'Currency');
                $grid->column('account_number', 'Account #');
                $grid->column('balance_in_dollar', 'Balance');
            });
    }
}

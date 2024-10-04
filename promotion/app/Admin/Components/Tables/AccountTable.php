<?php

namespace App\Admin\Components\Tables;

use App\Models\Account;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Illuminate\Contracts\Support\Renderable;

class AccountTable extends LazyRenderable implements Renderable
{
    public function grid(): Grid
    {
        return Grid::make(Account::query()
            ->where('status', '=', Account::STATUS_ACTIVE),
            function (Grid $grid) {
                $grid->disableCreateButton();
                $grid->disableRefreshButton();
                $grid->disableActions();

                $grid->quickSearch(['name']);

                $grid->column('id', 'Id');
                $grid->column('contact.contact', 'Contact');
                $grid->column('nickname', 'Nickname');
                $grid->column('account_url', 'Account Url');
                $grid->column('created_at_formatted', 'Created');
            });
    }
}

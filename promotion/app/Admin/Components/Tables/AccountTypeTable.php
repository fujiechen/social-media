<?php

namespace App\Admin\Components\Tables;

use App\Models\AccountType;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Illuminate\Contracts\Support\Renderable;

class AccountTypeTable extends LazyRenderable implements Renderable
{
    public function grid(): Grid
    {
        return Grid::make(AccountType::query(),
            function (Grid $grid) {
                $grid->disableCreateButton();
                $grid->disableRefreshButton();
                $grid->disableActions();

                $grid->quickSearch(['name']);

                $grid->column('id', 'Id');
                $grid->column('name', 'Name')->width('30%');
                $grid->column('created_at_formatted', 'Created');
            });
    }
}

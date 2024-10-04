<?php

namespace App\Admin\Components\Tables;

use App\Models\Currency;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Illuminate\Contracts\Support\Renderable;

class CurrencyTable extends LazyRenderable implements Renderable
{
    public function grid(): Grid
    {
        return Grid::make(Currency::query(),
            function (Grid $grid) {
                $grid->disablePagination();
                $grid->disableCreateButton();
                $grid->disableRefreshButton();
                $grid->disableActions();

                $grid->quickSearch(['name']);

                $grid->column('id', 'Id');
                $grid->column('name', 'Name')->width('30%');
                $grid->column('symbol', 'Symbol');
            });
    }
}

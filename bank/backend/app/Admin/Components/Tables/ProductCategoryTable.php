<?php

namespace App\Admin\Components\Tables;

use App\Models\ProductCategory;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Illuminate\Contracts\Support\Renderable;

class ProductCategoryTable extends LazyRenderable implements Renderable
{
    public function grid(): Grid
    {
        return Grid::make(ProductCategory::query(),
            function (Grid $grid) {
                $grid->disableCreateButton();
                $grid->disableRefreshButton();
                $grid->disableActions();

                $grid->quickSearch(['name']);

                $grid->column('id', 'Id');
                $grid->column('name', 'Name')->width('30%');
            });
    }
}

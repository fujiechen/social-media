<?php

namespace App\Admin\Components\Tables;

use App\Models\Product;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Illuminate\Contracts\Support\Renderable;

class ProductTable extends LazyRenderable implements Renderable
{
    public function grid(): Grid
    {
        return Grid::make(Product::query(),
            function (Grid $grid) {
                $grid->disableCreateButton();
                $grid->disableRefreshButton();
                $grid->disableActions();

                $grid->quickSearch(['user_id', 'name']);

                $grid->column('id', 'Id');
                $grid->column('title', 'Title');
                $grid->column('name', 'name')->width('30%');
                $grid->column('productCategory.name', 'Category');
                $grid->column('created_at', 'Created');
            });
    }
}

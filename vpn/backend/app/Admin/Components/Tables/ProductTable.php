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

                $grid->column('id', 'Id');
                $grid->column('category.name', 'Category');
                $grid->column('name', 'Name');
                $grid->column('frequency', 'Frequency');
                $grid->column('unit_price', 'Unit Price');
                $grid->column('created_at', 'Created');
            });
    }
}

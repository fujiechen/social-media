<?php

namespace App\Admin\Components\Tables;

use App\Models\Category;
use App\Models\Server;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Illuminate\Contracts\Support\Renderable;

class ServerTable extends LazyRenderable implements Renderable
{
    public function grid(): Grid
    {
        return Grid::make(Server::query(),
            function (Grid $grid) {
                $grid->disableCreateButton();
                $grid->disableRefreshButton();
                $grid->disableActions();

                $grid->quickSearch(['name']);

                $grid->column('id', 'Id');
                $grid->column('category.name', 'Category');
                $grid->column('type', 'Type');
                $grid->column('name', 'Name');
                $grid->column('country_code', 'Country');
                $grid->column('created_at_formatted', 'Created');
            });
    }
}

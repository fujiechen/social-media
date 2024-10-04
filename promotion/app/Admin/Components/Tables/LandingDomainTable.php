<?php

namespace App\Admin\Components\Tables;

use App\Models\LandingDomain;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Illuminate\Contracts\Support\Renderable;

class LandingDomainTable extends LazyRenderable implements Renderable
{
    public function grid(): Grid
    {
        return Grid::make(LandingDomain::query(),
            function (Grid $grid) {
                $grid->disableCreateButton();
                $grid->disableRefreshButton();
                $grid->disableActions();

                $grid->quickSearch(['name']);

                $grid->column('id', 'Id');
                $grid->column('name', 'Name');
                $grid->column('description', 'Description');
                $grid->column('status', 'Status');
                $grid->column('created_at_formatted', 'Created');
            });
    }
}

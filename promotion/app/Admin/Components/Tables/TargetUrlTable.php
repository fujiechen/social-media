<?php

namespace App\Admin\Components\Tables;

use App\Models\TargetUrl;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Illuminate\Contracts\Support\Renderable;

class TargetUrlTable extends LazyRenderable implements Renderable
{
    public function grid(): Grid
    {
        return Grid::make(TargetUrl::query()
            ->where('status', '=', TargetUrl::STATUS_ACTIVE),
            function (Grid $grid) {
                $grid->disableCreateButton();
                $grid->disableRefreshButton();
                $grid->disableActions();

                $grid->column('id', 'Id');
                $grid->column('url', 'Url');
                $grid->column('status', 'Status');
                $grid->column('created_at_formatted', 'Created');
            });
    }
}

<?php

namespace App\Admin\Components\Tables;

use App\Models\LandingTemplate;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Illuminate\Contracts\Support\Renderable;

class LandingTemplateTable extends LazyRenderable implements Renderable
{
    public function grid(): Grid
    {
        return Grid::make(LandingTemplate::query()->where('status', '=', LandingTemplate::STATUS_ACTIVE),
            function (Grid $grid) {
                $grid->disableCreateButton();
                $grid->disableRefreshButton();
                $grid->disableActions();

                $grid->quickSearch(['name']);

                $grid->column('id', 'Id');
                $grid->column('name', 'Name');
                $grid->column('targetUrl.name', 'Target Url Name');
                $grid->column('status', 'Status')->label();
                $grid->column('created_at_formatted', 'Created');
            });
    }
}

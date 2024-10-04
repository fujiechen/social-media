<?php

namespace App\Admin\Components\Tables;

use App\Models\ResourceActor;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Illuminate\Contracts\Support\Renderable;

class ResourceActorTable extends LazyRenderable implements Renderable
{
    public function grid(): Grid
    {
        return Grid::make(ResourceActor::query()->with(['resource']),
            function (Grid $grid) {
                $grid->disableCreateButton();
                $grid->disableRefreshButton();
                $grid->disableActions();

                $grid->quickSearch(['name']);

                $grid->column('id', );
                $grid->column('resource.name', admin_trans_field('resource_name'));
                $grid->column('name')->width('30%');
                $grid->column('country');
                $grid->column('created_at');
            });
    }
}

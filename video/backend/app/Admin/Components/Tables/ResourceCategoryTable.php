<?php

namespace App\Admin\Components\Tables;

use App\Models\ResourceCategory;
use App\Models\ResourceTag;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Illuminate\Contracts\Support\Renderable;

class ResourceCategoryTable extends LazyRenderable implements Renderable
{
    public function grid(): Grid
    {
        return Grid::make(ResourceCategory::query()->with(['resource']),
            function (Grid $grid) {
                $grid->disableCreateButton();
                $grid->disableRefreshButton();
                $grid->disableActions();

                $grid->quickSearch(['name']);

                $grid->column('id')->sortable();
                $grid->column('resource.name', admin_trans_field('resource'))->sortable('resource_id');
                $grid->column('name')->sortable()->width('30%');
                $grid->column('created_at');
            });
    }
}

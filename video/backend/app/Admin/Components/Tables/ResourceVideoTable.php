<?php

namespace App\Admin\Components\Tables;

use App\Models\ResourceVideo;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Illuminate\Contracts\Support\Renderable;

class ResourceVideoTable extends LazyRenderable implements Renderable
{
    public function grid(): Grid
    {
        return Grid::make(ResourceVideo::query()->with(['resource']),
            function (Grid $grid) {
                $grid->disableCreateButton();
                $grid->disableRefreshButton();
                $grid->disableActions();

                $grid->quickSearch(['name']);

                $grid->column('id')->sortable();
                $grid->column('resource.name', admin_trans_field('resource'))->sortable();
                $grid->column('name');
                $grid->column('resource_video_url', admin_trans_field('url'))->link()->width('20%');
            });
    }
}

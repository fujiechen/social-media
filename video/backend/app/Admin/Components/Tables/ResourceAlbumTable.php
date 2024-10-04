<?php

namespace App\Admin\Components\Tables;

use App\Models\ResourceAlbum;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Illuminate\Contracts\Support\Renderable;

class ResourceAlbumTable extends LazyRenderable implements Renderable
{
    public function grid(): Grid
    {
        return Grid::make(ResourceAlbum::query()->with(['resource']),
            function (Grid $grid) {
                $grid->disableCreateButton();
                $grid->disableRefreshButton();
                $grid->disableActions();

                $grid->quickSearch(['name']);

                $grid->column('id')->sortable();
                $grid->column('resource.name', admin_trans_field('resource'))->sortable();
                $grid->column('name');
                $grid->column('resource_album_url', admin_trans_field('url'))->link()->width('20%');
            });
    }
}

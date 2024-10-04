<?php

namespace App\Admin\Components\Tables;

use App\Models\Media;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Str;

class MediaTable extends LazyRenderable implements Renderable
{
    public function grid(): Grid
    {
        return Grid::make(
            Media::query(),
            function (Grid $grid) {
                $grid->disableCreateButton();
                $grid->disableRefreshButton();
                $grid->disableActions();

                $grid->quickSearch(['id', 'name']);

                $grid->selector(function (Grid\Tools\Selector $selector) {
                    $selector->select('mediaable_type', admin_trans_field('type'), [
                        Media::toMediaableType(Media::TYPE_VIDEO) => admin_trans_option('video', 'media_type'),
                        Media::toMediaableType(Media::TYPE_SERIES) => admin_trans_option('series', 'media_type'),
                        Media::toMediaableType(Media::TYPE_ALBUM) => admin_trans_option('album', 'media_type'),
                    ]);

                    $selector->select('media_permission', admin_trans_field('media_permission'), [
                        Media::MEDIA_PERMISSION_ROLE => admin_trans_option('role', 'media_permission'),
                        Media::MEDIA_PERMISSION_SUBSCRIPTION => admin_trans_option('subscription', 'media_permission'),
                        Media::MEDIA_PERMISSION_PURCHASE => admin_trans_option('purchase', 'media_permission'),
                    ]);

                    $selector->select('status', admin_trans_field('media_status'), [
                        Media::STATUS_DELETED => admin_trans_option('deleted', 'media_status'),
                        Media::STATUS_DRAFT => admin_trans_option('draft', 'media_status'),
                        Media::STATUS_READY => admin_trans_option('ready', 'media_status'),
                        Media::STATUS_ACTIVE => admin_trans_option('active', 'media_status'),
                    ]);
                });

                $grid->column('id')->sortable();
                $grid->column('name')->width('15%')->sortable();
                $grid->column('status')->label('danger');
                $grid->column('user.nickname', admin_trans_field('user'))->sortable('user_id');
                $grid->column('type')->label('warning')->sortable('mediaable_type');
                $grid->column('created_at_formatted');
            });
    }
}

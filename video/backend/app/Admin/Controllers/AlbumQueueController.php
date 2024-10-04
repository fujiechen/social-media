<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\ResourceTable;
use App\Models\AlbumQueue;
use App\Models\Resource;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;
use Illuminate\Support\Str;

class AlbumQueueController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(AlbumQueue::query()->with(['resource']),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();

                $grid->selector(function (Grid\Tools\Selector $selector) {
                    $selector->select('mid',
                        admin_trans_field('queue_media').':', [
                            true => admin_trans_option('true', 'attached'),
                            false => admin_trans_option('false', 'attached'),
                        ], function ($query, $value) {
                            $query->select(['album_queues.*', 'album_queues.id as mid']);
                            $query->leftJoin('media_queues', 'media_queues.id', '=', 'album_queues.media_queue_id');
                            if ($value[0]) {
                                $query->whereNotNull('media_queues.id');
                            } else {
                                $query->whereNull('media_queues.id');
                            }
                            $query->groupBy('album_queues.id');
                        });

                    $selector->select('sid',
                        admin_trans_field('queue_series').':', [
                            true => admin_trans_option('true', 'attached'),
                            false => admin_trans_option('false', 'attached'),
                        ], function ($query, $value) {
                            $query->select(['album_queues.*', 'album_queues.id as sid']);
                            $query->leftJoin('series_queues', 'series_queues.id', '=', 'album_queues.series_queue_id');
                            if ($value[0]) {
                                $query->whereNotNull('series_queues.id');
                            } else {
                                $query->whereNull('series_queues.id');
                            }
                            $query->groupBy('album_queues.id');
                        });

                    $selector->select('pid',
                        admin_trans_field('queue_playlist').':', [
                            true => admin_trans_option('true', 'attached'),
                            false => admin_trans_option('false', 'attached'),
                        ], function ($query, $value) {
                            $query->select(['album_queues.*', 'album_queues.id as pid']);
                            $query->leftJoin('playlist_queues', 'playlist_queues.id', '=', 'album_queues.playlist_queue_id');
                            if ($value[0]) {
                                $query->whereNotNull('playlist_queues.id');
                            } else {
                                $query->whereNull('playlist_queues.id');
                            }
                            $query->groupBy('album_queues.id');
                        });

                    $selector->select('status',
                        admin_trans_field('queue_status').':', [
                            AlbumQueue::STATUS_PENDING => admin_trans_option('pending', 'queue_status'),
                            AlbumQueue::STATUS_STARTED => admin_trans_option('started', 'queue_status'),
                            AlbumQueue::STATUS_COMPLETED =>admin_trans_option('completed', 'queue_status'),
                            AlbumQueue::STATUS_ERROR => admin_trans_option('error', 'queue_status'),
                        ]);
                });

                $grid->quickCreate(function (Grid\Tools\QuickCreate $create) {
                    $create->select('resource_id', admin_trans_field('resource'))
                        ->options(Resource::query()->pluck('name', 'id')->toArray());
                    $create->text('resource_album_url', admin_trans_field('url'));
                });

                $grid->column('id')->sortable()->width('5%');
                $grid->column('resource.name', admin_trans_field('resource'));
                $grid->column('resource_album_url', admin_trans_field('url'))->display(function ($url) {
                    return '<a target=_blank href="' . $url . '">'.admin_trans_field('数据源链接').'</a>';
                });
                $grid->column('status')->display(function ($status) {
                    return admin_trans_option($status, 'queue_status');
                });
                $grid->column('series_queue_id', admin_trans_field('queue_series_attached'))->display(function ($data) {
                    return '<a href=/admin/seriesQueue/' . $data . '>' . $data . '</a>';
                });
                $grid->column('playlist_queue_id', admin_trans_field('queue_playlist_attached'))->display(function ($data) {
                    return '<a href=/admin/playlistQueue/' . $data . '>' . $data . '</a>';
                });
                $grid->column('media_queue_id', admin_trans_field('queue_media_attached'))->display(function ($data) {
                    return '<a href=/admin/mediaQueue/' . $data . '>' . $data . '</a>';
                });
                $grid->column('created_at_formatted', admin_trans_field('created_at'));
            });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id): Show
    {
        return Show::make($id,
            AlbumQueue::query()
                ->with(['resource'])
            , function (Show $show) {
                $show->field('id');
                $show->field('resource.name', admin_trans_field('resource'));
                $show->field('resource_album_url', admin_trans_field('url'))->link();
                $show->field('status')->as(function ($status) {
                    return admin_trans_option($status, 'queue_status');
                })->label();
                if (!empty($show->model()->response)) {
                    $show->field('response')->json();
                }
                $show->field('created_at');
            });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        return Form::make(AlbumQueue::query(),
            function (Form $form) {
                $form->display('id');
                $form->selectTable('resource_id', admin_trans_field('resource'))
                    ->from(ResourceTable::make())
                    ->model(Resource::class, 'id', 'name')
                    ->required();
                $form->text('resource_album_url', 'URL')->required()
                    ->rules(function (Form $form) {
                        if (!$form->model()->id) {
                            return 'unique:album_queues,resource_album_url';
                        }
                    });
                if ($form->isEditing()) {
                    $form->select('status')
                        ->options([
                            AlbumQueue::STATUS_PENDING => admin_trans_option('pending', 'queue_status'),
                            AlbumQueue::STATUS_STARTED => admin_trans_option('started', 'queue_status'),
                            AlbumQueue::STATUS_COMPLETED =>admin_trans_option('completed', 'queue_status'),
                            AlbumQueue::STATUS_ERROR => admin_trans_option('error', 'queue_status'),
                        ])
                        ->required();
                }
            });
    }

    public function title(): string
    {
        return admin_trans_label('albumQueue');
    }

    public function routeName(): string
    {
        return 'albumQueue';
    }
}

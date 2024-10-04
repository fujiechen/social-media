<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\ResourceTable;
use App\Models\PlaylistQueue;
use App\Models\Resource;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;
use Illuminate\Support\Str;

class PlaylistQueueController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(PlaylistQueue::query()->with(['resource']),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();

                $grid->selector(function (Grid\Tools\Selector $selector) {
                    $selector->select('status',
                        admin_trans_field('queue_status').':', [
                            PlaylistQueue::STATUS_PENDING => admin_trans_option('pending', 'queue_status'),
                            PlaylistQueue::STATUS_STARTED => admin_trans_option('started', 'queue_status'),
                            PlaylistQueue::STATUS_COMPLETED => admin_trans_option('completed', 'queue_status'),
                            PlaylistQueue::STATUS_ERROR => admin_trans_option('error', 'queue_status'),
                        ]);
                });

                $grid->quickCreate(function (Grid\Tools\QuickCreate $create) {
                    $create->select('resource_id', admin_trans_field('resource'))
                        ->options(Resource::query()->pluck('name', 'id')->toArray());
                    $create->text('resource_playlist_url', admin_trans_field('url'));
                });

                $grid->column('id')->sortable()->width('5%');
                $grid->column('resource.name', admin_trans_field('resource'));
                $grid->column('resource_playlist_url', admin_trans_field('url'))->link()->width('20%');
                $grid->column('status', admin_trans_field('queue_status'))->display(function ($status) {
                    return admin_trans_option($status, 'queue_status');
                });
                $grid->column('media_queue_id', admin_trans_field('media_queue_attached'))->display(function($data) {
                    return '<a href=/admin/mediaQueue/' . $data . '>' . $data . '</a>';
                });
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
            PlaylistQueue::query()
                ->with(['resource'])
            , function (Show $show) {
                $show->field('id');
                $show->field('resource.name', admin_trans_field('resource'));
                $show->field('resource_playlist_url', admin_trans_field('url'))->link();
                $show->field('status', admin_trans_field('queue_status'))->as(function ($status) {
                    return admin_trans_option($status, 'queue_status');
                })->label();
                if (!empty($show->model()->response)) {
                    $show->field('response', admin_trans_field('response'))->json();
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
        return Form::make(PlaylistQueue::query(),
            function (Form $form) {
                $form->display('id');
                $form->selectTable('resource_id', admin_trans_field('resource'))
                    ->from(ResourceTable::make())
                    ->model(Resource::class, 'id', 'name')
                    ->required();
                $form->text('resource_playlist_url', admin_trans_field('url'))->required();
                if ($form->isEditing()) {
                    $form->select('status', admin_trans_field('queue_status'))
                        ->options([
                            PlaylistQueue::STATUS_PENDING => admin_trans_option('pending', 'queue_status'),
                            PlaylistQueue::STATUS_STARTED => admin_trans_option('started', 'queue_status'),
                            PlaylistQueue::STATUS_COMPLETED => admin_trans_option('completed', 'queue_status'),
                            PlaylistQueue::STATUS_ERROR => admin_trans_option('error', 'queue_status'),
                        ])
                        ->required();
                }
            });
    }

    public function title(): string
    {
        return admin_trans_label('playlistQueue');
    }

    public function routeName(): string
    {
        return 'playlistQueue';
    }
}

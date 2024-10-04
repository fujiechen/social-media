<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\ResourceTable;
use App\Dtos\AlbumQueueDto;
use App\Dtos\SeriesQueueDto;
use App\Dtos\UploadFileDto;
use App\Dtos\VideoQueueDto;
use App\Models\AlbumQueue;
use App\Models\File;
use App\Models\Resource;
use App\Models\SeriesQueue;
use App\Models\VideoQueue;
use App\Services\SeriesQueueService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Http\JsonResponse;
use Dcat\Admin\Show;
use Exception;
use Illuminate\Support\Str;

class SeriesQueueController extends AdminController
{
    private SeriesQueueService $seriesQueueService;

    public function __construct(SeriesQueueService $seriesQueueService)
    {
        $this->seriesQueueService = $seriesQueueService;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(SeriesQueue::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();
                $grid->disableEditButton();

                $grid->selector(function (Grid\Tools\Selector $selector) {
                    $selector->select('mid',
                        admin_trans_field('media_queue').':', [
                            true => admin_trans_option('true', 'attached'),
                            false => admin_trans_option('false', 'attached'),
                        ], function ($query, $value) {
                            $query->select(['series_queues.*', 'series_queues.id as mid']);
                            $query->leftJoin('media_queues', 'media_queues.id', '=', 'series_queues.media_queue_id');
                            if ($value[0]) {
                                $query->whereNotNull('media_queues.id');
                            } else {
                                $query->whereNull('media_queues.id');
                            }
                            $query->groupBy('series_queues.id');
                        });

                    $selector->select('sid',
                        admin_trans_field('series').':', [
                            true => admin_trans_option('true', 'attached'),
                            false => admin_trans_option('false', 'attached'),
                        ], function ($query, $value) {
                            $query->select(['series_queues.*', 'series_queues.id as sid']);
                            $query->leftJoin('series', 'series.id', '=', 'series_queues.series_id');
                            if ($value[0]) {
                                $query->whereNotNull('series.id');
                            } else {
                                $query->whereNull('series.id');
                            }
                            $query->groupBy('series_queues.id');
                        });


                    $selector->select('status',
                        admin_trans_field('queue_status').':', [
                            SeriesQueue::STATUS_PENDING => admin_trans_option('pending', 'queue_status'),
                            SeriesQueue::STATUS_STARTED => admin_trans_option('started', 'queue_status'),
                            SeriesQueue::STATUS_COMPLETED => admin_trans_option('completed', 'queue_status'),
                            SeriesQueue::STATUS_ERROR => admin_trans_option('error', 'queue_status'),
                        ]);
                });

                $grid->column('id')->sortable();
                $grid->column('name')->sortable();
                $grid->column('status')->display(function ($status) {
                    return admin_trans_option($status, 'queue_status');
                });
                $grid->column('series_id', admin_trans_field('series_attached'))->display(function ($data) {
                    return '<a href=/admin/series/' . $data . '>' . $data . '</a>';
                });
                $grid->column('media_queue_id', admin_trans_field('media_queue_attached'))->display(function ($data) {
                    return '<a href=/admin/mediaQueue/' . $data . '>' . $data . '</a>';
                });
                $grid->column('created_at_formatted');
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
        return Show::make($id, SeriesQueue::query(), function (Show $show) {
            $show->disableEditButton();
            $show->disableDeleteButton();

            $show->field('id');
            $show->field('name');
            $show->field('description');
            $show->field('series_id', admin_trans_field('series'))->as(function ($seriesId) {
                return '<a href=/admin/series/' . $seriesId . '>' . $seriesId . '</a>';
            })->link();
            $show->field('status')->as(function ($status) {
                return admin_trans_option($status, 'queue_status');
            });
            $show->field('created_at');

            $show->relation('videoQueues', admin_trans_field('video_queue'), function ($model) {
                $grid = new Grid(VideoQueue::class);

                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableCreateButton();
                $grid->disablePagination();
                $grid->disableActions();

                $grid->model()->where('series_queue_id', $model->id)->orderBy('id', 'desc');
                $grid->column('id')->sortable();
                $grid->column('resource.name', admin_trans_field('resource'));
                $grid->column('resource_video_url', admin_trans_field('url'))->display(function ($url) {
                    return '<a target=_blank href="' . $url . '">Resource Url</a>';
                });
                $grid->column('status')->display(function ($status) {
                    return admin_trans_option($status, 'queue_status');
                })->label();
                $grid->column('resource_video_id', admin_trans_field('resource_video_attached'))->display(function ($data) {
                    return '<a href=/admin/resourceVideo/' . $data . '>' . $data . '</a>';
                });
                $grid->column('video_id', admin_trans_field('video_attached'))->display(function ($data) {
                    return '<a href=/admin/video/' . $data . '>' . $data . '</a>';
                });
                $grid->column('created_at_formatted');
                return $grid;
            });

            $show->relation('albumQueues', admin_trans_field('album_queue'), function ($model) {
                $grid = new Grid(AlbumQueue::class);

                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableCreateButton();
                $grid->disablePagination();
                $grid->disableActions();

                $grid->model()->where('series_queue_id', $model->id)->orderBy('id', 'desc');

                $grid->column('id')->sortable();
                $grid->column('resource.name', admin_trans_field('resource'));
                $grid->column('resource_album_url', admin_trans_field('url'))->display(function ($url) {
                    return '<a target=_blank href="' . $url . '">'.admin_trans_field('resource_url').'</a>';
                });
                $grid->column('status')->display(function ($status) {
                    return admin_trans_option($status, 'queue_status');
                })->label();
                $grid->column('resource_album_id', admin_trans_field('resource_album_attached'))->display(function ($data) {
                    return '<a href=/admin/resourceAlbum/' . $data . '>' . $data . '</a>';
                });
                $grid->column('album_id', admin_trans_field('album_attached'))->display(function ($data) {
                    return '<a href=/admin/album/' . $data . '>' . $data . '</a>';
                });
                $grid->column('created_at_formatted');
                return $grid;
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        return Form::make(SeriesQueue::query(),
            function (Form $form) {
                $form->disableDeleteButton();
                $form->display('id');
                $form->text('name')->required();
                $form->textarea('description');

                $form->image('thumbnail_file_path', admin_trans_field('thumbnail_file'))
                    ->url('ajax/upload')
                    ->autoUpload()
                    ->removable()
                    ->required();

                $form->table('videoQueue', admin_trans_field('video_queue'),function (Form\NestedForm $table) {
                    $table->selectTable('resource_id', admin_trans_field('resource'))
                        ->from(ResourceTable::make())
                        ->model(Resource::class, 'id', 'name')
                        ->width('30%');
                    $table->url('resource_video_url', admin_trans_field('url'));
                });

                $form->table('albumQueue', admin_trans_field('album_queue'), function (Form\NestedForm $table) {
                    $table->selectTable('resource_id', admin_trans_field('resource'))
                        ->from(ResourceTable::make())
                        ->model(Resource::class, 'id', 'name')
                        ->width('30%');
                    $table->url('resource_album_url', admin_trans_field('url'));
                });
            });
    }

    private function save(): JsonResponse
    {
        $videoQueueDtos = [];
        foreach (request()->input('videoQueue', []) as $videoQueue) {
            if (isset($videoQueue['_remove_']) && $videoQueue['_remove_'] == 1) {
                continue;
            }

            $videoQueueDtos[] = new VideoQueueDto([
                'resourceId' => $videoQueue['resource_id'],
                'resourceVideoUrl' => $videoQueue['resource_video_url'],
            ]);
        }

        $albumQueueDtos = [];
        foreach (request()->input('albumQueue', []) as $albumQueue) {
            if (isset($albumQueue['_remove_']) && $albumQueue['_remove_'] == 1) {
                continue;
            }

            $albumQueueDtos[] = new AlbumQueueDto([
                'resourceId' => $albumQueue['resource_id'],
                'resourceAlbumUrl' => $albumQueue['resource_album_url'],
            ]);
        }

        $thumbnailFileDto = new UploadFileDto([
            'fileId' => 0,
            'uploadPath' => request()->input('thumbnail_file_path'),
            'bucketType' => File::TYPE_PUBLIC_BUCKET,
        ]);

        $this->seriesQueueService->createSeriesQueue(new SeriesQueueDto([
            'name' => request()->input('name') ? request()->input('name') : '',
            'description' => request()->input('description') ? request()->input('description') : '',
            'videoQueueDtos' => $videoQueueDtos,
            'albumQueueDtos' => $albumQueueDtos,
            'thumbnailFileDto' => $thumbnailFileDto,
        ]));


        return $this->form()
            ->response()
            ->redirect('seriesQueue/')
            ->success(trans('admin.save_succeeded'));
    }

    /**
     * @throws Exception
     */
    public function update($id)
    {
        throw new Exception(admin_trans_label('no_update_allowed'));
    }

    public function store(): JsonResponse
    {
        return $this->save();
    }

    public function title(): string
    {
        return admin_trans_label('seriesQueue');
    }

    public function routeName(): string
    {
        return 'seriesQueue';
    }
}

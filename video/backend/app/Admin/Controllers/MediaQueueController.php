<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\ResourceTable;
use App\Admin\Components\Tables\UserTable;
use App\Dtos\AlbumQueueDto;
use App\Dtos\MediaAlbumQueueDto;
use App\Dtos\MediaPlaylistQueueDto;
use App\Dtos\MediaSeriesQueueDto;
use App\Dtos\MediaVideoQueueDto;
use App\Dtos\PlaylistQueueDto;
use App\Dtos\UploadFileDto;
use App\Dtos\VideoQueueDto;
use App\Exceptions\IllegalArgumentException;
use App\Models\AlbumQueue;
use App\Models\File;
use App\Models\MediaQueue;
use App\Models\Resource;
use App\Models\Role;
use App\Models\User;
use App\Models\VideoQueue;
use App\Services\MediaQueueService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Http\JsonResponse;
use Dcat\Admin\Show;
use Exception;
use Illuminate\Support\Str;

class MediaQueueController extends AdminController
{
    private MediaQueueService $mediaQueueService;

    public function __construct(MediaQueueService $mediaQueueService)
    {
        $this->mediaQueueService = $mediaQueueService;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(MediaQueue::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();
                $grid->disableCreateButton();
                $grid->disableEditButton();

                $grid->selector(function (Grid\Tools\Selector $selector) {
                    $selector->select('media_type',
                        admin_trans_field('media_queue_type').':', [
                            MediaQueue::TYPE_VIDEO => admin_trans_option('Video', 'media_queue_types'),
                            MediaQueue::TYPE_ALBUM => admin_trans_option('Album', 'media_queue_types'),
                            MediaQueue::TYPE_SERIES => admin_trans_option('Series', 'media_queue_types'),
                            MediaQueue::TYPE_PLAYLIST => admin_trans_option('Playlist', 'media_queue_types'),
                        ]);

                    $selector->select('status',
                        admin_trans_field('queue_status').':', [
                            MediaQueue::STATUS_PENDING => admin_trans_option('pending', 'queue_status'),
                            MediaQueue::STATUS_STARTED => admin_trans_option('started', 'queue_status'),
                            MediaQueue::STATUS_COMPLETED => admin_trans_option('completed', 'queue_status'),
                            MediaQueue::STATUS_ERROR => admin_trans_option('error', 'queue_status'),
                        ]);
                });

                $grid->column('id')->sortable();
                $grid->column('user.username', admin_trans_field('media_owner'));
                $grid->column('media_type', admin_trans_field('media_queue_type'))->display(function ($type) {
                    return admin_trans_option($type, 'media_queue_types');
                });
                $grid->column('role_names', admin_trans_field('roles'))->label();
                $grid->column('status')->display(function ($status) {
                    return admin_trans_option($status, 'queue_status');
                });
                $grid->column('media_id', admin_trans_field('media_attached'))->display(function($data) {
                    return '<a href=/admin/media/' . $data . '>' . $data . '</a>';
                });
                $grid->column('created_at_formatted');

                $grid->tools('<a class="btn btn-primary pull-right" href="/admin/mediaQueue/create?media_type=Video"> +'.admin_trans_field('create_video_queue').'</a>');
                $grid->tools('<a class="btn btn-primary pull-right" href="/admin/mediaQueue/create?media_type=Series"> +'.admin_trans_field('create_series_queue').'</a>');
                $grid->tools('<a class="btn btn-primary pull-right" href="/admin/mediaQueue/create?media_type=Album"> +'.admin_trans_field('create_album_queue').'</a>');
                $grid->tools('<a class="btn btn-primary pull-right" href="/admin/mediaQueue/create?media_type=Playlist"> +'.admin_trans_field('create_playlist_queue').'</a>');
                $grid->tools('<a class="btn btn-primary pull-right" href="/admin/mediaQueue/create?media_type=Playlist_Batch"> +'.admin_trans_field('create_batch_playlist_queue').'</a>');
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
        return Show::make($id, MediaQueue::query(), function (Show $show) {
            $show->disableEditButton();
            $show->disableDeleteButton();

            $show->field('id');
            $show->field('name');
            $show->field('description');
            $show->field('role_names', admin_trans_field('roles'))->json();
            $show->field('status')->as(function ($status) {
                return admin_trans_option($status, 'queue_status');
            });
            $show->field('media_id', admin_trans_field('media'))->as(function ($mediaId) {
                return '<a href=/admin/media/' . $mediaId . '>' . $mediaId . '</a>';
            })->link();
            $show->field('created_at_formatted');

            $show->relation('videoQueues', admin_trans_field('video_queue'), function ($model) {
                $grid = new Grid(VideoQueue::class);

                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableCreateButton();
                $grid->disablePagination();
                $grid->disableActions();

                $grid->model()->where('media_queue_id', $model->id)->orderBy('id', 'desc');

                $grid->column('id')->sortable();
                $grid->column('resource.name', admin_trans_field('resource'));
                $grid->column('resource_video_url', admin_trans_field('url'))->display(function($url) {
                    return '<a target=_blank href="' . $url . '">'.admin_trans_field('resource_url').'</a>';
                });
                $grid->column('status')->display(function ($status) {
                    return admin_trans_option($status, 'queue_status');
                })->label();
                $grid->column('resource_video_id', admin_trans_field('resource_video_attached'))->display(function($data) {
                    return '<a href=/admin/resourceVideo/' . $data . '>' . $data . '</a>';
                });
                $grid->column('video_id', admin_trans_field('video_attached'))->display(function($data) {
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

                $grid->model()->where('media_queue_id', $model->id)->orderBy('id', 'desc');
                $grid->column('id')->sortable();
                $grid->column('resource.name', admin_trans_field('resource'));
                $grid->column('resource_album_url', admin_trans_field('url'))->display(function($url) {
                    return '<a target=_blank href="' . $url . '">' .admin_trans_field('resource_url').'</a>';
                });
                $grid->column('status')->display(function ($status) {
                    return admin_trans_option($status, 'queue_status');
                })->label();
                $grid->column('resource_album_id', admin_trans_field('resource_album_attached'))->display(function($data) {
                    return '<a href=/admin/resourceAlbum/' . $data . '>' . $data . '</a>';
                });
                $grid->column('album_id', admin_trans_field('album_attached'))->display(function($data) {
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
        return Form::make(MediaQueue::query(),
            function (Form $form) {
                $form->disableDeleteButton();
                $form->html('<b>' . admin_trans_field('create_media') . admin_trans_option(request()->input('media_type'), 'media_batch_types') . '</b>');
                $form->display('id');
                $form->selectTable('user_id', admin_trans_field('media_owner'))
                    ->title(admin_trans_label('select_user'))
                    ->from(UserTable::make())
                    ->model(User::class, 'id', 'username')
                    ->required();

                $form->checkbox('role_ids', admin_trans_field('media_role'))
                    ->help(admin_trans_label('media_available_for_user_roles'))
                    ->options([
                        Role::ROLE_VISITOR_ID => Role::ROLE_VISITOR_NAME,
                        Role::ROLE_USER_ID => Role::ROLE_USER_NAME,
                        Role::ROLE_MEMBERSHIP_ID => Role::ROLE_MEMBERSHIP_NAME
                    ])->required();

                if (request()->input('media_type') == MediaQueue::TYPE_SERIES) {
                    $form->hidden('media_type')->value(MediaQueue::TYPE_SERIES);
                    $form->text('name')->required();
                    $form->textarea('description')->required();
                    $form->image('thumbnail_file_path', admin_trans_field('thumbnail_file'))
                        ->url('ajax/upload')
                        ->autoUpload()
                        ->removable()
                        ->required();

                    $form->table('videoQueue', function (Form\NestedForm $table) {
                        $table->selectTable('resource_id', admin_trans_field('resource'))
                            ->from(ResourceTable::make())
                            ->model(Resource::class, 'id', 'name')
                            ->width('30%');
                        $table->url('resource_video_url', admin_trans_field('url'));
                    });

                    $form->table('albumQueue', function (Form\NestedForm $table) {
                        $table->selectTable('resource_id', admin_trans_field('resource'))
                            ->from(ResourceTable::make())
                            ->model(Resource::class, 'id', 'name')
                            ->width('30%');
                        $table->url('resource_album_url', admin_trans_field('url'));
                    });
                }

                if (request()->input('media_type') == MediaQueue::TYPE_VIDEO) {
                    $form->hidden('media_type')->value(MediaQueue::TYPE_VIDEO);
                    $form->selectTable('videoQueue.0.resource_id', admin_trans_field('resource'))
                        ->from(ResourceTable::make())
                        ->model(Resource::class, 'id', 'name')
                        ->required();

                    $form->url('videoQueue.0.resource_video_url', admin_trans_field('url'))
                        ->required();
                }

                if (request()->input('media_type') == MediaQueue::TYPE_ALBUM) {
                    $form->hidden('media_type')->value(MediaQueue::TYPE_ALBUM);
                    $form->selectTable('albumQueue.0.resource_id', admin_trans_field('resource'))
                        ->from(ResourceTable::make())
                        ->model(Resource::class, 'id', 'name')
                        ->required();

                    $form->url('albumQueue.0.resource_album_url', admin_trans_field('url'))
                        ->required();
                }

                if (request()->input('media_type') == MediaQueue::TYPE_PLAYLIST) {
                    $form->hidden('media_type')->value(MediaQueue::TYPE_PLAYLIST);
                    $form->selectTable('playlistQueue.0.resource_id', admin_trans_field('resource'))
                        ->from(ResourceTable::make())
                        ->model(Resource::class, 'id', 'name')
                        ->required();

                    $form->url('playlistQueue.0.resource_playlist_url', admin_trans_field('url'))
                        ->required();
                }

                if (request()->input('media_type') == MediaQueue::TYPE_PLAYLIST_BATCH) {
                    $form->hidden('media_type')->value(MediaQueue::TYPE_PLAYLIST_BATCH);
                    $form->selectTable('playlistQueue.0.resource_id', admin_trans_field('resource'))
                        ->from(ResourceTable::make())
                        ->model(Resource::class, 'id', 'name')
                        ->required();

                    $form->url('playlistQueue.0.resource_playlist_url', admin_trans_field('base_url'))
                        ->help(admin_trans_label('playlist_queue_url_helper'))
                        ->required();
                }
            });
    }

    private function save(): JsonResponse
    {
        $roleIds = [];
        foreach (request()->input('role_ids') as $roleId) {
            if (!empty($roleId)) {
                $roleIds[] = $roleId;
            }
        }

        $videoQueueDtos = [];
        foreach(request()->input('videoQueue', []) as $videoQueue) {
            if (isset($videoQueue['_remove_']) && $videoQueue['_remove_'] == 1) {
                continue;
            }

            $videoQueueDtos[] = new VideoQueueDto([
                'resourceId' => $videoQueue['resource_id'],
                'resourceVideoUrl' => $videoQueue['resource_video_url'],
            ]);
        }

        $albumQueueDtos = [];
        foreach(request()->input('albumQueue', []) as $albumQueue) {
            if (isset($albumQueue['_remove_']) && $albumQueue['_remove_'] == 1) {
                continue;
            }

            $albumQueueDtos[] = new AlbumQueueDto([
                'resourceId' => $albumQueue['resource_id'],
                'resourceAlbumUrl' => $albumQueue['resource_album_url'],
            ]);
        }

        $playlistQueueDtos = [];
        foreach(request()->input('playlistQueue', []) as $playlistQueue) {
            if (isset($playlistQueue['_remove_']) && $playlistQueue['_remove_'] == 1) {
                continue;
            }

            $playlistQueueDtos[] = new PlaylistQueueDto([
                'resourceId' => $playlistQueue['resource_id'],
                'resourcePlaylistUrl' => $playlistQueue['resource_playlist_url'],
            ]);
        }

        $thumbnailFileDto = new UploadFileDto([
            'fileId' => 0,
            'uploadPath' => request()->input('thumbnail_file_path'),
            'bucketType' => File::TYPE_PUBLIC_BUCKET,
        ]);

        if (request()->input('media_type') == MediaQueue::TYPE_SERIES) {
            $this->mediaQueueService->createMediaSeriesQueue(new MediaSeriesQueueDto([
                'userId' => request()->input('user_id'),
                'mediaRoleIds' => implode(',', $roleIds),
                'mediaType' => request()->input('media_type'),
                'name' => request()->input('name') ? request()->input('name') : '',
                'description' => request()->input('description') ? request()->input('description') : '',
                'videoQueueDtos' => $videoQueueDtos,
                'albumQueueDtos' => $albumQueueDtos,
                'thumbnailFileDto' => $thumbnailFileDto,
            ]));

        } else if (request()->input('media_type') == MediaQueue::TYPE_VIDEO) {
            $this->mediaQueueService->createMediaVideoQueue(new MediaVideoQueueDto([
                'userId' => request()->input('user_id'),
                'mediaRoleIds' => implode(',', $roleIds),
                'mediaType' => request()->input('media_type'),
                'videoQueueDto' => $videoQueueDtos[0],
            ]));
        } else if (request()->input('media_type') == MediaQueue::TYPE_ALBUM) {
            $this->mediaQueueService->createMediaAlbumQueue(new MediaAlbumQueueDto([
                'userId' => request()->input('user_id'),
                'mediaRoleIds' => implode(',', $roleIds),
                'mediaType' => request()->input('media_type'),
                'albumQueueDto' => $albumQueueDtos[0],
            ]));
        } else if (request()->input('media_type') == MediaQueue::TYPE_PLAYLIST) {
            $this->mediaQueueService->createMediaPlaylistQueue(new MediaPlaylistQueueDto([
                'userId' => request()->input('user_id'),
                'mediaRoleIds' => implode(',', $roleIds),
                'mediaType' => request()->input('media_type'),
                'playlistQueueDto' => $playlistQueueDtos[0],
            ]));
        } else if (request()->input('media_type') == MediaQueue::TYPE_PLAYLIST_BATCH) {
            $urls = [];
            $baseUrl = $playlistQueueDtos[0]->resourcePlaylistUrl;

            // Match the pattern @@[start-end]@@
            if (preg_match('/@@\[(\d+)-(\d+)\]@@/', $baseUrl, $matches)) {
                $start = (int)$matches[1];
                $end = (int)$matches[2];

                if ($start <= $end) {
                    $range = range($start, $end); // Define the range based on the pattern

                    foreach ($range as $number) {
                        $urls[] = preg_replace('/@@\[\d+-\d+\]@@/', $number, $baseUrl);
                    }
                }
            } else {
                throw new IllegalArgumentException('url', admin_trans_label('base_url_error'));
            }

            foreach ($urls as $url) {
                $playlistQueueDto = new PlaylistQueueDto([
                    'resourceId' => $playlistQueueDtos[0]->resourceId,
                    'resourcePlaylistUrl' => $url,
                ]);

                $this->mediaQueueService->createMediaPlaylistQueue(new MediaPlaylistQueueDto([
                    'userId' => request()->input('user_id'),
                    'mediaRoleIds' => implode(',', $roleIds),
                    'mediaType' => MediaQueue::TYPE_PLAYLIST,
                    'playlistQueueDto' => $playlistQueueDto,
                ]));
            }
        }

        return $this->form()
            ->response()
            ->redirect('mediaQueue/')
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
        return admin_trans_label('mediaQueue');
    }

    public function routeName(): string
    {
        return 'mediaQueue';
    }
}

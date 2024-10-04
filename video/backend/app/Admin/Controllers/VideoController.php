<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\ActorTable;
use App\Admin\Components\Tables\CategoryTable;
use App\Admin\Components\Tables\MediaTable;
use App\Admin\Components\Tables\PrivateFileTable;
use App\Admin\Components\Tables\PublicFileTable;
use App\Admin\Components\Tables\ResourceVideoTable;
use App\Admin\Components\Tables\TagTable;
use App\Dtos\BucketFileDto;
use App\Dtos\UploadFileDto;
use App\Dtos\VideoDto;
use App\Models\Actor;
use App\Models\Category;
use App\Models\File;
use App\Models\Media;
use App\Models\ResourceVideo;
use App\Models\Tag;
use App\Models\Video;
use App\Services\VideoService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Http\JsonResponse;
use Dcat\Admin\Show;
use InvalidArgumentException;

class VideoController extends AdminController
{
    private VideoService $videoService;

    public function __construct(VideoService $videoService)
    {
        $this->videoService = $videoService;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(Video::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();

                $grid->quickSearch(['id', 'name']);

                $grid->filter(function (Grid\Filter $filter) {
                    $filter->panel();

                    $filter->equal('categories.id', admin_trans_field('category'))
                        ->multipleSelectTable(CategoryTable::make())
                        ->options(function ($v) {
                            if (!$v) {
                                return [];
                            }
                            return Category::find($v)->pluck('name', 'id');
                        })
                        ->width(3);

                    $filter->equal('tags.id', admin_trans_field('tag'))
                        ->multipleSelectTable(TagTable::make())
                        ->options(function ($v) {
                            if (!$v) {
                                return [];
                            }
                            return Tag::find($v)->pluck('name', 'id');
                        })
                        ->width(3);

                    $filter->equal('actors.id', admin_trans_field('actor'))
                        ->multipleSelectTable(ActorTable::make())
                        ->options(function ($v) {
                            if (!$v) {
                                return [];
                            }
                            return Actor::find($v)->pluck('name', 'id');
                        })
                        ->width(3);

                    $filter->equal('medias.id', admin_trans_field('media'))
                        ->multipleSelectTable(MediaTable::make())
                        ->options(function ($v) {
                            if (!$v) {
                                return [];
                            }
                            return Media::find($v)->pluck('name', 'id');
                        })
                        ->width(3);
                });

                $grid->selector(function (Grid\Tools\Selector $selector) {
                    $selector->select('videos.id',
                        admin_trans_field('media').':', [
                            true => admin_trans_option('true', 'attached'),
                            false => admin_trans_option('false', 'attached'),
                        ], function ($query, $value) {
                            $query->select('videos.*');
                            $query->leftJoin('medias', function ($join) {
                                $join->on('medias.mediaable_id', '=', 'videos.id')
                                    ->where('medias.mediaable_type', '=', Video::class);
                            });

                            if ($value[0]) {
                                $query->whereNotNull('medias.id');
                                $query->whereNull('medias.deleted_at');
                            } else {
                                $query->whereNull('medias.id');
                            }
                            $query->groupBy('videos.id');
                        });

                    $selector->select('tid',
                        admin_trans_field('tag').':', [
                            true => admin_trans_option('true', 'attached'),
                            false => admin_trans_option('false', 'attached'),
                        ], function ($query, $value) {
                            $query->select(['videos.*', 'videos.id as tid']);
                            $query->leftJoin('video_tags', 'videos.id', '=', 'video_tags.video_id');

                            if ($value[0]) {
                                $query->whereNotNull('video_tags.id');
                            } else {
                                $query->whereNull('video_tags.id');
                            }
                            $query->groupBy('videos.id');
                        });

                    $selector->select('aid',
                        admin_trans_field('actor').':', [
                            true => admin_trans_option('true', 'attached'),
                            false => admin_trans_option('false', 'attached'),
                        ], function ($query, $value) {
                            $query->select(['videos.*', 'videos.id as aid']);
                            $query->leftJoin('video_actors', 'videos.id', '=', 'video_actors.video_id');

                            if ($value[0]) {
                                $query->whereNotNull('video_actors.id');
                            } else {
                                $query->whereNull('video_actors.id');
                            }
                            $query->groupBy('videos.id');
                        });

                    $selector->select('cid',
                        admin_trans_field('category').':', [
                            true => admin_trans_option('true', 'attached'),
                            false => admin_trans_option('false', 'attached'),
                        ], function ($query, $value) {
                            $query->select(['videos.*', 'videos.id as cid']);
                            $query->leftJoin('video_categories', 'videos.id', '=', 'video_categories.video_id');

                            if ($value[0]) {
                                $query->whereNotNull('video_categories.id');
                            } else {
                                $query->whereNull('video_categories.id');
                            }
                            $query->groupBy('videos.id');
                        });

                });

                $grid->column('id')->sortable()->width('5%')->display(function ($id) {
                    return '<a href="/admin/video/' . $id . '/edit">' . $id . '</a>';
                });
                $grid->column('name')->sortable()->width('40%');
                $grid->column('media_ids', admin_trans_field('media'))->display(function ($mediaIds) {
                    $html = '';
                    foreach ($mediaIds as $mediaId) {
                        $html .= '<a target="_blank" href="/admin/media/' . $mediaId . '/edit">' . admin_trans_field('media').': ' . $mediaId . '</a> <br/>';
                    }
                    return $html;
                });
                $grid->column('tag_names', admin_trans_field('tag'))->label();
                $grid->column('actor_names', admin_trans_field('actor'))->label();
                $grid->column('category_names', admin_trans_field('category'))->label();
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
        return Show::make($id, Video::query(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('created_at');
            $show->field('video_file.url', admin_trans_field('video_url'))->link();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        return Form::make(Video::query(),
            function (Form $form) {
                $form->column(6, function (Form $form) {
                    $form->display('id');
                    $form->hidden('id');
                    $form->text('name');
                    $form->text('description');
                    $form->text('duration_in_seconds');
                    $options = [
                        Video::TYPE_CLOUD => admin_trans_option('Cloud', 'type'),
                        Video::TYPE_UPLOAD => admin_trans_option('Upload', 'type'),
                        Video::TYPE_RESOURCE => admin_trans_option('Resource', 'type'),
                    ];

                    if ($form->isEditing()) {
                        unset($options[Video::TYPE_UPLOAD]);
                        unset($options[Video::TYPE_RESOURCE]);
                    }

                    $form->radio('type')
                        ->required()
                        ->options($options)
                        ->default(Video::TYPE_CLOUD)
                        ->help(admin_trans_label('copy_from_resource'))
                        ->when([Video::TYPE_CLOUD], function (Form $form) {
                            $form->selectTable('thumbnail_file_id', admin_trans_field('thumbnail_file'))
                                ->title(admin_trans_label('select_thumbnail'))
                                ->from(PublicFileTable::make())
                                ->model(File::class, 'id', 'name')
                                ->required();

                            $form->selectTable('video_file_id', admin_trans_field('video_file'))
                                ->title(admin_trans_label('select_video'))
                                ->from(PrivateFileTable::make())
                                ->model(File::class, 'id', 'name')
                                ->required();

                            $form->selectTable('preview_file_id', admin_trans_field('preview_file'))
                                ->title(admin_trans_label('select_preview'))
                                ->from(PublicFileTable::make())
                                ->model(File::class, 'id', 'name');

                            $form->selectTable('download_file_id', admin_trans_field('download_file'))
                                ->title(admin_trans_label('select_download'))
                                ->from(PrivateFileTable::make())
                                ->model(File::class, 'id', 'name');
                        })
                        ->when([Video::TYPE_RESOURCE], function (Form $form) {
                            $form->selectTable('resource_video_id', admin_trans_field('resource_video'))
                                ->title(admin_trans_label('select_resource_video'))
                                ->from(ResourceVideoTable::make())
                                ->model(ResourceVideo::class, 'id', 'name')
                                ->required();
                        })
                        ->when([Video::TYPE_UPLOAD], function (Form $form) {
                            $form->image('thumbnail_file_path', admin_trans_field('thumbnail_file'))
                                ->url('ajax/upload')
                                ->autoUpload()
                                ->removable()
                                ->required();

                            $form->file('video_file_path', admin_trans_field('video_file'))
                                ->url('ajax/upload')
                                ->autoUpload()
                                ->removable()
                                ->required();

                            $form->file('preview_file_path', admin_trans_field('preview_file'))
                                ->url('ajax/upload')
                                ->autoUpload()
                                ->removable();

                            $form->file('download_file_path', admin_trans_field('download_file'))
                                ->url('ajax/upload')
                                ->autoUpload()
                                ->removable();
                        });
                    $form->multipleSelectTable('tag_ids', admin_trans_field('tag'))
                        ->title(admin_trans_label('select_tag'))
                        ->from(TagTable::make())
                        ->model(Tag::class, 'id', 'name');

                    $form->multipleSelectTable('actor_ids', admin_trans_field('actor'))
                        ->title(admin_trans_label('select_actor'))
                        ->from(ActorTable::make())
                        ->model(Actor::class, 'id', 'name');

                    $form->multipleSelectTable('category_ids', admin_trans_field('category'))
                        ->title(admin_trans_label('select_category'))
                        ->from(CategoryTable::make())
                        ->model(Category::class, 'id', 'name');

                    $form->table('meta_json', admin_trans_field('meta'), function ($form) {
                        $form->text('meta_key');
                        $form->text('meta_value');
                    });
                });

                $form->column(6, function (Form $form) {
                    if ($form->model()->resource_video_id) {

                        $form->display('resourceVideo.resource_video_url', admin_trans_field('resource_video_url'))->with(function ($value) {
                            return '<a target=_blank href="' . $value . '">' . $value . '</a>';
                        });

                        $form->display('resourceVideo.name', admin_trans_field('resource_video_name'));
                        $form->display('resourceVideo.description', admin_trans_field('resource_video_description'));
                        $form->display('resourceVideo.duration_in_seconds', admin_trans_field('resource_video_duration_in_seconds'));

                        $form->html('<br/>');
                        $form->html('<br/>');

                        $form->display('resourceVideo.thumbnailFile.name', admin_trans_field('resource_thumbnail_file_name'));
                        $form->display('resourceVideo.file.name', admin_trans_field('resource_video_file'));
                        $form->display('resourceVideo.previewFile.name', admin_trans_field('resource_video_preview_file'));
                        $form->display('resourceVideo.downloadFile.name', admin_trans_field('resource_video_download_file'));


                        $form->display('resourceVideo.resource_tag_names_string', admin_trans_field('resource_tag'));
                        $form->display('resourceVideo.resource_actor_names_string', admin_trans_field('resource_actor'));
                        $form->display('resourceVideo.resource_category_names_string', admin_trans_field('resource_category'));
                    }
                });


            });
    }

    private function save(): JsonResponse
    {
        $metas = [];
        foreach (request()->input('meta_json', []) as $meta) {
            if (isset($meta['_remove_']) && $meta['_remove_'] == 1) {
                continue;
            }

            $metas[] = [
                'meta_key' => $meta['meta_key'],
                'meta_value' => $meta['meta_value'],
            ];
        }

        if (request()->input('type') == Video::TYPE_UPLOAD) {
            $thumbnailFileDto = new UploadFileDto([
                'fileId' => 0,
                'uploadPath' => request()->input('thumbnail_file_path'),
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
            ]);

            $videoFileDto = new UploadFileDto([
                'fileId' => 0,
                'uploadPath' => request()->input('video_file_path'),
                'bucketType' => File::TYPE_PRIVATE_BUCKET,
            ]);

            $previewFileDto = null;
            if (request()->input('preview_file_path')) {
                $previewFileDto = new UploadFileDto([
                    'fileId' => 0,
                    'uploadPath' => request()->input('preview_file_path'),
                    'bucketType' => File::TYPE_PUBLIC_BUCKET,
                ]);
            }

            $downloadFileDto = null;
            if (request()->input('download_file_path')) {
                $downloadFileDto = new UploadFileDto([
                    'fileId' => 0,
                    'uploadPath' => request()->input('download_file_path'),
                    'bucketType' => File::TYPE_PRIVATE_BUCKET,
                ]);
            }

            $dto = new VideoDto([
                'videoId' => request()->input('id') ? request()->input('id') : 0,
                'type' => request()->input('type'),
                'name' => request()->input('name'),
                'description' => request()->input('description') ? request()->input('description') : '',
                'durationInSeconds' => request()->input('duration_in_seconds'),
                'thumbnailFileDto' => $thumbnailFileDto,
                'videoFileDto' => $videoFileDto,
                'previewFileDto' => $previewFileDto,
                'downloadFileDto' => $downloadFileDto,
                'tagIds' => request()->input('tag_ids') ? explode(',', request()->input('tag_ids')) : [],
                'categoryIds' => request()->input('category_ids') ? explode(',', request()->input('category_ids')) : [],
                'actorIds' => request()->input('actor_ids') ? explode(',', request()->input('actor_ids')) : [],
                'metaJson' => $metas
            ]);
        } else if (request()->input('type') == Video::TYPE_CLOUD) {
            $dto = new VideoDto([
                'videoId' => request()->input('id') ? request()->input('id') : 0,
                'type' => request()->input('type'),
                'name' => request()->input('name'),
                'durationInSeconds' => request()->input('duration_in_seconds'),
                'description' => request()->input('description') ? request()->input('description') : '',
                'thumbnailFileDto' => new BucketFileDto(['fileId' => request()->input('thumbnail_file_id')]),
                'videoFileDto' => new BucketFileDto(['fileId' => request()->input('video_file_id')]),
                'previewFileDto' => request()->input('preview_file_id') != null ? new BucketFileDto(['fileId' => request()->input('preview_file_id')]) : null,
                'downloadFileDto' => request()->input('download_file_id') != null ? new BucketFileDto(['fileId' => request()->input('download_file_id')]) : null,
                'tagIds' => request()->input('tag_ids') ? explode(',', request()->input('tag_ids')) : [],
                'categoryIds' => request()->input('category_ids') ? explode(',', request()->input('category_ids')) : [],
                'actorIds' => request()->input('actor_ids') ? explode(',', request()->input('actor_ids')) : [],
                'metaJson' => $metas,
            ]);
        } else if (request()->input('type') == Video::TYPE_RESOURCE) {
            $dto = new VideoDto([
                'videoId' => request()->input('id') ? request()->input('id') : 0,
                'type' => request()->input('type'),
                'name' => request()->input('name') ? request()->input('name') : '',
                'description' => request()->input('description') ? request()->input('description') : '',
                'resourceVideoId' => request()->input('resource_video_id'),
                'tagIds' => request()->input('tag_ids') ? explode(',', request()->input('tag_ids')) : [],
                'categoryIds' => request()->input('category_ids') ? explode(',', request()->input('category_ids')) : [],
                'actorIds' => request()->input('actor_ids') ? explode(',', request()->input('actor_ids')) : [],
                'metaJson' => $metas,
            ]);
        }

        try {
            $video = $this->videoService->updateOrCreateVideo($dto);
        } catch (\Error|\Exception $e) {
            throw new InvalidArgumentException('Invalid Inputs');
        }

        return $this->form()
            ->response()
            ->redirect('video/' . $video->id . '/edit')
            ->success(trans('admin.save_succeeded'));
    }

    public function update($id)
    {
        return $this->save();
    }

    public function store()
    {
        return $this->save();
    }

    public function title(): string
    {
        return admin_trans_label('video');
    }

    public function routeName(): string
    {
        return 'video';
    }
}

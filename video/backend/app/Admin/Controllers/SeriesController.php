<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\ActorTable;
use App\Admin\Components\Tables\AlbumTable;
use App\Admin\Components\Tables\CategoryTable;
use App\Admin\Components\Tables\MediaTable;
use App\Admin\Components\Tables\PublicFileTable;
use App\Admin\Components\Tables\ResourceAlbumTable;
use App\Admin\Components\Tables\ResourceVideoTable;
use App\Admin\Components\Tables\TagTable;
use App\Admin\Components\Tables\VideoTable;
use App\Dtos\AlbumDto;
use App\Dtos\BucketFileDto;
use App\Dtos\SeriesDto;
use App\Dtos\UploadFileDto;
use App\Dtos\VideoDto;
use App\Models\Actor;
use App\Models\Album;
use App\Models\Category;
use App\Models\File;
use App\Models\Media;
use App\Models\ResourceAlbum;
use App\Models\ResourceVideo;
use App\Models\Series;
use App\Models\Tag;
use App\Models\Video;
use App\Services\SeriesService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Http\JsonResponse;
use Dcat\Admin\Show;
use InvalidArgumentException;

class SeriesController extends AdminController
{
    private SeriesService $seriesService;

    public function __construct(SeriesService $seriesService)
    {
        $this->seriesService = $seriesService;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(Series::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();

                $grid->quickSearch(['id', 'name']);

                $grid->filter(function (Grid\Filter $filter) {
                    $filter->panel();

                    $filter->equal('videos.id', admin_trans_field('video'))
                        ->multipleSelectTable(VideoTable::make())
                        ->options(function ($v) {
                            if (!$v) {
                                return [];
                            }
                            return Video::find($v)->pluck('name', 'id');
                        })
                        ->width(4);

                    $filter->equal('albums.id', admin_trans_field('album'))
                        ->multipleSelectTable(AlbumTable::make())
                        ->options(function ($v) {
                            if (!$v) {
                                return [];
                            }
                            return Album::find($v)->pluck('name', 'id');
                        })
                        ->width(4);

                    $filter->equal('medias.id', admin_trans_field('media'))
                        ->multipleSelectTable(MediaTable::make())
                        ->options(function ($v) {
                            if (!$v) {
                                return [];
                            }
                            return Media::find($v)->pluck('name', 'id');
                        })
                        ->width(4);

                });

                $grid->selector(function (Grid\Tools\Selector $selector) {
                    $selector->select('series.id',
                        admin_trans_field('media').':', [
                            true => admin_trans_option('true', 'attached'),
                            false => admin_trans_option('false', 'attached'),
                        ], function ($query, $value) {
                            $query->select('series.*');
                            $query->leftJoin('medias', function ($join) {
                                $join->on('medias.mediaable_id', '=', 'series.id')
                                    ->where('medias.mediaable_type', '=', Series::class);
                            });

                            if ($value[0]) {
                                $query->whereNotNull('medias.id');
                                $query->whereNull('medias.deleted_at');
                            } else {
                                $query->whereNull('medias.id');
                            }
                            $query->groupBy('series.id');
                        });

                    $selector->select('vid',
                        admin_trans_field('video').':', [
                            true => admin_trans_option('true', 'attached'),
                            false => admin_trans_option('false', 'attached'),
                        ], function ($query, $value) {
                            $query->select(['series.*', 'series.id as vid']);
                            $query->leftJoin('series_videos', 'series.id', '=', 'series_videos.series_id');

                            if ($value[0]) {
                                $query->whereNotNull('series_videos.id');
                            } else {
                                $query->whereNull('series_videos.id');
                            }
                            $query->groupBy('series.id');
                        });


                    $selector->select('aid',
                        admin_trans_field('album').':', [
                            true => admin_trans_option('true', 'attached'),
                            false => admin_trans_option('false', 'attached'),
                        ], function ($query, $value) {
                            $query->select(['series.*', 'series.id as vid']);
                            $query->leftJoin('series_albums', 'series.id', '=', 'series_albums.series_id');

                            if ($value[0]) {
                                $query->whereNotNull('series_albums.id');
                            } else {
                                $query->whereNull('series_albums.id');
                            }
                            $query->groupBy('series.id');
                        });
                });

                $grid->column('id')->sortable();
                $grid->column('name')->sortable()->width('20%');
                $grid->column('media_ids', admin_trans_field('media'))->display(function($mediaIds) {
                    $html = '';
                    foreach ($mediaIds as $mediaId) {
                        $html .= '<a target="_blank" href="/admin/media/' . $mediaId . '/edit">'. admin_trans_field('media').': '. $mediaId . '</a> <br/>';
                    }
                    return $html;
                });
                $grid->column('video_names', admin_trans_field('video'))->width('20%')->toArray();
                $grid->column('album_names', admin_trans_field('album'))->width('20%')->toArray();
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
        return Show::make($id, Series::query(), function (Show $show) {
            $show->disableEditButton();

            $show->field('id');
            $show->field('name');
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
        return Form::make(Series::query(),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');
                $form->text('name')->required();
                $form->textarea('description')->required();

                if ($form->isCreating()) {
                    $form->image('thumbnailFile.path', admin_trans_field('thumbnail_file'))
                        ->url('ajax/upload')
                        ->removable()
                        ->autoUpload()
                        ->required();
                } else if ($form->isEditing()) {
                    $form->selectTable('thumbnailFile.id', admin_trans_field('thumbnail_file'))
                        ->title(admin_trans_label('select_thumbnail'))
                        ->from(PublicFileTable::make())
                        ->model(File::class, 'id', 'name');
                }

                $options = [
                    Series::TYPE_CLOUD => admin_trans_option('Cloud','type'),
                    Series::TYPE_UPLOAD => admin_trans_option('Upload','type'),
                    Series::TYPE_RESOURCE => admin_trans_option('Resource','type'),
                ];

                if ($form->isEditing()) {
                    unset($options[Series::TYPE_UPLOAD]);
                    unset($options[Series::TYPE_RESOURCE]);
                }

                $form->radio('type')
                    ->required()
                    ->options($options)
                    ->help(admin_trans_label('copy_from_resource'))
                    ->default(Series::TYPE_CLOUD)
                    ->when([Series::TYPE_CLOUD], function (Form $form) {
                        $form->multipleSelectTable('video_ids', admin_trans_field('video_file'))
                            ->title(admin_trans_label('select_video'))
                            ->from(VideoTable::make())
                            ->model(Video::class, 'id', 'name');

                        $form->multipleSelectTable('album_ids', admin_trans_field('album_file'))
                            ->title(admin_trans_label('select_album'))
                            ->from(AlbumTable::make())
                            ->model(Album::class, 'id', 'name');
                    })
                    ->when([Series::TYPE_RESOURCE], function (Form $form) {
                        $form->table('resource_videos', function (Form\NestedForm $table) {
                            $table->selectTable('id', admin_trans_field('resource_video'))
                                ->title(admin_trans_label('select_resource_video'))
                                ->from(ResourceVideoTable::make())
                                ->model(ResourceVideo::class, 'id', 'name');

                            $table->multipleSelectTable('tag_ids', admin_trans_field('tag'))
                                ->title(admin_trans_label('select_tag'))
                                ->from(TagTable::make())
                                ->model(Tag::class, 'id', 'name');

                            $table->multipleSelectTable('actor_ids', admin_trans_field('actor'))
                                ->title(admin_trans_label('select_actor'))
                                ->from(ActorTable::make())
                                ->model(Tag::class, 'id', 'name');

                            $table->multipleSelectTable('category_ids', admin_trans_field('category'))
                                ->title(admin_trans_label('select_category'))
                                ->from(CategoryTable::make())
                                ->model(Tag::class, 'id', 'name');

                        });

                        $form->table('resource_albums', function (Form\NestedForm $table) {
                            $table->selectTable('id', admin_trans_field('resource_album'))
                                ->title(admin_trans_label('select_resource_album'))
                                ->from(ResourceAlbumTable::make())
                                ->model(ResourceAlbum::class, 'id', 'name');

                            $table->multipleSelectTable('tag_ids', admin_trans_field('tag'))
                                ->title(admin_trans_label('select_tag'))
                                ->from(TagTable::make())
                                ->model(Tag::class, 'id', 'name');

                            $table->multipleSelectTable('actor_ids', admin_trans_field('actor'))
                                ->title(admin_trans_label('select_actor'))
                                ->from(ActorTable::make())
                                ->model(Tag::class, 'id', 'name');

                            $table->multipleSelectTable('category_ids', admin_trans_field('category'))
                                ->title(admin_trans_label('select_category'))
                                ->from(CategoryTable::make())
                                ->model(Tag::class, 'id', 'name');
                        });
                    })
                    ->when([Series::TYPE_UPLOAD], function (Form $form) {
                        $form->array('videos', function (Form\NestedForm $table) {
                            $table->hidden('id');
                            $table->text('name');
                            $table->text('description');

                            $table->image('thumbnailFile.path', admin_trans_field('video_thumbnail'))
                                ->url('ajax/upload')
                                ->removable()
                                ->autoUpload();

                            $table->file('previewFile.path', admin_trans_field('video_preview'))
                                ->url('ajax/upload')
                                ->removable()
                                ->autoUpload();

                            $table->file('videoFile.path', admin_trans_field('video_file'))
                                ->url('ajax/upload')
                                ->removable()
                                ->autoUpload();

                            $table->multipleSelectTable('tag_ids', admin_trans_field('tag'))
                                ->title(admin_trans_label('select_tag'))
                                ->from(TagTable::make())
                                ->model(Tag::class, 'id', 'name');

                            $table->multipleSelectTable('actor_ids', admin_trans_field('actor'))
                                ->title(admin_trans_label('select_actor'))
                                ->from(ActorTable::make())
                                ->model(Tag::class, 'id', 'name');

                            $table->multipleSelectTable('category_ids', admin_trans_field('category'))
                                ->title(admin_trans_label('select_category'))
                                ->from(CategoryTable::make())
                                ->model(Tag::class, 'id', 'name');
                        });

                        $form->array('albums', function (Form\NestedForm $table) {
                            $table->hidden('id');
                            $table->text('name');
                            $table->text('description');

                            $table->multipleImage('image_file_paths', admin_trans_field('album_file'))
                                ->url('ajax/upload')
                                ->removable()
                                ->autoUpload();

                            $table->file('downloadFile.path', admin_trans_field('download_file'))
                                ->url('ajax/upload')
                                ->removable()
                                ->autoUpload();

                            $table->multipleSelectTable('tag_ids', admin_trans_field('tag'))
                                ->title(admin_trans_label('select_tag'))
                                ->from(TagTable::make())
                                ->model(Tag::class, 'id', 'name');

                            $table->multipleSelectTable('actor_ids', admin_trans_field('actor'))
                                ->title(admin_trans_label('select_actor'))
                                ->from(ActorTable::make())
                                ->model(Tag::class, 'id', 'name');

                            $table->multipleSelectTable('category_ids', admin_trans_field('category'))
                                ->title(admin_trans_label('select_category'))
                                ->from(CategoryTable::make())
                                ->model(Tag::class, 'id', 'name');
                        });
                    })
                ;


            });
    }

    private function save(): JsonResponse
    {
        if (isset(request()->input('thumbnailFile')['id'])) {
            $seriesThumbnailFileDto = new BucketFileDto([
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
                'fileId' => request()->input('thumbnailFile')['id'],
            ]);
        } else {
            $seriesThumbnailFileDto = new UploadFileDto([
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
                'fileId' => isset(request()->input('thumbnailFile')['id']) ? request()->input('thumbnailFile')['id'] : 0,
                'uploadPath' => isset(request()->input('thumbnailFile')['path']) ? request()->input('thumbnailFile')['path'] : '',
            ]);
        }

        $seriesDto = new SeriesDto([
            'seriesId' => request()->input('id') ? request()->input('id') : 0,
            'name' => request()->input('name'),
            'description' => request()->input('description'),
            'thumbnailFileDto' => $seriesThumbnailFileDto,
        ]);

        if (request()->input('type') == Video::TYPE_UPLOAD) {
            foreach (request()->input('videos') as $video) {
                if ($video['_remove_'] == 1) {
                    continue;
                }

                $videoThumbnailFileDto = new UploadFileDto([
                    'bucketType' => File::TYPE_PUBLIC_BUCKET,
                    'fileId' => 0,
                    'uploadPath' => $video['thumbnailFile']['path'],
                ]);

                $videoPreviewFileDto = null;
                if (!empty($video['previewFile']['path'])) {
                    $videoPreviewFileDto = new UploadFileDto([
                        'bucketType' => File::TYPE_PUBLIC_BUCKET,
                        'fileId' => 0,
                        'uploadPath' => $video['previewFile']['path'],
                    ]);
                }

                $videoFileDto = new UploadFileDto([
                    'bucketType' => File::TYPE_PRIVATE_BUCKET,
                    'fileId' => 0,
                    'uploadPath' => $video['videoFile']['path'],
                ]);

                $videoDto = new VideoDto([
                    'videoId' => $video['id'] ?? 0,
                    'type' => Video::TYPE_UPLOAD,
                    'name' => $video['name'],
                    'description' => $video['description'],
                    'thumbnailFileDto' => $videoThumbnailFileDto,
                    'previewFileDto' => $videoPreviewFileDto,
                    'videoFileDto' => $videoFileDto,
                    'tagIds' => isset($video['tag_ids']) ? explode(',', $video['tag_ids']) : [],
                    'categoryIds' => isset($video['category_ids']) ? explode(',', $video['category_ids']) : [],
                    'actorIds' => isset($video['actor_ids']) ? explode(',', $video['actor_ids']) : [],
                ]);

                $seriesDto->videoDtos[] = $videoDto;
            }

            foreach (request()->input('albums') as $album) {
                if ($album['_remove_'] == 1) {
                    continue;
                }

                $imageFileDtos = [];
                foreach (explode(',', $album['image_file_paths']) as $imageFilePath) {
                    $imageFileDtos[] = new UploadFileDto([
                        'fileId' => 0,
                        'bucketType' => File::TYPE_PRIVATE_BUCKET,
                        'uploadPath' => $imageFilePath,
                    ]);
                }

                $downloadFileDto = null;
                if (!empty($album['downloadFile']['path'])) {
                    $downloadFileDto = new UploadFileDto([
                        'bucketType' => File::TYPE_PUBLIC_BUCKET,
                        'fileId' => 0,
                        'uploadPath' => $album['downloadFile']['path'],
                    ]);
                }

                $albumDto = new AlbumDto([
                    'albumId' => $video['id'] ?? 0,
                    'type' => Album::TYPE_UPLOAD,
                    'name' => $album['name'],
                    'description' => $album['description'],
                    'downloadFileDto' => $downloadFileDto,
                    'imageFileDtos' => $imageFileDtos,
                    'tagIds' => isset($video['tag_ids']) ? explode(',', $video['tag_ids']) : [],
                    'categoryIds' => isset($video['category_ids']) ? explode(',', $video['category_ids']) : [],
                    'actorIds' => isset($video['actor_ids']) ? explode(',', $video['actor_ids']) : [],
                ]);

                $seriesDto->albumDtos[] = $albumDto;
            }


        } else if (request()->input('type') == Series::TYPE_CLOUD) {
            if (request()->input('video_ids')) {
                foreach (explode(',' , request()->input('video_ids')) as $videoId) {
                    $videoDto = new VideoDto([
                        'videoId' => $videoId,
                        'type' => Video::TYPE_CLOUD,
                    ]);
                    $seriesDto->videoDtos[] = $videoDto;
                }
            }

            if (request()->input('album_ids')) {
                foreach (explode(',' , request()->input('album_ids')) as $albumId) {
                    $albumDto = new AlbumDto([
                        'albumId' => $albumId,
                        'type' => Album::TYPE_CLOUD,
                    ]);
                    $seriesDto->albumDtos[] = $albumDto;
                }
            }
        } else if (request()->input('type') == Video::TYPE_RESOURCE) {
            foreach (request()->input('resource_videos') as $resourceVideo) {
                if ($resourceVideo['_remove_'] == 1) {
                    continue;
                }

                $videoDto = new VideoDto([
                    'videoId' => $video['id'] ?? 0,
                    'type' => Video::TYPE_RESOURCE,
                    'resourceVideoId' => $resourceVideo['id'],
                    'tagIds' => $resourceVideo['tag_ids'] ? explode(',', $resourceVideo['tag_ids']) : [],
                    'categoryIds' => $resourceVideo['category_ids'] ? explode(',', $resourceVideo['category_ids']) : [],
                    'actorIds' => $resourceVideo['actor_ids'] ? explode(',', $resourceVideo['actor_ids']) : [],
                ]);
                $seriesDto->videoDtos[] = $videoDto;
            }

            foreach (request()->input('resource_albums') as $resourceAlbum) {
                if ($resourceAlbum['_remove_'] == 1) {
                    continue;
                }

                $albumDto = new AlbumDto([
                    'videoId' => $video['id'] ?? 0,
                    'type' => Video::TYPE_RESOURCE,
                    'resourceAlbumId' => $resourceAlbum['id'],
                    'tagIds' => $resourceAlbum['tag_ids'] ? explode(',', $resourceAlbum['tag_ids']) : [],
                    'categoryIds' => $resourceAlbum['category_ids'] ? explode(',', $resourceAlbum['category_ids']) : [],
                    'actorIds' => $resourceAlbum['actor_ids'] ? explode(',', $resourceAlbum['actor_ids']) : [],
                ]);
                $seriesDto->albumDtos[] = $albumDto;
            }
        }

        try {
            $series = $this->seriesService->updateOrCreateSeries($seriesDto);
        } catch (\Error|\Exception $e) {
            throw new InvalidArgumentException('Invalid Inputs');
        }

        return $this->form()
            ->response()
            ->redirect('series/' . $series->id . '/edit')
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
        return admin_trans_label('series');
    }

    public function routeName(): string
    {
        return 'series';
    }
}

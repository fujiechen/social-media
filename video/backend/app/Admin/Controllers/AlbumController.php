<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\ActorTable;
use App\Admin\Components\Tables\CategoryTable;
use App\Admin\Components\Tables\MediaTable;
use App\Admin\Components\Tables\PrivateFileTable;
use App\Admin\Components\Tables\PublicFileTable;
use App\Admin\Components\Tables\ResourceAlbumTable;
use App\Admin\Components\Tables\TagTable;
use App\Dtos\AlbumDto;
use App\Dtos\BucketFileDto;
use App\Dtos\UploadFileDto;
use App\Models\Actor;
use App\Models\Album;
use App\Models\Category;
use App\Models\File;
use App\Models\Media;
use App\Models\ResourceAlbum;
use App\Models\Tag;
use App\Services\AlbumService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Http\JsonResponse;
use Dcat\Admin\Show;
use InvalidArgumentException;

class AlbumController extends AdminController
{
    private AlbumService $albumService;

    public function __construct(AlbumService $albumService)
    {
        $this->albumService = $albumService;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(Album::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();

                $grid->quickSearch(['name']);

                $grid->filter(function (Grid\Filter $filter) {
                    $filter->panel();

                    $filter->equal('categories.id', admin_trans_field('category'))
                        ->multipleSelectTable(CategoryTable::make())
                        ->options(function ($v) { // 设置编辑数据显示
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
                        admin_trans_field('media'). ':', [
                            true => admin_trans_option('true', 'attached'),
                            false => admin_trans_option('false', 'attached'),
                        ], function ($query, $value) {
                            $query->select('albums.*');
                            $query->leftJoin('medias', function ($join) {
                                $join->on('medias.mediaable_id', '=', 'albums.id')
                                    ->where('medias.mediaable_type', '=', Album::class);
                            });

                            if ($value[0]) {
                                $query->whereNotNull('medias.id');
                                $query->whereNull('medias.deleted_at');
                            } else {
                                $query->whereNull('medias.id');
                            }
                            $query->groupBy('albums.id');
                        });


                    $selector->select('tid',
                        admin_trans_field('tag'). ':', [
                            true => admin_trans_option('true', 'attached'),
                            false => admin_trans_option('false', 'attached'),
                        ], function ($query, $value) {
                            $query->select(['albums.*', 'albums.id as tid']);
                            $query->leftJoin('album_tags', 'albums.id', '=', 'album_tags.album_id');

                            if ($value[0]) {
                                $query->whereNotNull('album_tags.id');
                            } else {
                                $query->whereNull('album_tags.id');
                            }
                            $query->groupBy('albums.id');
                        });

                    $selector->select('aid',
                        admin_trans_field('actor').':', [
                            true => admin_trans_option('true', 'attached'),
                            false => admin_trans_option('false', 'attached'),
                        ], function ($query, $value) {
                            $query->select(['albums.*', 'albums.id as tid']);
                            $query->leftJoin('album_actors', 'albums.id', '=', 'album_actors.album_id');

                            if ($value[0]) {
                                $query->whereNotNull('album_actors.id');
                            } else {
                                $query->whereNull('album_actors.id');
                            }
                            $query->groupBy('albums.id');
                        });

                    $selector->select('cid',
                        admin_trans_field('category').':', [
                            true => admin_trans_option('true', 'attached'),
                            false => admin_trans_option('false', 'attached'),
                        ], function ($query, $value) {
                            $query->select(['albums.*', 'albums.id as cid']);
                            $query->leftJoin('album_categories', 'albums.id', '=', 'album_categories.album_id');

                            if ($value[0]) {
                                $query->whereNotNull('album_categories.id');
                            } else {
                                $query->whereNull('album_categories.id');
                            }
                            $query->groupBy('albums.id');
                        });

                });

                $grid->column('id')->sortable()->width('5%')->display(function ($id) {
                    return '<a href="/admin/album/' . $id . '/edit">' . $id . '</a>';
                });
                $grid->column('name')->sortable()->width('40%');
                $grid->column('media_ids', admin_trans_field('media'))->display(function($mediaIds) {
                    $html = '';
                    foreach ($mediaIds as $mediaId) {
                        $html .= '<a target="_blank" href="/admin/media/' . $mediaId . '/edit">'. admin_trans_field('media').': '. $mediaId . '</a> <br/>';
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
        return Show::make($id, Album::query(), function (Show $show) {
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
        return Form::make(Album::query(),
            function (Form $form) {
                $form->column(6, function(Form $form) {
                    $form->display('id');
                    $form->hidden('id');
                    $form->text('name');
                    $form->text('description');

                    $options = [
                        Album::TYPE_CLOUD => admin_trans_option('Cloud','type'),
                        Album::TYPE_UPLOAD => admin_trans_option('Upload','type'),
                        Album::TYPE_RESOURCE => admin_trans_option('Resource','type'),
                    ];

                    if ($form->isEditing()) {
                        unset($options[Album::TYPE_UPLOAD]);
                        unset($options[Album::TYPE_RESOURCE]);
                        $form->model()->type = Album::TYPE_CLOUD;
                    }

                    $form->radio('type')
                        ->required()
                        ->options($options)
                        ->default(Album::TYPE_CLOUD)
                        ->help(admin_trans_label('choose_resource_auto_fill'))
                        ->when([Album::TYPE_CLOUD], function (Form $form) {
                            $form->selectTable('thumbnail_file_id', admin_trans_field('thumbnail_file'))
                                ->title(admin_trans_label('select_thumbnail_file'))
                                ->from(PublicFileTable::make())
                                ->model(File::class, 'id', 'name');

                            $form->multipleSelectTable('image_file_ids', admin_trans_field('image_file'))
                                ->title(admin_trans_label('select_image_file'))
                                ->from(PrivateFileTable::make())
                                ->model(File::class, 'id', 'name');

                            $form->selectTable('download_file_id', admin_trans_field('download_file'))
                                ->title(admin_trans_label('select_download_file'))
                                ->from(PrivateFileTable::make())
                                ->model(File::class, 'id', 'name');
                        })
                        ->when([Album::TYPE_UPLOAD], function (Form $form) {
                            $form->file('thumbnailFile.path', admin_trans_field('thumbnail_file'))
                                ->url('ajax/upload')
                                ->autoUpload()
                                ->removable();

                            $form->multipleImage('image_file_paths', admin_trans_field('image_file'))
                                ->url('ajax/upload')
                                ->autoUpload()
                                ->removable()
                                ->required();

                            $form->file('downloadFile.path', admin_trans_field('download_file'))
                                ->url('ajax/upload')
                                ->autoUpload()
                                ->removable();
                        })
                        ->when([Album::TYPE_RESOURCE], function (Form $form) {
                            $form->selectTable('resource_album_id', admin_trans_field('resource_album'))
                                ->title(admin_trans_label('select_resource_album'))
                                ->from(ResourceAlbumTable::make())
                                ->model(ResourceAlbum::class, 'id', 'name')
                                ->required();
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
                        $form->text('meta_key', admin_trans_field('meta_key'));
                        $form->text('meta_value', admin_trans_field('meta_value'));
                    });
                });

                $form->column(6, function(Form $form) {
                    if ($form->model()->resource_album_id) {

                        $form->display('resourceAlbum.resource_album_url', admin_trans_field('resource_album_url'))->with(function ($value) {
                            return '<a target=_blank href="' . $value . '">' . $value . '</a>';
                        });

                        $form->display('resourceAlbum.name', admin_trans_field('resource_album_name'));
                        $form->display('resourceAlbum.description', admin_trans_field('resource_description'));

                        $form->html('<br/>');
                        $form->html('<br/>');

                        $form->display('resourceAlbum.thumbnailFile.name', admin_trans_field('resource_thumbnail_file_name'));
                        $form->display('resourceAlbum.image_file_names_string', admin_trans_field('resource_image_file_name'));
                        $form->display('resourceAlbum.downloadFile.name', admin_trans_field('resource_download_file_name'));


                        $form->display('resourceAlbum.resource_tag_names_string', admin_trans_field('resource_tag'));
                        $form->display('resourceAlbum.resource_actor_names_string', admin_trans_field('resource_actor'));
                        $form->display('resourceAlbum.resource_category_names_string', admin_trans_field('resource_category'));
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

        $albumDto = new AlbumDto([
            'albumId' => request()->input('id') ? request()->input('id') : 0,
            'type' => request()->input('type'),
            'name' => request()->input('name'),
            'description' => request()->input('description'),
            'tagIds' => request()->input('tag_ids') ? explode(',', request()->input('tag_ids')) : [],
            'categoryIds' => request()->input('category_ids') ? explode(',', request()->input('category_ids')) : [],
            'actorIds' => request()->input('actor_ids') ? explode(',', request()->input('actor_ids')) : [],
            'metaJson' => $metas
        ]);

        if (request()->input('type') == Album::TYPE_UPLOAD) {
            foreach (explode(',', request()->input('image_file_paths')) as $imageFilePath) {
                $albumDto->imageFileDtos[] = new UploadFileDto([
                    'fileId' => 0,
                    'bucketType' => File::TYPE_PRIVATE_BUCKET,
                    'uploadPath' => $imageFilePath,
                ]);
            }

            if (isset(request()->input('downloadFile')['path'])) {
                $albumDto->downloadFileDto = new UploadFileDto([
                    'fileId' => isset(request()->input('downloadFile')['id']) ? request()->input('downloadFile')['id'] : 0,
                    'uploadPath' => isset(request()->input('downloadFile')['path']) ? request()->input('downloadFile')['path'] : '',
                    'bucketType' => File::TYPE_PRIVATE_BUCKET,
                ]);
            }

            if (isset(request()->input('thumbnailFile')['path'])) {
                $albumDto->thumbnailFileDto = new UploadFileDto([
                    'fileId' => isset(request()->input('thumbnailFile')['id']) ? request()->input('thumbnailFile')['id'] : 0,
                    'uploadPath' => isset(request()->input('thumbnailFile')['path']) ? request()->input('thumbnailFile')['path'] : '',
                    'bucketType' => File::TYPE_PUBLIC_BUCKET,
                ]);
            }

        } else if (request()->input('type') == Album::TYPE_CLOUD) {
            foreach (explode(',', request()->input('image_file_ids')) as $imageFileId) {
                $albumDto->imageFileDtos[] = new BucketFileDto([
                    'fileId' => $imageFileId,
                    'bucketType' => File::TYPE_PRIVATE_BUCKET,
                ]);
            }

            if (request()->input('download_file_id') !== null) {
                $downloadFile = File::find(request()->input('download_file_id'));
                $albumDto->downloadFileDto = new BucketFileDto([
                    'fileId' => $downloadFile->id,
                    'bucketType' => File::TYPE_PRIVATE_BUCKET,
                ]);
            }

            if (request()->input('thumbnail_file_id') !== null) {
                $thumbnailFile = File::find(request()->input('thumbnail_file_id'));
                $albumDto->thumbnailFileDto = new BucketFileDto([
                    'fileId' => $thumbnailFile->id,
                    'bucketType' => File::TYPE_PUBLIC_BUCKET,
                ]);
            }
        } else if (request()->input('type') == Album::TYPE_RESOURCE) {
            $albumDto = new AlbumDto([
                'albumId' => request()->input('id') ? request()->input('id') : 0,
                'type' => request()->input('type'),
                'name' => request()->input('name'),
                'description' => request()->input('description'),
                'resourceAlbumId' => request()->input('resource_album_id'),
                'tagIds' => request()->input('tag_ids') ? explode(',', request()->input('tag_ids')) : [],
                'categoryIds' => request()->input('category_ids') ? explode(',', request()->input('category_ids')) : [],
                'actorIds' => request()->input('actor_ids') ? explode(',', request()->input('actor_ids')) : [],
                'metaJson' => $metas
            ]);
        }

        try {
            $album = $this->albumService->updateOrCreateAlbum($albumDto);
        } catch (\Error|\Exception $e) {
            throw new InvalidArgumentException('Invalid Inputs');
        }

        return $this->form()
            ->response()
            ->redirect('album/' . $album->id . '/edit')
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
        return admin_trans_label('albums');
    }

    public function routeName(): string
    {
        return 'album';
    }
}

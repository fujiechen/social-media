<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\PrivateFileTable;
use App\Admin\Components\Tables\PublicFileTable;
use App\Admin\Components\Tables\ResourceActorTable;
use App\Admin\Components\Tables\ResourceCategoryTable;
use App\Admin\Components\Tables\ResourceTable;
use App\Admin\Components\Tables\ResourceTagTable;
use App\Dtos\BucketFileDto;
use App\Dtos\ResourceActorDto;
use App\Dtos\ResourceAlbumDto;
use App\Dtos\ResourceCategoryDto;
use App\Dtos\ResourceTagDto;
use App\Dtos\UploadFileDto;
use App\Models\File;
use App\Models\Resource;
use App\Models\ResourceActor;
use App\Models\ResourceAlbum;
use App\Models\ResourceCategory;
use App\Models\ResourceTag;
use App\Services\ResourceAlbumService;
use Dcat\Admin\Form;
use Dcat\Admin\Form\NestedForm;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class ResourceAlbumController extends AdminController
{

    private ResourceAlbumService $resourceAlbumService;

    public function __construct(ResourceAlbumService $resourceAlbumService)
    {
        $this->resourceAlbumService = $resourceAlbumService;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(ResourceAlbum::query()->with(['resource']),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();

                $grid->quickSearch(['name']);

                $grid->selector(function (Grid\Tools\Selector $selector) {
                    $selector->select('resource.id',
                        admin_trans_field('resource').':', Resource::query()
                            ->pluck('name', 'id')
                            ->all());
                    $selector->select('resourceCategories.id',
                        admin_trans_field('resource_category').':', ResourceCategory::query()
                            ->pluck('name', 'id')
                            ->all());
                });

                $grid->filter(function (Grid\Filter $filter) {
                    $filter->panel();

                    $filter->equal('resourceCategories.id', admin_trans_field('resource_category'))
                        ->multipleSelectTable(ResourceCategoryTable::make())
                        ->options(function ($v) {
                            if (!$v) {
                                return [];
                            }
                            return ResourceCategory::find($v)->pluck('name', 'id');
                        })
                        ->width(4);

                    $filter->equal('resourceTags.id', admin_trans_field('resource_tag'))
                        ->multipleSelectTable(ResourceTagTable::make())
                        ->options(function ($v) {
                            if (!$v) {
                                return [];
                            }
                            return ResourceTag::find($v)->pluck('name', 'id');
                        })
                        ->width(4);

                    $filter->equal('resourceActors.id', admin_trans_field('resource_actor'))
                        ->multipleSelectTable(ResourceActorTable::make())
                        ->options(function ($v) {
                            if (!$v) {
                                return [];
                            }
                            return ResourceActor::find($v)->pluck('name', 'id');
                        })
                        ->width(4);
                });

                $grid->column('id')->sortable();
                $grid->column('resource.name', admin_trans_field('resource'))->sortable('resource_id');
                $grid->column('name')->sortable()->limit(50)->width('10%');
                $grid->column('resource_tag_names', admin_trans_field('resource_tag'))->label();
                $grid->column('resource_actor_names', admin_trans_field('resource_actor'))->label();
                $grid->column('resource_category_names', admin_trans_field('resource_category'))->label();
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
        return Show::make($id, ResourceAlbum::query()->with(['resource', 'resourceTags']), function (Show $show) {
            $show->disableEditButton();
            $show->disableDeleteButton();

            $show->field('id');
            $show->field('resource.name', admin_trans_field('resource'));
            $show->field('name');
            $show->field('resource_album_url', admin_trans_field('resource_url'))->link();
            $show->field('thumbnail_file.url', admin_trans_field('thumbnail_url'))->link();
            $show->field('download_file.url', admin_trans_field('download_url'))->link();
            $show->field('created_at_formatted');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        return Form::make(ResourceAlbum::query()
            ->with(['resourceTags', 'resourceCategories', 'resourceActors', 'resourceAlbumFiles']),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');
                $form->text('name')->required();
                $form->textarea('description')->required();
                $form->url('resource_album_url', admin_trans_field('url'))->required();
                $form->selectTable('resource_id', admin_trans_field('resource'))
                    ->from(ResourceTable::make())
                    ->model(Resource::class, 'id', 'name')
                    ->required();


                $options = [
                    ResourceAlbum::TYPE_CLOUD => admin_trans_option('Cloud', 'type'),
                    ResourceAlbum::TYPE_UPLOAD => admin_trans_option('Upload', 'type'),
                ];

                if ($form->isEditing()) {
                    unset($options[ResourceAlbum::TYPE_UPLOAD]);
                    $form->model()->type = ResourceAlbum::TYPE_CLOUD;
                }

                $form->radio('type')
                    ->required()
                    ->options($options)
                    ->default(ResourceAlbum::TYPE_CLOUD)
                    ->when([ResourceAlbum::TYPE_CLOUD], function (Form $form) {
                        $form->selectTable('thumbnailFile.id', admin_trans_field('thumbnail_file'))
                            ->title(admin_trans_label('select_thumbnail'))
                            ->from(PublicFileTable::make())
                            ->model(File::class, 'id', 'name');

                        $form->multipleSelectTable('image_file_ids', admin_trans_field('image_file'))
                            ->title(admin_trans_label('select_image_files'))
                            ->from(PrivateFileTable::make())
                            ->model(File::class, 'id', 'name');

                        $form->selectTable('downloadFile.id', admin_trans_field('download_file'))
                            ->title(admin_trans_label('select_zip_download'))
                            ->from(PrivateFileTable::make())
                            ->model(File::class, 'id', 'name');
                    })
                    ->when([ResourceAlbum::TYPE_UPLOAD], function (Form $form) {
                        $form->file('thumbnailFile.path', admin_trans_field('thumbnail_file'))
                            ->autoUpload()
                            ->url('ajax/upload')
                            ->removable();

                        $form->multipleImage('image_file_paths', admin_trans_field('image_file'))
                            ->autoUpload()
                            ->url('ajax/upload')
                            ->removable()
                            ->required();

                        $form->file('downloadFile.path', admin_trans_field('download_file'))
                            ->autoUpload()
                            ->url('ajax/upload')
                            ->removable();
                    })
                ;

                $form->table('meta_json', admin_trans_field('meta'), function($form) {
                    $form->text('meta_key');
                    $form->text('meta_value');
                })->required();

                $form->table('resourceTags', admin_trans_field('resource_tag'), function (NestedForm $table) {
                    $table->text('name');
                });

                $form->table('resourceActors', admin_trans_field('resource_actor'), function (NestedForm $table) {
                    $table->text('name');
                    $table->text('country');
                });

                $form->table('resourceCategories', admin_trans_field('resource_category'), function (NestedForm $table) {
                    $table->text('name');
                });
            });
    }

    private function save()
    {
        $resourceTagDtos = [];
        foreach (request()->input('resourceTags', []) as $resourceTag) {
            if ($resourceTag['_remove_'] == 1) {
                continue;
            }

            $resourceTagDtos[] = new ResourceTagDto(['name' => $resourceTag['name']]);
        }

        $resourceActorDtos = [];
        foreach (request()->input('resourceActors', []) as $resourceActor) {
            if ($resourceActor['_remove_'] == 1) {
                continue;
            }

            $resourceActorDtos[] = new ResourceActorDto([
                'name' => $resourceActor['name'],
                'country' => $resourceActor['country']
            ]);
        }

        $resourceCategoryDtos = [];
        foreach (request()->input('resourceCategories', []) as $resourceCategory) {
            if ($resourceCategory['_remove_'] == 1) {
                continue;
            }

            $resourceCategoryDtos[] = new ResourceCategoryDto([
                'name' => $resourceCategory['name']
            ]);
        }

        $imageFileDtos = [];
        $downloadFileDto = null;
        $thumbnailFileDto = null;
        if (request()->input('type') == ResourceAlbum::TYPE_UPLOAD) {
            foreach (explode(',', request()->input('image_file_paths')) as $imageFilePath) {
                $imageFileDtos[] = new UploadFileDto([
                    'fileId' => 0,
                    'uploadPath' => $imageFilePath,
                    'bucketType' => File::TYPE_PRIVATE_BUCKET,
                ]);
            }

            if (isset(request()->input('downloadFile')['path'])) {
                $downloadFileDto = new UploadFileDto([
                    'fileId' => isset(request()->input('downloadFile')['id']) ? request()->input('downloadFile')['id'] : 0,
                    'uploadPath' => isset(request()->input('downloadFile')['path']) ? request()->input('downloadFile')['path'] : '',
                    'bucketType' => File::TYPE_PRIVATE_BUCKET,
                ]);
            }

            if (isset(request()->input('thumbnailFile')['path'])) {
                $thumbnailFileDto = new UploadFileDto([
                    'fileId' => isset(request()->input('thumbnailFile')['id']) ? request()->input('thumbnailFile')['id'] : 0,
                    'uploadPath' => isset(request()->input('thumbnailFile')['path']) ? request()->input('thumbnailFile')['path'] : '',
                    'bucketType' => File::TYPE_PUBLIC_BUCKET,
                ]);
            }

        } else if (request()->input('type') == ResourceAlbum::TYPE_CLOUD) {
            foreach (explode(',' , request()->input('image_file_ids')) as $imageFileId) {
                $imageFileDtos[] = new BucketFileDto([
                    'fileId' => $imageFileId,
                    'bucketType' => File::TYPE_PRIVATE_BUCKET,
                ]);
            }

            if (isset(request()->input('downloadFile')['id'])) {
                $downloadFile = File::find(request()->input('downloadFile')['id']);
                $downloadFileDto = new BucketFileDto([
                    'fileId' => $downloadFile->id,
                    'bucketType' => File::TYPE_PRIVATE_BUCKET,
                ]);
            }

            if (isset(request()->input('thumbnailFile')['id'])) {
                $thumbnailFile = File::find(request()->input('thumbnailFile')['id']);
                $thumbnailFileDto = new BucketFileDto([
                    'fileId' => $thumbnailFile->id,
                    'bucketType' => File::TYPE_PUBLIC_BUCKET,
                ]);
            }
        }

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

        $resourceAlbumDto = new ResourceAlbumDto([
            'resourceAlbumId' => request()->input('id') == null ? 0 : request()->input('id'),
            'resourceId' => request()->input('resource_id'),
            'name' => request()->input('name'),
            'description' => request()->input('description', ''),
            'resourceAlbumUrl' => request()->input('resource_album_url'),
            'resourceAlbumFileDtos' => $imageFileDtos,
            'thumbnailFileDto' => $thumbnailFileDto ?? null,
            'downloadFileDto' => $downloadFileDto ?? null,
            'resourceTagDtos' => $resourceTagDtos,
            'resourceActorDtos' => $resourceActorDtos,
            'resourceCategoryDtos' => $resourceCategoryDtos,
            'metaJson' => $metas,
        ]);

        $resourceAlbum = $this->resourceAlbumService->updateOrCreateResourceAlbum($resourceAlbumDto);

        return $this->form()
            ->response()
            ->redirect('resourceAlbum/' . $resourceAlbum->id . '/edit')
            ->success(trans('admin.save_succeeded'));
    }

    public function store()
    {
        return $this->save();
    }

    public function update($id)
    {
        return $this->save();
    }

    public function title(): string
    {
        return admin_trans_label('resourceAlbum');
    }

    public function routeName(): string
    {
        return 'resourceAlbum';
    }
}

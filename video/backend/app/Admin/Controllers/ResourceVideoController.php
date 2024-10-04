<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\PrivateFileTable;
use App\Admin\Components\Tables\PublicFileTable;
use App\Admin\Components\Tables\ResourceActorTable;
use App\Admin\Components\Tables\ResourceCategoryTable;
use App\Admin\Components\Tables\ResourceTable;
use App\Admin\Components\Tables\ResourceTagTable;
use App\Dtos\BucketFileDto;
use App\Dtos\FileDto;
use App\Dtos\ResourceActorDto;
use App\Dtos\ResourceCategoryDto;
use App\Dtos\ResourceTagDto;
use App\Dtos\ResourceVideoDto;
use App\Dtos\UploadFileDto;
use App\Models\File;
use App\Models\Resource;
use App\Models\ResourceActor;
use App\Models\ResourceCategory;
use App\Models\ResourceTag;
use App\Models\ResourceVideo;
use App\Services\FileService;
use App\Services\ResourceVideoService;
use Dcat\Admin\Form;
use Dcat\Admin\Form\NestedForm;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class ResourceVideoController extends AdminController
{

    private ResourceVideoService $resourceVideoService;

    public function __construct(ResourceVideoService $resourceVideoService)
    {
        $this->resourceVideoService = $resourceVideoService;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(ResourceVideo::query()->with(['resource']),
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
                        ->options(function ($v) { // 设置编辑数据显示
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
        return Show::make($id, ResourceVideo::query()->with(['resource', 'resourceTags']), function (Show $show) {
            $show->disableEditButton();
            $show->disableDeleteButton();

            $show->field('id');
            $show->field('resource.name', admin_trans_field('resource'));
            $show->field('name');
            $show->field('resource_video_url', admin_trans_field('resource_video_url'))->link();
            $show->field('thumbnail_file.url', admin_trans_field('thumbnail_url'))->link();
            $show->field('preview_file.url', admin_trans_field('preview_url'))->link();
            $show->field('file.url', admin_trans_field('video_file_url'))->link();
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
        return Form::make(ResourceVideo::query()
            ->with(['resourceTags', 'resourceCategories', 'resourceActors']),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');
                $form->text('name')->required();
                $form->textarea('description')->required();
                $form->url('resource_video_url', admin_trans_field('resource_video_url'))->required();
                $form->text('duration_in_seconds')->required();
                $form->selectTable('resource_id', admin_trans_field('resource'))
                    ->from(ResourceTable::make())
                    ->model(Resource::class, 'id', 'name')
                    ->required();

                if ($form->isCreating()) {
                    $form->file('file.path', admin_trans_field('video'))
                        ->autoUpload()
                        ->url('ajax/upload')
                        ->removable()
                        ->required();
                    $form->image('thumbnailFile.path', admin_trans_field('thumbnail_file'))
                        ->autoUpload()
                        ->url('ajax/upload')
                        ->removable()
                        ->required();
                    $form->file('previewFile.path', admin_trans_field('preview_file'))
                        ->autoUpload()
                        ->url('ajax/upload')
                        ->removable();
                    $form->file('downloadFile.path', admin_trans_field('download_file'))
                        ->autoUpload()
                        ->url('ajax/upload')
                        ->removable();

                } else if ($form->isEditing()) {
                    $form->selectTable('file.id', admin_trans_field('video'))
                        ->from(PrivateFileTable::make())
                        ->model(File::class, 'id', 'name')
                        ->required();
                    $form->selectTable('thumbnailFile.id', admin_trans_field('thumbnail_file'))
                        ->from(PublicFileTable::make())
                        ->model(File::class, 'id', 'name')
                        ->required();
                    $form->selectTable('previewFile.id', admin_trans_field('preview_file'))
                        ->from(PublicFileTable::make())
                        ->model(File::class, 'id', 'name');
                    $form->selectTable('downloadFile.id', admin_trans_field('download_file'))
                        ->from(PrivateFileTable::make())
                        ->model(File::class, 'id', 'name');
                }

                $form->table('meta_json', admin_trans_field('meta'), function ($form) {
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

        if (isset(request()->input('thumbnailFile')['id'])) {
            $thumbnailFile = File::find(request()->input('thumbnailFile')['id']);
            $thumbnailFileDto = new BucketFileDto([
                'fileId' => $thumbnailFile->id,
                'bucketFilePath' => $thumbnailFile->bucket_file_path,
                'bucketFileName' => $thumbnailFile->bucket_file_name,
                'bucketName' => $thumbnailFile->bucket_name,
                'bucketType' => $thumbnailFile->bucket_type,
            ]);
        } else {
            $thumbnailFileDto = new UploadFileDto([
                'fileId' => isset(request()->input('thumbnailFile')['id']) ? request()->input('thumbnailFile')['id'] : 0,
                'uploadPath' => isset(request()->input('thumbnailFile')['path']) ? request()->input('thumbnailFile')['path'] : '',
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
            ]);
        }

        if (isset(request()->input('file')['id'])) {
            $videoFile = File::find(request()->input('file')['id']);
            $videoFileDto = new BucketFileDto([
                'fileId' => $videoFile->id,
                'bucketFilePath' => $videoFile->bucket_file_path,
                'bucketFileName' => $videoFile->bucket_file_name,
                'bucketName' => $videoFile->bucket_name,
                'bucketType' => $videoFile->bucket_type,
            ]);
        } else {
            $videoFileDto = new UploadFileDto([
                'fileId' => isset(request()->input('file')['id']) ? request()->input('file')['id'] : 0,
                'uploadPath' => isset(request()->input('file')['path']) ? request()->input('file')['path'] : '',
                'bucketType' => File::TYPE_PRIVATE_BUCKET,
            ]);
        }


        if (isset(request()->input('previewFile')['id'])) {
            $previewFile = File::find(request()->input('previewFile')['id']);
            $previewFileDto = new BucketFileDto([
                'fileId' => $previewFile->id,
                'bucketFilePath' => $previewFile->bucket_file_path,
                'bucketName' => $previewFile->bucket_name,
                'bucketFileName' => $previewFile->bucket_file_name,
                'bucketType' => $previewFile->bucket_type,
            ]);
        } else if (isset(request()->input('previewFile')['path'])) {
            $previewFileDto = new UploadFileDto([
                'fileId' => isset(request()->input('previewFile')['id']) ? request()->input('previewFile')['id'] : 0,
                'uploadPath' => isset(request()->input('previewFile')['path']) ? request()->input('previewFile')['path'] : '',
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
            ]);
        } else {
            $previewFileDto = null;
        }

        if (isset(request()->input('downloadFile')['id'])) {
            $downloadFile = File::find(request()->input('downloadFile')['id']);
            $downloadFileDto = new BucketFileDto([
                'fileId' => $downloadFile->id,
                'bucketFilePath' => $downloadFile->bucket_file_path,
                'bucketName' => $downloadFile->bucket_name,
                'bucketFileName' => $downloadFile->bucket_file_name,
                'bucketType' => $downloadFile->bucket_type,
            ]);
        } else if (isset(request()->input('downloadFile')['path'])) {
            $downloadFileDto = new UploadFileDto([
                'fileId' => isset(request()->input('downloadFile')['id']) ? request()->input('downloadFile')['id'] : 0,
                'uploadPath' => isset(request()->input('downloadFile')['path']) ? request()->input('downloadFile')['path'] : '',
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
            ]);
        } else {
            $downloadFileDto = null;
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


        $resourceVideoDto = new ResourceVideoDto([
            'resourceVideoId' => request()->input('id') == null ? 0 : request()->input('id'),
            'resourceId' => request()->input('resource_id'),
            'name' => request()->input('name'),
            'description' => request()->input('description', ''),
            'durationInSeconds' => request()->input('duration_in_seconds'),
            'resourceVideoUrl' => request()->input('resource_video_url'),
            'thumbnailFileDto' => $thumbnailFileDto,
            'previewFileDto' => $previewFileDto ?? null,
            'downloadFileDto' => $downloadFileDto ?? null,
            'videoFileDto' => $videoFileDto,
            'resourceTagDtos' => $resourceTagDtos,
            'resourceActorDtos' => $resourceActorDtos,
            'resourceCategoryDtos' => $resourceCategoryDtos,
            'metaJson' => $metas
        ]);

        $resourceVideo = $this->resourceVideoService->updateOrCreateResourceVideo($resourceVideoDto);

        return $this->form()
            ->response()
            ->redirect('resourceVideo/' . $resourceVideo->id . '/edit')
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
        return admin_trans_label('resourceVideo');
    }

    public function routeName(): string
    {
        return 'resourceVideo';
    }
}

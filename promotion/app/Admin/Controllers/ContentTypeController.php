<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\PublicFileTable;
use App\Dtos\BucketFileDto;
use App\Dtos\ContentTypeDto;
use App\Dtos\FileDto;
use App\Models\ContentType;
use App\Models\File;
use App\Services\ContentTypeService;
use Dcat\Admin\Exception\InvalidArgumentException;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Http\JsonResponse;
use Dcat\Admin\Show;
use Error;
use Exception;
use Illuminate\Support\Facades\Log;

class ContentTypeController extends AdminController
{
    private ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    public function store(): JsonResponse
    {
        return $this->save();
    }

    private function save(): JsonResponse
    {
        try {
            if (empty(request()->input('name'))) {
                return new JsonResponse();
            }

            $fileDtos = [];

            $fileType = request()->input('file_type');
            if ($fileType === 'cloud') {
                if (request()->input('file_ids')) {
                    foreach (explode(',', request()->input('file_ids')) as $fileId) {
                        $file = File::find($fileId);
                        $fileDtos[] = new BucketFileDto([
                            'fileId' => $file->id,
                            'bucketFilePath' => $file->bucket_file_path,
                            'bucketName' => $file->bucket_name,
                            'bucketType' => $file->bucket_type,
                        ]);
                    }
                }
            } else {
                if (request()->input('file_paths')) {
                    foreach (explode(',', request()->input('file_paths')) as $filePath) {
                        $fileDtos[] = FileDto::createFileDto($filePath, File::TYPE_PUBLIC_BUCKET);
                    }
                }
            }

            $contentTypeDto = new ContentTypeDto([
                'id' => request()->input('id') ? request()->input('id') : 0,
                'name' => request()->input('name'),
                'description' => request()->input('description'),
                'fileType' => $fileType,
                'fileDtos' => $fileDtos,
            ]);

            $this->contentTypeService->updateOrCreateContentType($contentTypeDto);
        } catch (Error|Exception $e) {
            Log::error('input error', $e->getTrace());
            throw new InvalidArgumentException('Invalid Inputs');
        }

        return $this->form()
            ->response()
            ->redirect('contentType/')
            ->success(trans('admin.save_succeeded'));
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        return Form::make(ContentType::query(),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');
                $form->text('name')->required();
                $form->textarea('description')->required();
                $form->radio('file_type', admin_trans_field('image_upload_or_cloud'))
                    ->help(admin_trans_label('upload_cover_cloud'))
                    ->required()
                    ->options([
                        'upload' => admin_trans_option('upload', 'file_type'),
                        'cloud' => admin_trans_option('cloud', 'file_type'),
                    ])
                    ->default('upload')
                    ->when('upload', function (Form $form) {
                        $form->multipleFile('file_paths', admin_trans_field('image_or_video'))
                            ->url('ajax/upload')
                            ->autoUpload()
                            ->removable();
                    })
                    ->when('cloud', function (Form $form) {
                        $form->multipleSelectTable('file_ids', admin_trans_field('image_or_video'))
                            ->title(admin_trans_label('select_image_or_video_files'))
                            ->from(PublicFileTable::make())
                            ->model(File::class, 'id', 'name');
                    });
            });
    }

    public function title(): string
    {
        return admin_trans_label('contentType');
    }

    public function update($id): JsonResponse
    {
        return $this->save();
    }

    public function routeName(): string
    {
        return 'contentType';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(ContentType::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();

                $grid->column('id')->sortable();
                $grid->column('name');
                $grid->column('description');
                $grid->column('updated_at_formatted');
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
        return Show::make($id, ContentType::query(), function (Show $show) {
            $show->disableEditButton();
            $show->disableDeleteButton();

            $show->field('id');
            $show->field('name');
            $show->field('description');
            $show->field('updated_at_formatted');
            $show->field('created_at_formatted');
        });
    }
}

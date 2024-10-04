<?php

namespace App\Admin\Controllers;

use App\Dtos\FileDto;
use App\Dtos\UploadFileDto;
use App\Models\File;
use App\Services\FileService;
use Dcat\Admin\Exception\InvalidArgumentException;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Http\JsonResponse;
use Dcat\Admin\Show;
use Illuminate\Support\Facades\Log;

class FileController extends AdminController
{
    private FileService $fileService;

    public function __construct(FileService $fileService) {
        $this->fileService = $fileService;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(File::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();
                $grid->disableDeleteButton();
                $grid->disableEditButton();

                $grid->column('id')->sortable();
                $grid->column('name')->width('20%');
                $grid->column('bucket_type', 'Type');
                $grid->column('upload_path', 'Upload Path');
                $grid->column('bucket_name', 'Bucket Name');
                $grid->column('bucket_file_path', 'Bucket Path')->width('20%');
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
        return Show::make($id, File::query(), function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $url = $show->model()->url;
                $show->field('id');
                $show->field('name', 'Name');
                $show->field('Cloud URL')->value($url)->link();
                $show->field('upload_path', 'Upload Path');
                $show->field('bucket_name', 'Bucket Name');
                $show->field('bucket_file_name', 'Bucket File Name');
                $show->field('bucket_file_path', 'Bucket File Path');
                $show->field('bucket_url', 'Bucket URL');
                $show->field('updated_at', 'Updated');
                $show->field('created_at', 'Created');
            });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        return Form::make(File::query(),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');
                $form->radio('bucket_type', 'Type')
                    ->options([
                        File::TYPE_PUBLIC_BUCKET => 'Public',
                        File::TYPE_PRIVATE_BUCKET => 'Private',
                    ])
                    ->required();
                $form->file('upload_path', 'File')
                    ->url('ajax/upload')
                    ->maxSize(512000)
                    ->autoUpload()
                    ->removable()
                    ->required();
            });
    }

    private function save(): JsonResponse {
        try {
            $fileDto = new UploadFileDto([
                'fileId' => request()->input('id') ? request()->input('id') : 0,
                'uploadPath' => request()->input('upload_path'),
                'bucketType' => request()->input('bucket_type')
            ]);
            $this->fileService->createFile($fileDto);
        } catch (\Error | \Exception $e) {
            throw new InvalidArgumentException('Invalid Inputs');
        }

        return $this->form()
            ->response()
            ->redirect('file/')
            ->success(trans('admin.save_succeeded'));
    }

    public function store(): JsonResponse
    {
        return $this->save();
    }

    public function update($id): JsonResponse
    {
        return $this->save();
    }

    public function title(): string
    {
        return 'Files';
    }

    public function routeName(): string
    {
        return 'file';
    }
}

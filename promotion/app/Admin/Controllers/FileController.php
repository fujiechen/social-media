<?php

namespace App\Admin\Controllers;

use App\Dtos\UploadFileDto;
use App\Models\File;
use App\Services\FileService;
use Dcat\Admin\Exception\InvalidArgumentException;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Http\JsonResponse;
use Dcat\Admin\Show;
use Error;
use Exception;

class FileController extends AdminController
{
    private FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function store(): JsonResponse
    {
        return $this->save();
    }

    private function save(): JsonResponse
    {
        try {
            $fileDto = new UploadFileDto([
                'fileId' => request()->input('id') ? request()->input('id') : 0,
                'uploadPath' => request()->input('upload_path'),
                'bucketType' => request()->input('bucket_type')
            ]);
            $this->fileService->createFile($fileDto);
        } catch (Error|Exception $e) {
            throw new InvalidArgumentException('Invalid Inputs');
        }

        return $this->form()
            ->response()
            ->redirect('file/')
            ->success(trans('admin.save_succeeded'));
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
                $form->radio('bucket_type')
                    ->options([
                        File::TYPE_PUBLIC_BUCKET => admin_trans_option('public', 'bucket_type'),
                        File::TYPE_PRIVATE_BUCKET => admin_trans_option('private', 'bucket_type'),
                    ])
                    ->required();
                $form->file('upload_path', admin_trans_field('file'))
                    ->url('ajax/upload')
                    ->maxSize(512000)
                    ->autoUpload()
                    ->removable()
                    ->required();
            });
    }

    public function update($id): JsonResponse
    {
        return $this->save();
    }

    public function title(): string
    {
        return admin_trans_label('file');
    }

    public function routeName(): string
    {
        return 'file';
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

                $grid->quickSearch(['id', 'name', 'bucket_type', 'bucket_name', 'bucket_file_path']);

                $grid->selector(function (Grid\Tools\Selector $selector) {
                    $selector->select('bucket_type',
                        admin_trans_field('bucket_type') . ':', [
                            'private' => admin_trans_option('private', 'bucket_type'),
                            'public' => admin_trans_option('public', 'bucket_type'),
                        ]);
                });

                $grid->column('id')->sortable();
                $grid->column('name')->width('20%');
                $grid->column('bucket_type');
                $grid->column('upload_path');
                $grid->column('bucket_name');
                $grid->column('bucket_file_path', admin_trans_field('bucket_path'))->width('20%');
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
            $show->field('name');
            $show->field('Cloud URL', admin_trans_field('cloud_url'))->value($url)->link();
            $show->field('upload_path');
            $show->field('bucket_name');
            $show->field('bucket_file_name', admin_trans_field('bucket_file_name'));
            $show->field('bucket_file_path', admin_trans_field('bucket_path'));
            $show->field('bucket_url');
            $show->field('updated_at');
            $show->field('created_at');


        });
    }
}

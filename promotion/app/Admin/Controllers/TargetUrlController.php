<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\PublicFileTable;
use App\Dtos\BucketFileDto;
use App\Dtos\FileDto;
use App\Dtos\TargetUrlDto;
use App\Models\File;
use App\Models\TargetUrl;
use App\Services\TargetUrlService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Http\JsonResponse;
use Dcat\Admin\Show;
use Error;
use Exception;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class TargetUrlController extends AdminController
{

    private TargetUrlService $targetUrlService;

    public function __construct(TargetUrlService $targetUrlService)
    {
        $this->targetUrlService = $targetUrlService;
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

            $fileDto = null;

            $fileType = request()->input('file_type');
            if ($fileType === 'cloud') {
                if (request()->input('qr_file_id')) {
                    $fileId = request()->input('qr_file_id');
                    $file = File::find($fileId);
                    $fileDto = new BucketFileDto([
                        'fileId' => $file->id,
                        'bucketFilePath' => $file->bucket_file_path,
                        'bucketName' => $file->bucket_name,
                        'bucketType' => $file->bucket_type,
                    ]);
                }
            } else {
                if (request()->input('file_path')) {
                    $fileDto = FileDto::createFileDto(request()->input('file_path'), File::TYPE_PUBLIC_BUCKET);
                }
            }

            $landingTemplateDto = new TargetUrlDto([
                'id' => request()->input('id') ? request()->input('id') : 0,
                'name' => request()->input('name'),
                'url' => request()->input('url'),
                'status' => request()->input('status'),
                'fileType' => $fileType,
                'qrFileDto' => $fileDto,
            ]);

            $this->targetUrlService->updateOrCreate($landingTemplateDto);
        } catch (Error|Exception $e) {
            Log::error('input error: ' . $e->getMessage());
            throw new InvalidArgumentException('Invalid Inputs');
        }

        return $this->form()
            ->response()
            ->redirect('targetUrl/')
            ->success(trans('admin.save_succeeded'));
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        return Form::make(TargetUrl::query(),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');
                $form->text('name')->required();
                $form->url('url')->required();
                $form->select('status')
                    ->options([
                        TargetUrl::STATUS_ACTIVE => admin_trans_option('active', 'target_url_status'),
                        TargetUrl::STATUS_INACTIVE => admin_trans_option('inactive', 'target_url_status'),
                    ])
                    ->default(TargetUrl::STATUS_ACTIVE)
                    ->required();
                $form->radio('file_type', admin_trans_field('image_upload_or_cloud'))
                    ->help(admin_trans_label('upload_cover_cloud'))
                    ->required()
                    ->options([
                        'upload' => admin_trans_option('upload', 'file_type'),
                        'cloud' => admin_trans_option('cloud', 'file_type'),
                    ])
                    ->default('upload')
                    ->when('upload', function (Form $form) {
                        $form->file('file_path', admin_trans_field('qr_code_image'))
                            ->url('ajax/upload')
                            ->autoUpload()
                            ->removable();
                    })
                    ->when('cloud', function (Form $form) {
                        $form->selectTable('qr_file_id', admin_trans_field('qr_code_image'))
                            ->title(admin_trans_label('select_image'))
                            ->from(PublicFileTable::make())
                            ->model(File::class, 'id', 'name');
                    });
            });
    }

    public function title(): string
    {
        return admin_trans_label('targetUrl');
    }

    public function update($id): JsonResponse
    {
        return $this->save();
    }

    public function routeName(): string
    {
        return 'targetUrl';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(TargetUrl::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();
                $grid->disableDeleteButton();

                $grid->column('id')->sortable();
                $grid->column('name');
                $grid->column('url')->link();
                $grid->column('status')->display(function ($status) {
                    return admin_trans_option($status, 'target_url_status');
                })->label();
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
        return Show::make($id, TargetUrl::query(), function (Show $show) {
            $show->disableEditButton();
            $show->disableDeleteButton();

            $show->field('id');
            $show->field('name');
            $show->field('url');
            $show->field('status')->as(function ($status) {
                return admin_trans_option($status, 'target_url_status');
            });
            $show->field('updated_at_formatted');
            $show->field('created_at_formatted');
        });
    }
}

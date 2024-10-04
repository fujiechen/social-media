<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\LandingDomainTable;
use App\Admin\Components\Tables\PublicFileTable;
use App\Admin\Components\Tables\RedirectTypeTable;
use App\Admin\Components\Tables\TargetUrlTable;
use App\Dtos\BucketFileDto;
use App\Dtos\FileDto;
use App\Dtos\LandingTemplateDto;
use App\Models\File;
use App\Models\LandingDomain;
use App\Models\LandingTemplate;
use App\Models\RedirectType;
use App\Models\TargetUrl;
use App\Services\LandingTemplateService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Http\JsonResponse;
use Dcat\Admin\Show;
use Error;
use Exception;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class LandingTemplateController extends AdminController
{

    private LandingTemplateService $landingTemplateService;

    public function __construct(LandingTemplateService $landingTemplateService)
    {
        $this->landingTemplateService = $landingTemplateService;
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
                if (request()->input('banner_file_id')) {
                    $fileId = request()->input('banner_file_id');
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

            $landingTemplateDto = new LandingTemplateDto([
                'id' => request()->input('id') ? request()->input('id') : 0,
                'name' => request()->input('name'),
                'description' => request()->input('description'),
                'landingHtml' => request()->input('landing_html'),
                'redirectTypeId' => request()->input('redirect_type_id'),
                'targetUrlId' => request()->input('target_url_id'),
                'landingDomainId' => request()->input('landing_domain_id'),
                'status' => request()->input('status'),
                'fileType' => $fileType,
                'bannerFileDto' => $fileDto,
            ]);

            $this->landingTemplateService->updateOrCreate($landingTemplateDto);
        } catch (Error|Exception $e) {
            Log::error('input error: ' . $e->getMessage());
            throw new InvalidArgumentException('Invalid Inputs');
        }

        return $this->form()
            ->response()
            ->redirect('landingTemplate/')
            ->success(trans('admin.save_succeeded'));
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        return Form::make(LandingTemplate::query(),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');
                $form->text('name')->required();
                $form->textarea('description')->required();
                $form->selectTable('redirect_type_id', admin_trans_field('redirect_type'))
                    ->title(admin_trans_label('select_redirect_type'))
                    ->from(RedirectTypeTable::make())
                    ->model(RedirectType::class, 'id', 'name')
                    ->required();
                $form->selectTable('target_url_id', admin_trans_field('target_url'))
                    ->title(admin_trans_label('select_target_url'))
                    ->from(TargetUrlTable::make())
                    ->model(TargetUrl::class, 'id', 'url')
                    ->required();

                $form->select('status')
                    ->options([
                        LandingTemplate::STATUS_DRAFT => admin_trans_option('draft', 'landing_template_status'),
                        LandingTemplate::STATUS_ACTIVE => admin_trans_option('active', 'landing_template_status'),
                        LandingTemplate::STATUS_INACTIVE => admin_trans_option('inactive', 'landing_template_status'),
                    ])
                    ->default(LandingTemplate::STATUS_DRAFT)
                    ->required();

                $form->html('
                <div class="alert alert-primary">当状态为Active并且landing_url为空时，系统会自动触发landing page的发布</div>
                ');

                if ($form->model()->landing_url) {
                    $form->url('landing_url');
                    $form->html('<a target=_blank href="' . $form->model()->landing_url . '">' . admin_trans_field('landing_url') . '</a>');
                }

                $form->selectTable('landing_domain_id', admin_trans_field('landing_domain'))
                    ->title(admin_trans_label('select_landing_domain'))
                    ->from(LandingDomainTable::make())
                    ->model(LandingDomain::class, 'id', 'name')
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
                        $form->file('file_path', admin_trans_field('image_or_video'))
                            ->url('ajax/upload')
                            ->autoUpload()
                            ->removable();
                    })
                    ->when('cloud', function (Form $form) {
                        $form->selectTable('banner_file_id', admin_trans_field('image_or_video'))
                            ->title(admin_trans_label('select_image_or_video_files'))
                            ->from(PublicFileTable::make())
                            ->model(File::class, 'id', 'name');
                    });

                $form->textarea('landing_html')
                    ->help(admin_trans_label('replace_html_string'))
                    ->required();
            });
    }

    public function title(): string
    {
        return admin_trans_label('landingTemplate');
    }

    public function update($id): JsonResponse
    {
        return $this->save();
    }

    public function routeName(): string
    {
        return 'landingTemplate';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(LandingTemplate::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();
                $grid->disablePagination();

                $grid->column('id')->sortable();
                $grid->column('name');
                $grid->column('status')->display(function ($status) {
                    return admin_trans_option($status, 'landing_template_status');
                })->label();
                $grid->column('landing_url')->link();
                $grid->column('landingDomain.name', admin_trans_field('landing_domain'));
                $grid->column('targetUrl.url', admin_trans_field('target_url'))->link();
                $grid->column('updated_at_formatted');
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
        return Show::make($id, LandingTemplate::query(), function (Show $show) {
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

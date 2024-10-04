<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\AccountTypeTable;
use App\Admin\Components\Tables\ContactTable;
use App\Admin\Components\Tables\LandingTemplateTable;
use App\Admin\Components\Tables\PublicFileTable;
use App\Dtos\AccountDto;
use App\Dtos\BucketFileDto;
use App\Dtos\UploadFileDto;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Contact;
use App\Models\File;
use App\Models\LandingTemplate;
use App\Services\AccountService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Http\JsonResponse;
use Dcat\Admin\Show;
use Error;
use Exception;
use InvalidArgumentException;

class AccountController extends AdminController
{

    private AccountService $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    public function store(): JsonResponse
    {
        return $this->save();
    }

    private function save(): JsonResponse
    {
        try {
            if (empty(request()->input('file_type'))) {
                return new JsonResponse();
            }

            $profileAvatarFileDto = null;
            if (request()->input('file_type') === 'upload') {
                $profileAvatarFileDto = new UploadFileDto([
                    'uploadPath' => request()->input('profile_avatar_file_path'),
                    'bucketType' => File::TYPE_PUBLIC_BUCKET,
                ]);
            } else if (request()->input('file_type') === 'cloud') {
                $file = File::find(request()->input('profileAvatarFile.id'));
                $profileAvatarFileDto = new BucketFileDto([
                    'fileId' => $file->id,
                    'bucketFilePath' => $file->bucket_file_path,
                    'bucketName' => $file->bucket_name,
                    'bucketType' => $file->bucket_type,
                ]);
            }


            $accountDto = new AccountDto([
                'id' => request()->input('id') ? request()->input('id') : 0,
                'instruction' => request()->input('instruction'),
                'contactId' => request()->input('contact_id'),
                'accountTypeId' => request()->input('account_type_id'),
                'nickname' => request()->input('nickname'),
                'accountNo' => request()->input('account_no'),
                'accountUrl' => request()->input('account_url'),
                'adminUsername' => request()->input('admin_username'),
                'adminPassword' => request()->input('admin_password'),
                'profileDescription' => request()->input('profile_description'),
                'landingTemplateId' => request()->input('landing_template_id'),
                'status' => request()->input('status'),
                'fileType' => request()->input('file_type'),
                'profileAvatarFile' => $profileAvatarFileDto,
            ]);

            $this->accountService->updateOrCreateAccount($accountDto);
        } catch (Error|Exception $e) {
            throw new InvalidArgumentException($e->getTraceAsString());
        }

        return $this->form()
            ->response()
            ->redirect('account/')
            ->success(trans('admin.save_succeeded'));
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        return Form::make(Account::query(),
            function (Form $form) {
                if ($form->isEditing()) {
                    $form->html('<div class="alert alert-primary">' . $form->model()->instruction . '</div>');
                    $form->hidden('instruction');
                    $form->hidden('id');
                } else {
                    $form->textarea('instruction');
                }

                $form->display('id');
                $form->selectTable('contact_id', admin_trans_field('contact'))
                    ->title(admin_trans_label('select_contact'))
                    ->from(ContactTable::make())
                    ->model(Contact::class, 'id', 'contact')
                    ->required();
                $form->selectTable('account_type_id', admin_trans_field('account_type'))
                    ->title(admin_trans_label('select_account_type'))
                    ->from(AccountTypeTable::make())
                    ->model(AccountType::class, 'id', 'name')
                    ->required();
                $form->text('nickname')->required();
                $form->text('account_no')->required();
                $form->url('account_url')->required();
                $form->text('admin_username')->required();
                $form->text('admin_password')->required();

                $form->radio('file_type', admin_trans_field('image_upload_or_cloud'))
                    ->help(admin_trans_label('upload_cover_cloud'))
                    ->required()
                    ->options([
                        'upload' => admin_trans_option('upload', 'file_type'),
                        'cloud' => admin_trans_option('cloud', 'file_type'),
                    ])
                    ->default('upload')
                    ->when('upload', function (Form $form) {
                        $form->image('profile_avatar_file_path', admin_trans_field('profile_avatar'))
                            ->url('ajax/upload')
                            ->autoUpload()
                            ->removable();
                    })
                    ->when('cloud', function (Form $form) {
                        $form->selectTable('profileAvatarFile.id', admin_trans_field('profile_avatar'))
                            ->title(admin_trans_label('select_image'))
                            ->from(PublicFileTable::make())
                            ->model(File::class, 'id', 'name');
                    });
                $form->textarea('profile_description')->required();
                $form->selectTable('landing_template_id', admin_trans_field('landing_template'))
                    ->title(admin_trans_label('select_landing_template'))
                    ->from(LandingTemplateTable::make())
                    ->model(LandingTemplate::class, 'id', 'name')
                    ->required();
                $form->select('status', 'Status')
                    ->options([
                        Account::STATUS_DRAFT => admin_trans_option('draft', 'account_status'),
                        Account::STATUS_ACTIVE => admin_trans_option('active', 'account_status'),
                        Account::STATUS_INACTIVE => admin_trans_option('inactive', 'account_status'),
                    ])
                    ->default(Account::STATUS_DRAFT)
                    ->required();
            });
    }

    public function title(): string
    {
        return admin_trans_label('account');
    }

    public function update($id): JsonResponse
    {
        return $this->save();
    }

    public function routeName(): string
    {
        return 'account';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(Account::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();

                $grid->selector(function (Grid\Tools\Selector $selector) {
                    $selector->select('status',
                        admin_trans_field('status') . ':', [
                            Account::STATUS_DRAFT => admin_trans_option('draft', 'account_status'),
                            Account::STATUS_ACTIVE => admin_trans_option('active', 'account_status'),
                            Account::STATUS_INACTIVE => admin_trans_option('inactive', 'account_status'),
                        ]);
                });

                $grid->column('id')->sortable();
                $grid->column('contact.contact', admin_trans_field('contact'));
                $grid->column('accountType.name', admin_trans_field('account_type'));
                $grid->column('nickname');
                $grid->column('status')->display(function ($status) {
                    return admin_trans_option($status, 'account_status');
                })->label();
                $grid->column('account_url')->link();
                $grid->column('admin_username');
                $grid->column('admin_password');
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
        return Show::make($id, Account::query(), function (Show $show) {
            $show->disableEditButton();
            $show->disableDeleteButton();

            $show->field('id');
            $show->field('name');
            $show->field('updated_at_formatted');
            $show->field('created_at_formatted');
        });
    }
}

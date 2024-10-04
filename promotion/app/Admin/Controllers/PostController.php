<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\AccountTable;
use App\Admin\Components\Tables\ContentTypeTable;
use App\Admin\Components\Tables\LandingTemplateTable;
use App\Models\Account;
use App\Models\ContentType;
use App\Models\LandingTemplate;
use App\Models\Post;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class PostController extends AdminController
{

    public function routeName(): string
    {
        return 'post';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(Post::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();

                $grid->selector(function (Grid\Tools\Selector $selector) {
                    $selector->select('status',
                        admin_trans_field('status') . ':', [
                            Post::STATUS_DRAFT => admin_trans_option('draft', 'post_status'),
                            Post::STATUS_ACTIVE => admin_trans_option('active', 'post_status'),
                            Post::STATUS_INACTIVE => admin_trans_option('inactive', 'post_status'),
                        ]);
                });

                $grid->column('id')->sortable();
                $grid->column('account.accountType.name', admin_trans_field('account_type'));
                $grid->column('account.contact.contact', admin_trans_field('contact'));
                $grid->column('status')->display(function ($status) {
                    return admin_trans_option($status, 'post_status');
                })->label();
                $grid->column('nickname');
                $grid->column('contentType.name', admin_trans_field('content_type'));
                $grid->column('landingTemplate.name', admin_trans_field('landing_template'));
                $grid->column('post_url', admin_trans_field('post_url'))->link();
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
        return Show::make($id, Post::query(), function (Show $show) {
            $show->disableEditButton();
            $show->disableDeleteButton();

            $show->field('id');
            $show->field('status')->as(function ($status) {
                return admin_trans_option($status, 'post_status');
            });
            $show->field('updated_at_formatted', 'Updated');
            $show->field('created_at_formatted', 'Created');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        return Form::make(Post::query(),
            function (Form $form) {
                if ($form->isEditing()) {
                    $form->html('<div class="alert alert-primary">' . $form->model()->instruction . '</div>');
                    $form->hidden('instruction');
                    $form->hidden('id');
                } else {
                    $form->textarea('instruction');
                }

                $form->display('id');
                $form->selectTable('account_id', admin_trans_field('account'))
                    ->title(admin_trans_label('select_account'))
                    ->from(AccountTable::make())
                    ->model(Account::class, 'id', 'nickname')
                    ->required();
                $form->selectTable('content_type_id', admin_trans_field('content_type'))
                    ->title(admin_trans_label('select_content_type'))
                    ->from(ContentTypeTable::make())
                    ->model(ContentType::class, 'id', 'name')
                    ->required();
                $form->text('title')->required();
                $form->textarea('description')->required();
                $form->url('post_url', admin_trans_field('url'))->required();
                $form->text('tags');
                $form->selectTable('landing_template_id', admin_trans_field('landing_template'))
                    ->title(admin_trans_label('select_landing_template'))
                    ->from(LandingTemplateTable::make())
                    ->model(LandingTemplate::class, 'id', 'name')
                    ->required();

                $form->select('status')
                    ->options([
                        Post::STATUS_DRAFT => admin_trans_option('draft', 'post_status'),
                        Post::STATUS_ACTIVE => admin_trans_option('active', 'post_status'),
                        Post::STATUS_INACTIVE => admin_trans_option('inactive', 'post_status'),
                    ])
                    ->default(Post::STATUS_DRAFT)
                    ->required();
            });
    }

    public function title(): string
    {
        return admin_trans_label('post');
    }
}

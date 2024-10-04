<?php

namespace App\Admin\Controllers;

use App\Models\AccountType;
use App\Models\Contact;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class AccountTypeController extends AdminController
{

    public function title(): string
    {
        return admin_trans_label('accountType');
    }

    public function routeName(): string
    {
        return 'accountType';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(AccountType::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();
                $grid->disableDeleteButton();

                $grid->column('id')->sortable();
                $grid->column('name');
                $grid->column('contact_type');
                $grid->column('admin_url')->link();
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
        return Show::make($id, AccountType::query(), function (Show $show) {
            $show->disableEditButton();
            $show->disableDeleteButton();

            $show->field('id');
            $show->field('name');
            $show->field('description');
            $show->field('contact_type');
            $show->field('admin_url');
            $show->field('updated_at_formatted');
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
        return Form::make(AccountType::query(),
            function (Form $form) {
                $form->disableDeleteButton();

                $form->display('id');
                $form->text('name')->required();
                $form->textarea('description')->required();
                $form->select('contact_type')
                    ->options([
                        Contact::TYPE_PHONE => admin_trans_option('phone', 'contact_type'),
                        Contact::TYPE_EMAIL => admin_trans_option('email', 'contact_type'),
                    ])
                    ->default(Contact::TYPE_PHONE)
                    ->required();
                $form->url('admin_url')->required();
            });
    }
}

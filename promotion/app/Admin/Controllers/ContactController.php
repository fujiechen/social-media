<?php

namespace App\Admin\Controllers;

use App\Models\Contact;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class ContactController extends AdminController
{

    public function title(): string
    {
        return admin_trans_label('contact');
    }

    public function routeName(): string
    {
        return 'contact';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(Contact::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();
                $grid->disableDeleteButton();

                $grid->column('id')->sortable();
                $grid->column('contact');
                $grid->column('type');
                $grid->column('admin_url')->link();
                $grid->column('admin_username');
                $grid->column('admin_password');
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
        return Show::make($id, Contact::query(), function (Show $show) {
            $show->disableEditButton();
            $show->disableDeleteButton();

            $show->field('id');
            $show->field('created_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        return Form::make(Contact::query(),
            function (Form $form) {
                $form->disableDeleteButton();
                $form->display('id');
                $form->select('type')
                    ->options([
                        Contact::TYPE_EMAIL => admin_trans_option('email', 'contact_type'),
                        Contact::TYPE_PHONE => admin_trans_option('phone', 'contact_type'),
                    ])
                    ->required();
                $form->text('contact')->required();
                $form->textarea('description')->required();
                $form->url('admin_url')->required();
                $form->text('admin_username')->required();
                $form->text('admin_password')->required();
            });
    }
}

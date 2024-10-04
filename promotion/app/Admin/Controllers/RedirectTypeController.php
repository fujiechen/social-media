<?php

namespace App\Admin\Controllers;

use App\Models\RedirectType;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class RedirectTypeController extends AdminController
{

    public function title(): string
    {
        return admin_trans_label('redirectType');
    }

    public function routeName(): string
    {
        return 'redirectType';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(RedirectType::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();
                $grid->disableDeleteButton();

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
        return Show::make($id, RedirectType::query(), function (Show $show) {
            $show->disableEditButton();
            $show->disableDeleteButton();

            $show->field('id');
            $show->field('name');
            $show->field('description');
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
        return Form::make(RedirectType::query(),
            function (Form $form) {
                $form->disableDeleteButton();
                $form->display('id');
                $form->text('name')->required();
                $form->textarea('description')->required();
            });
    }
}

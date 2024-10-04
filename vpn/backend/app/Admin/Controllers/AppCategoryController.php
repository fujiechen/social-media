<?php

namespace App\Admin\Controllers;

use App\Models\AppCategory;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class AppCategoryController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(AppCategory::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();

                $grid->column('id')->sortable();
                $grid->column('name', 'Name');
                $grid->column('updated_at_formatted', 'Updated');
                $grid->column('created_at_formatted', 'Created');
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
        return Show::make($id, AppCategory::query(),
            function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('name');
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
        return Form::make(AppCategory::query(),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');
                $form->text('name', 'Name');
            });
    }

    public function title(): string
    {
        return 'App Category';
    }

    public function routeName(): string
    {
        return 'appCategory';
    }
}

<?php

namespace App\Admin\Controllers;

use App\Models\Resource;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class ResourceController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(Resource::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();
                $grid->disablePagination();

                $grid->column('id')->sortable();
                $grid->column('name');
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
        return Show::make($id, Resource::query(), function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('name');
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
        return Form::make(Resource::query(),
            function (Form $form) {
                $form->display('id');
                $form->text('name')->required();
            });
    }

    public function title(): string
    {
        return admin_trans_label('resource');
    }

    public function routeName(): string
    {
        return 'resource';
    }
}

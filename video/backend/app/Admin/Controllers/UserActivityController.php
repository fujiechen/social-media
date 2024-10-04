<?php

namespace App\Admin\Controllers;

use App\Models\ActivityLog;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class UserActivityController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(ActivityLog::query()->with(['user']),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();
                $grid->disableEditButton();
                $grid->disableCreateButton();
                $grid->disableDeleteButton();

                $grid->column('id')->sortable();
                $grid->column('user.username', admin_trans_field('user'));
                $grid->column('description');
                $grid->column('event');
                $grid->column('created_at');
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
        return Show::make($id, ActivityLog::query(),
            function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('user.username', admin_trans_field('user'));
                $show->field('description');
                $show->field('event');
                $show->field('created_at');
                $show->field('properties')->json();

            });
    }


    public function title(): string
    {
        return admin_trans_label('userActivity');
    }

    public function routeName(): string
    {
        return 'userActivity';
    }
}

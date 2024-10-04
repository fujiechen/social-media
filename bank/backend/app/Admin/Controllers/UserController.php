<?php

namespace App\Admin\Controllers;

use App\Models\User;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class UserController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(User::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();
                $grid->disableDeleteButton();
                $grid->disableEditButton();
                $grid->disableCreateButton();

                $grid->quickSearch(['username', 'nickname']);

                $grid->column('id')->sortable();
                $grid->column('username');
                $grid->column('nickname');
                $grid->column('role_names')->label();
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
        return Show::make($id, User::query(),
            function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('username');
                $show->field('nickname');
                $show->field('role_names')->label();
                $show->field('created_at');
            });
    }

    public function title(): string
    {
        return admin_trans_label('user');
    }

    public function routeName(): string
    {
        return 'user';
    }
}

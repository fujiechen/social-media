<?php

namespace App\Admin\Controllers;

use App\Models\UserShare;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class UserShareController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(UserShare::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();
                $grid->disableEditButton();
                $grid->disableCreateButton();
                $grid->disableDeleteButton();

                $grid->column('id')->sortable();
                $grid->column('user.username', admin_trans_field('user'));
                $grid->column('shareable_type')->display(function ($shareable_type) {
                    return admin_trans_option($shareable_type, 'shareable_types');
                });
                $grid->column('shareable_id');
                $grid->column('url');
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
        return Show::make($id, UserShare::query()->with(['user']),
            function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('user.username', admin_trans_field('user'));
                $show->field('shareable_type')->as(function ($shareable_type) {
                    return admin_trans_option($shareable_type, 'shareable_types');
                });
                $show->field('shareable_id');
                $show->field('url');
                $show->field('created_at');
            });
    }


    public function title(): string
    {
        return admin_trans_label('userShare');
    }

    public function routeName(): string
    {
        return 'userShare';
    }
}

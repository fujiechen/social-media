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
                $grid->column('user.username', 'User');
                $grid->column('shareable_type', 'Shared Type');
                $grid->column('shareable_id', 'Shared Id');
                $grid->column('url', 'URL');
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
        return Show::make($id, UserShare::query()->with(['user']),
            function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('user.username', 'User');
                $show->field('shareable_type', 'Shared Type');
                $show->field('shareable_id', 'Shared Id');
                $show->field('url', 'Url');
                $show->field('created_at', 'Created');
            });
    }


    public function title(): string
    {
        return 'User Share';
    }

    public function routeName(): string
    {
        return 'userShare';
    }
}

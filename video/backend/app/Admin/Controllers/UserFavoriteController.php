<?php

namespace App\Admin\Controllers;

use App\Models\MediaFavorite;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class UserFavoriteController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(MediaFavorite::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();
                $grid->disableEditButton();
                $grid->disableCreateButton();
                $grid->disableDeleteButton();

                $grid->id('id')->bold();
                $grid->column('user.username', admin_trans_field('user'));
                $grid->column('media.name', admin_trans_field('media'));
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
        return Show::make($id, MediaFavorite::query(),
            function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('user.username', admin_trans_field('user'));
                $show->field('media.name', admin_trans_field('media'));
                $show->field('created_at');
            });
    }


    public function title(): string
    {
        return admin_trans_label('userFavorite');
    }

    public function routeName(): string
    {
        return 'userFavorite';
    }
}

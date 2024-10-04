<?php

namespace App\Admin\Controllers;

use App\Models\MediaFavorite;
use App\Models\MediaLike;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class UserLikeController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(MediaLike::query(),
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
                $grid->column('type', admin_trans_field('type'))->display(function ($type) {
                    return admin_trans_option($type, 'user_like_types');
                });
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
        return Show::make($id, MediaLike::query(),
            function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('user.username', admin_trans_field('user'));
                $show->field('media.name', admin_trans_field('media'));
                $show->field('type', admin_trans_field('type'))->as(function ($type) {
                    return admin_trans_option($type, 'user_like_types');
                });
                $show->field('created_at');
            });
    }


    public function title(): string
    {
        return admin_trans_label('userLike');
    }

    public function routeName(): string
    {
        return 'userLike';
    }
}

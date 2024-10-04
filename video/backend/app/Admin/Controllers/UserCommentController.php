<?php

namespace App\Admin\Controllers;

use App\Models\MediaComment;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class UserCommentController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(MediaComment::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();
                $grid->disableEditButton();
                $grid->disableCreateButton();
                $grid->disableDeleteButton();

                $grid->column('id')->sortable();
                $grid->column('user.username', admin_trans_field('user'));
                $grid->column('media.name', admin_trans_field('media'));
                $grid->column('comment');
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
        return Show::make($id, MediaComment::query()->with(['user']),
            function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('user.username', admin_trans_field('user'));
                $show->field('media.name', admin_trans_field('media'));
                $show->field('comment');
                $show->field('created_at');
            });
    }


    public function title(): string
    {
        return admin_trans_label('userComment');
    }

    public function routeName(): string
    {
        return 'userComment';
    }
}

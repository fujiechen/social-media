<?php

namespace App\Admin\Controllers;

use App\Models\UserFollowing;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class UserSubscriptionController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(UserFollowing::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();
                $grid->disableEditButton();
                $grid->disableCreateButton();
                $grid->disableDeleteButton();

                $grid->id('id')->bold();
                $grid->column('publisher_user.username', admin_trans_field('publisher'));
                $grid->column('following_user.username', admin_trans_field('follower'));
                $grid->column('valid_until_at_formatted', admin_trans_field('valid_until'));
                $grid->column('valid_until_at_days', admin_trans_field('valid_until_at_days'));
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
        return Show::make($id, UserFollowing::query(),
            function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('publisher_user.username', admin_trans_field('publisher'));
                $show->field('following_user.username', admin_trans_field('follower'));
                $show->field('valid_until_at_formatted', admin_trans_field('valid_until'));
                $show->field('valid_until_at_days', admin_trans_field('valid_until_at_days'));
                $show->field('created_at',);
            });
    }


    public function title(): string
    {
        return admin_trans_label('userSubscription');
    }

    public function routeName(): string
    {
        return 'userSubscription';
    }
}

<?php

namespace App\Admin\Controllers;

use App\Models\UserReferral;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class UserReferralController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(UserReferral::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();
                $grid->disableEditButton();
                $grid->disableCreateButton();
                $grid->disableDeleteButton();

                $grid->id('id')->bold();
                $grid->column('user.username', admin_trans_field('username'));
                $grid->column('subUser.role_names', admin_trans_field('roles'))->label();
                $grid->column('level', admin_trans_field('level'));
                $grid->column('subUser.username', admin_trans_field('sub_user'));
                $grid->column('userShare.url', admin_trans_field('user_share_url'));
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
        return Show::make($id, UserReferral::query(),
            function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('user.username', admin_trans_field('user'));
                $show->field('sub_user.username', admin_trans_field('sub_user'));
                $show->field('user_share.shareable_type', admin_trans_field('shareable_type'))->as(function ($shareable_type) {
                    return admin_trans_option($shareable_type, 'shareable_types');
                });
                $show->field('user_share.shareable_id', admin_trans_field('shareable_id'));
                $show->field('user_share.url', admin_trans_field('user_share_url'));
                $show->field('created_at');
            });
    }


    public function title(): string
    {
        return admin_trans_label('userReferral');
    }

    public function routeName(): string
    {
        return 'userReferral';
    }
}

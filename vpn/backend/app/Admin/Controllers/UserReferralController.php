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
                $grid->column('user.username', 'User');
                $grid->column('subUser.role_names', 'Roles')->label();
                $grid->column('level', 'Level');
                $grid->column('subUser.username', 'Child');
                $grid->column('userShare.url', 'From Url');
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
                $show->field('user.username', 'User');
                $show->field('sub_user.username', 'Child');
                $show->field('user_share.shareable_type', 'Shared Type');
                $show->field('user_share.shareable_id', 'Shared Id');
                $show->field('user_share.url', 'Url');
                $show->field('created_at', 'Created');
            });
    }


    public function title(): string
    {
        return 'User Referrals';
    }

    public function routeName(): string
    {
        return 'userReferral';
    }
}

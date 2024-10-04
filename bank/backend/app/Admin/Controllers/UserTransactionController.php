<?php

namespace App\Admin\Controllers;

use App\Models\UserTransaction;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;

class UserTransactionController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(UserTransaction::query()->orderBy('id', 'desc'),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();
                $grid->disableActions();
                $grid->disableCreateButton();

                $grid->column('id')->sortable();
                $grid->column('userAccount.user.nickname');
                $grid->column('userAccount.account_number');
                $grid->column('type')->display(function ($type) {
                    return admin_trans_option($type, 'types');
                });
                $grid->column('amount_in_dollar');
                $grid->column('balance_in_dollar');
                $grid->column('status')->display(function ($status) {
                    return admin_trans_option($status, 'status');
                });
                $grid->column('comment');
            });
    }

    public function title(): string
    {
        return admin_trans_label('userTransaction');
    }

    public function routeName(): string
    {
        return 'userTransaction';
    }
}

<?php

namespace App\Admin\Controllers;

use App\Models\UserOrderPayment;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class UserOrderPaymentController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(UserOrderPayment::query()->with('userOrder')->orderBy('id', 'desc'),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();

                $grid->column('id')->sortable();
                $grid->column('user_order.user_account.user.nickname');
                $grid->column('payment_gateway.name');
                $grid->column('action');
                $grid->column('stripe_intent_id');
                $grid->column('amount_in_dollar');
                $grid->column('status')->display(function ($status) {
                    return admin_trans_option($status, 'order_status');
                });
                $grid->column('created_at_formatted');
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
        return Show::make($id, UserOrderPayment::query()->with(['userOrder']),
            function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('user_order.user_account.user.nickname');
                $show->field('payment_gateway.name');
                $show->field('user_order_id');
                $show->field('amount_in_dollar');
                $show->field('action');
                $show->field('status')->as(function ($status) {
                    return admin_trans_option($status, 'order_status');
                })->label();
                $show->field('request')->json();
                $show->field('response')->json();
            });
    }

    public function title(): string
    {
        return admin_trans_label('payment');
    }

    public function routeName(): string
    {
        return 'payment';
    }
}

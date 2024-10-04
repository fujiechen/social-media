<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\UserTable;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserPayout;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class UserPayoutController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(UserPayout::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();
                $grid->disableEditButton();
                $grid->disableDeleteButton();

                $grid->column('id')->sortable();
                $grid->column('user.nickname', admin_trans_field('user'));
                $grid->column('type')->display(function ($type) {
                    return admin_trans_option($type, 'user_payout_types');
                });
                $grid->column('status')->display(function ($status) {
                    return admin_trans_option($status, 'user_payout_status');
                })->label();
                $grid->column('currency_name', admin_trans_field('currency'));
                $grid->column('amount');
                $grid->column('comment');
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
        return Show::make($id, UserPayout::query(),
            function (Show $show) {
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('user.username', admin_trans_field('user'));
                $show->field('type')->as(function ($type) {
                    return admin_trans_option($type, 'user_payout_types');
                });
                $show->field('status')->as(function ($status) {
                    return admin_trans_option($status, 'user_payout_status');
                });
                $show->field('currency_name', admin_trans_field('currency'));
                $show->field('amount');
                $show->field('comment');

                $show->relation('payments', admin_trans_field('payment'), function ($model) {
                    $grid = new Grid(Payment::class);

                    $grid->disableRowSelector();
                    $grid->disableRefreshButton();
                    $grid->disableCreateButton();
                    $grid->disablePagination();
                    $grid->disableActions();

                    $grid->model()->where('user_payout_id', $model->id)->orderBy('id', 'desc');
                    $grid->column('id')->sortable();
                    $grid->column('amount');
                    $grid->column('currency_name', admin_trans_field('currency'));
                    $grid->column('status')->display(function ($status) {
                        return admin_trans_option($status, 'user_payout_status');
                    })->label();
                    $grid->column('request')->toArray();
                    $grid->column('response')->toArray();
                    $grid->column('created_at_formatted');
                    return $grid;
                });
            });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        return Form::make(UserPayout::query(),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');

                $form->selectTable('user_id', admin_trans_field('user'))
                    ->title(admin_trans_label('select_user'))
                    ->from(UserTable::make())
                    ->model(User::class, 'id', 'username')
                    ->required();

                $form->radio('type')
                    ->options([
                        UserPayout::TYPE_COMMISSION => admin_trans_option('commission', 'user_payout_types'),
                        UserPayout::TYPE_EARNING => admin_trans_option('earning', 'user_payout_types'),
                    ])
                    ->required();

                $form->display('status')
                    ->value(UserPayout::STATUS_PENDING)
                    ->label();

                $form->hidden('status')->value(UserPayout::STATUS_PENDING);

                $form->radio('currency_name', admin_trans_field('currency'))
                    ->options([
                        env('CURRENCY_CASH') => env('CURRENCY_CASH'),
                        env('CURRENCY_POINTS') => env('CURRENCY_POINTS')
                    ])
                    ->required();

                $form->text('amount_cents', admin_trans_field('amount_cents'))->required();
                $form->textarea('comment')->required();
            });
    }


    public function title(): string
    {
        return admin_trans_label('userPayout');
    }

    public function routeName(): string
    {
        return 'userPayout';
    }
}

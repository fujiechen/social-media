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
                $grid->column('user.nickname', 'User');
                $grid->column('type');
                $grid->column('status')->label();
                $grid->column('currency_name', 'Currency');
                $grid->column('amount', 'Amount');
                $grid->column('comment', 'Comment');
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
                $show->field('user.username', 'User');
                $show->field('type');
                $show->field('status');
                $show->field('currency_name', 'Currency');
                $show->field('amount', 'Amount');
                $show->field('comment', 'Comment');

                $show->relation('payments', 'Payments', function ($model) {
                    $grid = new Grid(Payment::class);

                    $grid->disableRowSelector();
                    $grid->disableRefreshButton();
                    $grid->disableCreateButton();
                    $grid->disablePagination();
                    $grid->disableActions();

                    $grid->model()->where('user_payout_id', $model->id)->orderBy('id', 'desc');
                    $grid->column('id')->sortable();
                    $grid->column('amount', 'Amount');
                    $grid->column('currency_name', 'Currency');
                    $grid->column('status', 'Status')->label();
                    $grid->column('request', 'Request')->toArray();
                    $grid->column('response', 'Response')->toArray();
                    $grid->column('created_at_formatted', 'Created');
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

                $form->selectTable('user_id', 'User')
                    ->title('Please select user')
                    ->from(UserTable::make())
                    ->model(User::class, 'id', 'username')
                    ->required();

                $form->radio('type', 'Type')
                    ->options([
                        UserPayout::TYPE_COMMISSION => UserPayout::TYPE_COMMISSION,
                        UserPayout::TYPE_EARNING => UserPayout::TYPE_EARNING,
                    ])
                    ->required();

                $form->display('status', 'Status')
                    ->value(UserPayout::STATUS_PENDING)
                    ->label();

                $form->hidden('status')->value(UserPayout::STATUS_PENDING);

                $form->radio('currency_name', 'Currency')
                    ->options([
                        env('CURRENCY_CASH') => env('CURRENCY_CASH'),
                        env('CURRENCY_POINTS') => env('CURRENCY_POINTS')
                    ])
                    ->required();

                $form->text('amount_cents', 'Amount In Cents')->required();
                $form->textarea('comment', 'Comment')->required();
            });
    }


    public function title(): string
    {
        return 'User Payouts';
    }

    public function routeName(): string
    {
        return 'userPayout';
    }
}

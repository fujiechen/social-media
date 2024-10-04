<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\CurrencyTable;
use App\Admin\Components\Tables\UserTable;
use App\Models\Currency;
use App\Models\User;
use App\Models\UserAccount;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class UserAccountController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(UserAccount::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();

                $grid->quickSearch(['user.nickname', 'account_number']);

                $grid->column('id')->sortable();
                $grid->column('user.nickname');
                $grid->column('account_number');
                $grid->column('balance_in_dollar');
                $grid->column('product_balance');
                $grid->column('updated_at_formatted');
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
        return Show::make($id, UserAccount::query(),
            function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('user.nickname');
                $show->field('account_number');
                $show->field('currency.name');
                $show->field('balance');
                $show->field('product_balance');
                $show->field('updated_at_formatted');
                $show->field('created_at_formatted');
            });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        return Form::make(UserAccount::query(),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');

                if ($form->isCreating())
                {
                    $form->selectTable('user_id')
                        ->title(admin_trans_label('select_user'))
                        ->from(UserTable::make())
                        ->model(User::class, 'id', 'nickname')
                        ->required();

                    $form->selectTable('currency_id')
                        ->title(admin_trans_label('select_currency'))
                        ->from(CurrencyTable::make())
                        ->model(Currency::class, 'id', 'name')
                        ->required();
                }

                if ($form->isEditing()) {
                    $form->display('user.nickname');
                    $form->display('currency.name');
                    $form->display('account_number');
                    $form->text('balance');
                    $form->text('product_balance');
                }
            });
    }

    public function title(): string
    {
        return admin_trans_label('userAccount');
    }

    public function routeName(): string
    {
        return 'userAccount';
    }
}

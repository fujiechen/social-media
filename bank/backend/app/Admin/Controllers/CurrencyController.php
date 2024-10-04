<?php

namespace App\Admin\Controllers;

use App\Models\Currency;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class CurrencyController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(Currency::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();

                $grid->column('id')->sortable();
                $grid->column('name');
                $grid->column('symbol');
                $grid->column('is_default')->switch();
                $grid->column('purchase_enabled')->switch();
                $grid->column('deposit_enabled')->switch();
                $grid->column('withdraw_enabled')->switch();
                $grid->column('exchange_enabled')->switch();
                $grid->column('transfer_enabled')->switch();
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
        return Show::make($id, Currency::query(),
            function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('name');
                $show->field('symbol');
            });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        return Form::make(Currency::query(),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');
                $form->text('name')->required();
                $form->text('symbol')->required();
                $form->switch('is_default');
                $form->switch('purchase_enabled');
                $form->switch('deposit_enabled');
                $form->switch('withdraw_enabled');
                $form->switch('exchange_enabled');
                $form->switch('transfer_enabled');
            });
    }

    public function title(): string
    {
        return admin_trans_label('currency');
    }

    public function routeName(): string
    {
        return 'currency';
    }
}

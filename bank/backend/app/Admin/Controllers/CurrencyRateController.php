<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\CurrencyTable;
use App\Models\Currency;
use App\Models\CurrencyRate;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class CurrencyRateController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(CurrencyRate::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();

                $grid->column('id')->sortable();
                $grid->column('fromCurrency.name');
                $grid->column('toCurrency.name');
                $grid->column('rate');
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
        return Show::make($id, CurrencyRate::query(),
            function (Show $show) {
                $show->field('id');
                $show->field('from_currency.name');
                $show->field('to_currency.name');
                $show->field('rate');
                $show->field('created_at');
            });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        return Form::make(CurrencyRate::query(),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');

                $form->selectTable('from_currency_id')
                    ->title(admin_trans_label('select_currency'))
                    ->from(CurrencyTable::make())
                    ->model(Currency::class, 'id', 'name')
                    ->required();

                $form->selectTable('to_currency_id')
                    ->title(admin_trans_label('select_currency'))
                    ->from(CurrencyTable::make())
                    ->model(Currency::class, 'id', 'name')
                    ->required();

                $form->text('rate');
            });
    }

    public function title(): string
    {
        return admin_trans_label('currencyRate');
    }

    public function routeName(): string
    {
        return 'currencyRate';
    }
}

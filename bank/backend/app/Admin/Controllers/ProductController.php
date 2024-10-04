<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\CurrencyTable;
use App\Admin\Components\Tables\ProductCategoryTable;
use App\Models\Currency;
use App\Models\CurrencyRate;
use App\Models\Product;
use App\Models\ProductCategory;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class ProductController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(Product::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();

                $grid->column('id')->sortable();
                $grid->column('name');
                $grid->column('productCategory.name');
                $grid->column('estimate_rate');
                $grid->column('freeze_days');
                $grid->column('stock');
                $grid->column('is_recommend')->switch();
                $grid->column('deactivated_at');
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
        return Show::make($id, Product::query(),
            function (Show $show) {
                $show->field('id');
                $show->field('title');
                $show->field('name');
                $show->field('product_category.name');
                $show->field('currency.name');
                $show->field('description');
                $show->field('start_amount');
                $show->field('stock');
                $show->field('freeze_days');
                $show->field('is_recommend')->bool()->bold();
                $show->field('fund_assets');
                $show->field('fund_fact_url');
                $show->field('prospectus_url');
                $show->field('deactivated_at');
                $show->field('updated_at');
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
        return Form::make(Product::query(),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');

                $form->text('title')->required();
                $form->text('name')->required();

                $form->selectTable('product_category_id')
                    ->title(admin_trans_label('select_product_category'))
                    ->from(ProductCategoryTable::make())
                    ->model(ProductCategory::class, 'id', 'name')
                    ->required();

                $form->selectTable('currency_id')
                    ->title(admin_trans_label('select_currency'))
                    ->from(CurrencyTable::make())
                    ->model(Currency::class, 'id', 'name')
                    ->required();

                $form->textarea('description');
                $form->text('start_amount')->required();
                $form->text('stock')->required();
                $form->number('freeze_days')->required();
                $form->switch('is_recommend');
                $form->text('fund_assets');
                $form->text('fund_fact_url');
                $form->text('prospectus_url');
                $form->datetime('deactivated_at');
            });
    }

    public function title(): string
    {
        return admin_trans_label('product');
    }

    public function routeName(): string
    {
        return 'product';
    }
}

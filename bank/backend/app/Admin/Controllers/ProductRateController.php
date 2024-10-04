<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\ProductTable;
use App\Models\Product;
use App\Models\ProductRate;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class ProductRateController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(ProductRate::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();

                $grid->column('id')->sortable();
                $grid->column('product.title');
                $grid->column('product.name');
                $grid->column('rate')->append('%');
                $grid->column('value');
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
        return Show::make($id, ProductRate::query(),
            function (Show $show) {
                $show->field('id');
                $show->field('product.title');
                $show->field('product.name');
                $show->field('rate')->append('%');
                $show->field('value');
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
        return Form::make(ProductRate::query(),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');
                $form->selectTable('product_id')
                    ->title(admin_trans_label('select_product'))
                    ->from(ProductTable::make())
                    ->model(Product::class, 'id', 'name')
                    ->required();
                $form->text('rate');
                $form->text('value');
                $form->date('created_at');
            });
    }

    public function title(): string
    {
        return admin_trans_label('productRate');
    }

    public function routeName(): string
    {
        return 'productRate';
    }
}

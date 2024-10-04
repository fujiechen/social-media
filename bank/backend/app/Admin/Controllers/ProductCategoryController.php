<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\CurrencyTable;
use App\Models\Currency;
use App\Models\CurrencyRate;
use App\Models\Product;
use App\Models\ProductCategory;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class ProductCategoryController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(ProductCategory::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();

                $grid->column('id')->sortable();
                $grid->column('name');
                $grid->column('productsCount');
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
        return Show::make($id, ProductCategory::query(),
            function (Show $show) {
                $show->field('id');
                $show->field('name');
            });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        return Form::make(ProductCategory::query(),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');
                $form->text('name');
            });
    }

    public function title(): string
    {
        return admin_trans_label('productCategory');
    }

    public function routeName(): string
    {
        return 'productCategory';
    }
}

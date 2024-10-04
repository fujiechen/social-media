<?php

namespace App\Admin\Controllers;

use App\Models\PaymentGateway;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class PaymentGatewayController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(PaymentGateway::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();

                $grid->column('id')->sortable();
                $grid->column('name');
                $grid->column('payment_gateway_type');
                $grid->column('payment_methods')->toJson();
                $grid->column('is_active')->bool();
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
        return Show::make($id, PaymentGateway::query(),
            function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('name');
                $show->field('is_active')->bool();
                $show->field('payment_gateway_type');
                $show->field('payment_methods')->json();
                $show->field('endpoint_url');
                $show->field('secret');
                $show->field('public');
                $show->field('webhook_secret');
                $show->field('webhook_url');
            });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        return Form::make(PaymentGateway::query(),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');
                $form->text('name')->required();
                $form->switch('is_active');
                $form->select('payment_gateway_type')
                    ->options([
                        PaymentGateway::TYPE_STRIPE => PaymentGateway::TYPE_STRIPE,
                        PaymentGateway::TYPE_NIHAO => PaymentGateway::TYPE_NIHAO,
                    ])
                    ->required();
                $form->multipleSelect('payment_methods')
                    ->options([
                        PaymentGateway::METHOD_CC => PaymentGateway::METHOD_CC,
                        PaymentGateway::METHOD_ALIPAY => PaymentGateway::METHOD_ALIPAY,
                        PaymentGateway::METHOD_WECHAT => PaymentGateway::METHOD_WECHAT,
                        PaymentGateway::METHOD_UNION => PaymentGateway::METHOD_UNION,
                    ])
                    ->required();

                $form->text('secret')->required();
                $form->url('endpoint_url');
                $form->url('webhook_url');
                $form->text('webhook_secret');
                $form->text('public');
            });
    }

    public function title(): string
    {
        return admin_trans_label('paymentGateway');
    }

    public function routeName(): string
    {
        return 'paymentGateway';
    }
}

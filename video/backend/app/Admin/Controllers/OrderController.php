<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\ProductTable;
use App\Admin\Components\Tables\UserTable;
use App\Dtos\OrderDto;
use App\Dtos\OrderProductDto;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Dcat\Admin\Exception\InvalidArgumentException;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class OrderController extends AdminController
{
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(Order::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();
                $grid->disableDeleteButton();
                $grid->showQuickEditButton();
                $grid->disableEditButton();
                $grid->disableDeleteButton();

                $grid->column('id')->sortable();
                $grid->column('user.username', admin_trans_field('user'));
                $grid->column('product_names', admin_trans_field('product'))->label();
                $grid->column('currency_name', admin_trans_field('currency'))->label();
                $grid->column('total_amount', admin_trans_field('total_amount'));
                $grid->column('status', admin_trans_field('order_status'))->display(function ($status) {
                    return admin_trans_option($status, 'order_status');
                })->label();
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
        return Show::make($id, Order::query(),
            function (Show $show) {
                $show->disableDeleteButton();
                $show->disableEditButton();
                $show->showQuickEdit();

                $show->field('id');
                $show->field('user.nickname', admin_trans_field('user'));
                $show->field('total_amount');
                $show->field('status', admin_trans_field('order_status'))->as(function ($status) {
                    return admin_trans_option($status, 'order_status');
                })->label();
                $show->field('updated_at_formatted');
                $show->field('created_at_formatted');

                $show->relation('products', admin_trans_field('order_product'), function ($model) {
                    $grid = new Grid(OrderProduct::class);

                    $grid->disableRowSelector();
                    $grid->disableRefreshButton();
                    $grid->disableCreateButton();
                    $grid->disablePagination();
                    $grid->disableActions();

                    $grid->model()->where('order_id', $model->id)->orderBy('id', 'desc');

                    $grid->column('product.id', admin_trans_field('id'))->sortable();
                    $grid->column('product.name', admin_trans_field('name'));
                    $grid->column('product.type', admin_trans_field('type'));
                    $grid->column('product.owner', admin_trans_field('owner'));
                    $grid->column('product_json.unit_price', admin_trans_field('price'))->display(function($text) {
                        return '$' . $text;
                    });
                    $grid->column('product.updated_at_formatted', admin_trans_field('updated_at_formatted'));
                    $grid->column('product.created_at_formatted', admin_trans_field('created_at_formatted'));
                    return $grid;
                });

                $show->relation('payments', admin_trans_field('payment'), function ($model) {
                    $grid = new Grid(Payment::class);

                    $grid->disableRowSelector();
                    $grid->disableRefreshButton();
                    $grid->disableCreateButton();
                    $grid->disablePagination();
                    $grid->disableActions();

                    $grid->model()->where('order_id', $model->id)->orderBy('id', 'desc');
                    $grid->column('id')->sortable();
                    $grid->column('amount');
                    $grid->column('currency_name', admin_trans_field('currency'));
                    $grid->column('status')->display(function ($status) {
                        return admin_trans_option($status, 'payment_status');
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
        return Form::make(Order::query(),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');

                if ($form->isCreating()) {
                    $form->selectTable('user_id', admin_trans_field('user'))
                        ->from(UserTable::make())
                        ->model(User::class, 'id', 'nickname')
                        ->required();

                    $form->table('orderProducts', admin_trans_field('order_product'), function (Form\NestedForm $table) {
                        $table->selectTable('product_id', admin_trans_field('product'))
                            ->from(ProductTable::make())
                            ->model(Product::class, 'id', 'name')
                            ->width('30%');
                        $table->number('qty');
                    })->required();
                } else if ($form->isEditing()) {
                    $form->hidden('user_id');
                    $form->table('orderProducts', '', function (Form\NestedForm $table) {
                        $table->hidden('product_id');
                        $table->hidden('qty');
                    })->disableCreate()->disableDelete();
                }

                $form->radio('status', admin_trans_field('order_status'))
                    ->options([
                        Order::STATUS_PENDING => admin_trans_option('pending', 'order_status'),
                        Order::STATUS_COMPLETED => admin_trans_option('completed', 'order_status'),
                    ])
                    ->default(Order::STATUS_PENDING)
                    ->required();
            });
    }

    private function save()
    {
        try {
            $orderProductDtos = [];

            if (empty(request()->input('orderProducts'))) {
                throw new InvalidArgumentException('Invalid Input');
            }

            foreach (request()->input('orderProducts') as $orderProduct) {
                if ($orderProduct['_remove_'] === 1) {
                    continue;
                }
                $orderProductDtos[] = new OrderProductDto([
                    'productId' => $orderProduct['product_id'],
                    'qty' => $orderProduct['qty']
                ]);
            }

            $dto = new OrderDto([
                'orderId' => request()->input('id') ? request()->input('id') : null,
                'userId' => request()->input('user_id'),
                'status' => request()->input('status'),
                'orderProductDtos' => $orderProductDtos,
            ]);

            $this->orderService->updateOrCreateOrder($dto);
        } catch (\Error|\Exception $e) {
            throw new InvalidArgumentException('Invalid Inputs');
        }

        return $this->form()
            ->response()
            ->redirect('order')
            ->success(trans('admin.save_succeeded'));
    }

    public function store()
    {
        return $this->save();
    }

    public function update($id)
    {
        return $this->save();
    }

    public function title(): string
    {
        return admin_trans_label('order');
    }

    public function routeName(): string
    {
        return 'order';
    }
}

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
use Illuminate\Support\Facades\Log;

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
                $grid->column('user.username', 'User');
                $grid->column('product_names', 'Products')->label();
                $grid->column('currency_name', 'Currency')->label();
                $grid->column('total_amount', 'Total');
                $grid->column('status', 'Status')->label();
                $grid->column('updated_at_formatted', 'Updated');
                $grid->column('created_at_formatted', 'Created');
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
                $show->field('user.nickname', 'User');
                $show->field('total_amount', 'Total');
                $show->field('status', 'Status')->label();
                $show->field('updated_at_formatted', 'Updated');
                $show->field('created_at_formatted', 'Created');

                $show->relation('products', 'Products', function ($model) {
                    $grid = new Grid(OrderProduct::class);

                    $grid->disableRowSelector();
                    $grid->disableRefreshButton();
                    $grid->disableCreateButton();
                    $grid->disablePagination();
                    $grid->disableActions();

                    $grid->model()->where('order_id', $model->id)->orderBy('id', 'desc');

                    $grid->column('product.id')->sortable();
                    $grid->column('product.category.name', 'Category');
                    $grid->column('product.name', 'Name');
                    $grid->column('product.frequency', 'Frequency');
                    $grid->column('qty', 'Qty');
                    $grid->column('product_json.unit_price', 'Price')->display(function($text) {
                        return '$' . $text;
                    });
                    $grid->column('valid_util_at_formatted', 'Valid Until')->label();
                    return $grid;
                });

                $show->relation('payments', 'Payments', function ($model) {
                    $grid = new Grid(Payment::class);

                    $grid->disableRowSelector();
                    $grid->disableRefreshButton();
                    $grid->disableCreateButton();
                    $grid->disablePagination();
                    $grid->disableActions();

                    $grid->model()->where('order_id', $model->id)->orderBy('id', 'desc');
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
        return Form::make(Order::query(),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');

                if ($form->isCreating()) {
                    $form->selectTable('user_id', 'User')
                        ->from(UserTable::make())
                        ->model(User::class, 'id', 'nickname')
                        ->required();

                    $form->table('orderProducts', 'Products', function (Form\NestedForm $table) {
                        $table->selectTable('product_id', 'Product')
                            ->from(ProductTable::make())
                            ->model(Product::class, 'id', 'name')
                            ->width('30%');
                        $table->number('qty', 'Qty');
                    })->required();
                } else if ($form->isEditing()) {
                    $form->html('<div class="alert alert-info">完成订单后会触发vpn server同步创建或更新server user的ovnp文件, 同时更新category user的到期时间</div>');
                    $form->hidden('user_id');
                    $form->table('orderProducts', '', function (Form\NestedForm $table) {
                        $table->hidden('product_id');
                        $table->hidden('qty');
                    })->disableCreate()->disableDelete();
                }


                $statusOptions = [
                    Order::STATUS_PENDING => Order::STATUS_PENDING,
                ];

                if ($form->isEditing()) {
                    $statusOptions[Order::STATUS_COMPLETED ] = Order::STATUS_COMPLETED;
                }

                $form->radio('status', 'Status')
                    ->options($statusOptions)
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
            Log::error($e->getTraceAsString());
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
        return 'Orders';
    }

    public function routeName(): string
    {
        return 'order';
    }
}

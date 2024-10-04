<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\UserAccountTable;
use App\Admin\Components\Tables\UserTable;
use App\Models\User;
use App\Models\UserAccount;
use App\Models\UserOrder;
use App\Models\UserOrderPayment;
use App\Models\UserTransaction;
use App\Services\UserOrderService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Http\JsonResponse;
use Dcat\Admin\Show;
use Illuminate\Support\Str;

abstract class UserOrderController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        $type = $this->getType();

        return Grid::make(UserOrder::query()
            ->where('type', '=', $type)
            ->orderBy('id', 'desc'),
            function (Grid $grid) use ($type) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();

                $grid->quickSearch(['userAccount.user.nickname', 'userAccount.account_number']);

                $grid->column('id')->sortable();
                $grid->column('userAccount.user.nickname');
                $grid->column('userAccount.account_number');

                if ($type == UserOrder::TYPE_PURCHASE) {
                    $grid->column('product.name', admin_trans_field('product_name'));
                    $grid->column('start_amount');
                    $grid->column('freeze_days');
                } else if ($type == UserOrder::TYPE_EXCHANGE) {
                    $grid->column('toUserAccount.account_number');
                } else if ($type == UserOrder::TYPE_TRANSFER) {
                    $grid->column('toUserAccount.user.nickname');
                    $grid->column('toUserAccount.account_number');
                } else if ($type == UserOrder::TYPE_WITHDRAW) {
                    $grid->column('toUserWithdrawAccount.name');
                }

                $grid->column('amountInDollar');
                $grid->column('status')->display(function ($status) {
                    return admin_trans_option($status, 'order_status');
                });
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
        $type = $this->getType();
        return Show::make($id, UserOrder::query(),
            function (Show $show) use ($type) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('user_account.user.nickname');
                $show->field('user_account.account_number');

                if ($type == UserOrder::TYPE_PURCHASE) {
                    $show->field('product.name', admin_trans_field('product_name'));
                    $show->field('start_amount');
                    $show->field('freeze_days');
                } else if ($type == UserOrder::TYPE_EXCHANGE) {
                    $show->field('toUserAccount.account_number');
                } else if ($type == UserOrder::TYPE_TRANSFER) {
                    $show->field('to_user_account.user.nickname');
                    $show->field('to_user_account.account_number');
                } else if ($type == UserOrder::TYPE_WITHDRAW) {
                    $show->field('toUserWithdrawAccount.name');
                }

                $show->field('amountInDollar');
                $show->field('status');
                $show->field('comment');
                $show->field('updated_at_formatted');
                $show->field('created_at_formatted');

                $show->relation('userOrderPayments', admin_trans_field('userOrderPayments'), function ($model) {
                    $grid = new Grid(UserOrderPayment::class);

                    $grid->setResource('order/payment');

                    $grid->disableRowSelector();
                    $grid->disableRefreshButton();
                    $grid->disableCreateButton();
                    $grid->disablePagination();
                    $grid->showViewButton();

                    $grid->model()->where('user_order_id', $model->id);
                    $grid->column('id')->sortable();
                    $grid->column('payment_gateway.name');
                    $grid->column('action');
                    $grid->column('stripe_intent_id');
                    $grid->column('amount_in_dollar');
                    $grid->column('status');
                    $grid->column('created_at_formatted');
                    return $grid;
                });

                $show->relation('userTransactions', admin_trans_field('userTransactions'), function ($model) {
                    $grid = new Grid(UserTransaction::class);
                    $grid->disableActions();
                    $grid->disableRowSelector();
                    $grid->disableRefreshButton();
                    $grid->disableCreateButton();
                    $grid->disablePagination();

                    $grid->model()->where('user_order_id', $model->id);
                    $grid->id();
                    $grid->column('type');
                    $grid->column('amount_in_dollar');
                    $grid->column('balance_in_dollar');
                    $grid->column('status');
                    $grid->column('comment');
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
        $type = $this->getType();
        return Form::make(UserOrder::query(),
            function (Form $form) use ($type) {
                $form->display('id');
                $form->hidden('id');

                /**
                 * TODO
                 * - purchase order
                 * - withdraw order
                 */
                if ($form->isCreating())
                {
                    if ($type == UserOrder::TYPE_DEPOSIT) {
                        $form->selectTable('user_account_id')
                            ->title(admin_trans_label('select_user_account'))
                            ->from(UserAccountTable::make())
                            ->model(UserAccount::class, 'id', 'account_number')
                            ->required();
                    } else if ($type == UserOrder::TYPE_TRANSFER) {
                        $form->selectTable('user_account_id')
                            ->title(admin_trans_label('select_user_account'))
                            ->from(UserAccountTable::make())
                            ->model(UserAccount::class, 'id', 'account_number')
                            ->required();

                        $form->selectTable('to_user_id')
                            ->title(admin_trans_label('select_user_account_transfer'))
                            ->from(UserTable::make())
                            ->model(User::class, 'id', 'nickname')
                            ->required();
                    } else if ($type == UserOrder::TYPE_EXCHANGE) {
                        $form->selectTable('user_account_id', admin_trans_field('user_account_id_exchange_from'))
                            ->title(admin_trans_label('select_user_account'))
                            ->from(UserAccountTable::make())
                            ->model(UserAccount::class, 'id', 'account_number')
                            ->required();

                        $form->selectTable('to_user_account_id', admin_trans_field('user_account_id_exchange_to'))
                            ->title(admin_trans_label('select_user_account_exchange'))
                            ->from(UserAccountTable::make())
                            ->model(UserAccount::class, 'id', 'account_number')
                            ->required();
                    }
                    $form->currency('amount');
                    $form->textarea('comment');

                } else if ($form->isEditing()) {
                    $form->display('userAccount.user.nickname');
                    $form->display('userAccount.account_number');

                    if ($type == UserOrder::TYPE_PURCHASE) {
                        $form->display('product.name', admin_trans_field('product_name'));
                        $form->display('start_amount');
                        $form->text('freeze_days');
                    } else if ($type == UserOrder::TYPE_EXCHANGE) {
                        $form->display('toUserAccount.account_number');
                    } else if ($type == UserOrder::TYPE_TRANSFER) {
                        $form->display('toUserAccount.user.nickname');
                        $form->display('toUserAccount.account_number');
                    } else if ($type == UserOrder::TYPE_WITHDRAW) {
                        $form->display('toUserWithdrawAccount.name');
                    }

                    $form->display('amountInDollar');
                    $form->display('comment');
                    $form->select('status')
                        ->options([
                            UserOrder::STATUS_PENDING => UserOrder::STATUS_PENDING,
                            UserOrder::STATUS_FAILED => UserOrder::STATUS_FAILED,
                            UserOrder::STATUS_SUCCESSFUL => UserOrder::STATUS_SUCCESSFUL,
                        ])
                        ->default(UserOrder::STATUS_PENDING);
                }
            });
    }

    protected function save(): JsonResponse
    {
        /**
         * @var UserOrderService $userOrderService
         */
        $userOrderService = app(UserOrderService::class);
        $type = $this->getType();

        $id = request()->input('id');
        if (empty($id)) { //create
            if ($type == UserOrder::TYPE_DEPOSIT) {
                $userOrder = $userOrderService->createDepositOrder(
                    request()->input('user_account_id'),
                    request()->input('amount'),
                    request()->input('comment'),
                    null,
                );
            } else if ($type == UserOrder::TYPE_PURCHASE) {
                $userOrder = $userOrderService->createPurchaseOrder(request()->input('user_id'),
                    request()->input('product_id'), request()->input('amount'), request()->input('comment'));
            } else if ($type == UserOrder::TYPE_TRANSFER) {
                /**
                 * @var User $toUser
                 */
                $toUser = User::find(request()->input('to_user_id'));
                $userOrder = $userOrderService->createTransferOrder(
                    request()->input('user_account_id'),
                    $toUser->email,
                    $toUser->nickname,
                    request()->input('amount'),
                    request()->input('comment'));
            } else if ($type == UserOrder::TYPE_EXCHANGE) {
                $userOrder = $userOrderService->createExchangeOrder(request()->input('user_account_id'),
                    request()->input('to_user_account_id'), request()->input('amount'));
            }
            $id = $userOrder->id;
        } else { // update
            if (request()->input('status') == UserOrder::STATUS_FAILED) {
                $userOrderService->updateUserOrder(UserOrderService::ACTION_COMPLETE_ORDER_AS_FAILED, $id);
            } else if (request()->input('status') == UserOrder::STATUS_SUCCESSFUL) {
                $userOrderService->updateUserOrder(UserOrderService::ACTION_COMPLETE_ORDER_AS_SUCCESSFUL, $id);
            }
        }

        return $this->form()
            ->response()
            ->redirect('order/' . $this->getType() . '/' . $id . '/edit')
            ->success(trans('admin.save_succeeded'));
    }

    public function store()
    {
        return $this->save();
    }

    public function update($id)
    {
        $this->save();
    }

    public function title(): string
    {
        return admin_trans_label($this->getType()) . admin_trans_label('order');
    }

    public function routeName(): string
    {
        return admin_trans_label('order') . '/' . admin_trans_label($this->getType());
    }

    protected abstract function getType(): string;
}

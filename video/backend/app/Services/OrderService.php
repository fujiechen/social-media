<?php
namespace App\Services;

use App\Dtos\OrderDto;
use App\Dtos\OrderProductDto;
use App\Dtos\OrderSearchDto;
use App\Exceptions\IllegalArgumentException;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function fetchAllOrders(OrderSearchDto $dto): Builder {
        $query = Order::query();

        if ($dto->orderId) {
            $query->where('id', '=', $dto->orderId);
        }

        if ($dto->userId) {
            $query->where('user_id', '=', $dto->userId);
        }

        if ($dto->status) {
            $query->where('status', '=', $dto->status);
        }

        if ($dto->productId) {
            $query->join('order_products', 'order_products.order_id', '=', 'orders.id')
                ->where('order_products.product_id', '=', $dto->productId);
        }

        if ($dto->parentUserId) {
            $query->join('user_referrals', 'orders.user_id', '=', 'user_referrals.sub_user_id')
                ->where('user_referrals.user_id', '=', $dto->parentUserId)
                ->where('user_referrals.level', '=', 1)
                ->where('orders.status', '=', Order::STATUS_COMPLETED);
        }

        return $query;
    }

    /**
     * check order number allowance < successful orders
     * @param OrderDto $orderDto
     * @return bool
     */
    public function isCreateOrderAllowed(OrderDto $orderDto): bool {
        /**
         * @var OrderProductDto $orderProductDto
         */
        foreach ($orderDto->orderProductDtos as $orderProductDto) {
            $orderProductCompletedNumber = OrderProduct::query()
                ->select('order_products.*')
                ->join('orders','orders.id', '=', 'order_products.order_id')
                ->where('product_id', '=', $orderProductDto->productId)
                ->where('orders.status', '=', Order::STATUS_COMPLETED)
                ->where('orders.user_id', '=', $orderDto->userId)
                ->count();

            /**
             * @var Product $product
             */
            $product = Product::find($orderProductDto->productId);

            if (is_null($product->order_num_allowance)) {
                continue;
            }

            if ($orderProductCompletedNumber + $orderProductDto->qty > $product->order_num_allowance) {
                return false;
            }
        }

        return true;
    }


    public function updateOrCreateOrder(OrderDto $orderDto): Order {
        if (!$this->isCreateOrderAllowed($orderDto)) {
            throw new IllegalArgumentException('product.order_num_allowance', 'Number Order Allowance Exceeded');
        }

        return DB::transaction(function() use ($orderDto) {
            /**
             * @var Order $order
             */
            $order = Order::query()->find($orderDto->orderId);
            if ($order) {
                $oldStatus = $order->status;
                $order->status = $orderDto->status;

                //delete existing order products not in request dto
                if (!empty($orderDto->orderProductDtos)) {
                    OrderProduct::query()
                        ->where('order_id', '=', $order->id)
                        ->whereNotIn('product_id', array_column($orderDto->orderProductDtos, 'productId'))
                        ->delete();
                } else {
                    $order->save();
                    return $order;
                }
            } else {
                $order = Order::withoutEvents(function () use ($orderDto) {
                    return Order::query()->create([
                        'user_id' => $orderDto->userId,
                        'status' => $orderDto->status,
                    ]);
                });
                $oldStatus = $order->status;
            }

            /**
             * @var OrderProductDto $orderProductDto
             */
            foreach($orderDto->orderProductDtos as $orderProductDto) {
                // update order products from request
                if ($order->product_ids->contains($orderProductDto->productId)) {
                    /**
                     * @var OrderProduct $orderProduct
                     */
                    $orderProduct = OrderProduct::query()
                        ->where('order_id', '=',$order->id)
                        ->where('product_id', '=', $orderProductDto->productId)
                        ->first();
                    $orderProduct->qty = $orderProductDto->qty;
                    $orderProduct->save();
                } else { //create order products from request
                    /**
                     * @var Product $product
                     */
                    $product = Product::find($orderProductDto->productId);

                    OrderProduct::create([
                        'order_id' => $order->id,
                        'product_id' => $orderProductDto->productId,
                        'qty' => $orderProductDto->qty,
                        'product_json' => $product->attributesToArray(),
                        'unit_cents' => $product->unit_cents,
                        'currency_name' => $product->currency_name
                    ]);
                }
            }

            //refresh order total price
            $order->refresh();
            $order->status = $orderDto->status;
            $totalCents = 0;
            $currencyName = env('CURRENCY_CASH');
            foreach ($order->orderProducts as $orderProduct) {
                $totalCents += ((int)$orderProduct->product_json['unit_cents'] * $orderProduct->qty);
                $currencyName = $orderProduct->product->currency_name;
            }
            $order->total_cents = $totalCents;
            $order->currency_name = $currencyName;

            if ($orderDto->status == Order::STATUS_COMPLETED && $oldStatus == Order::STATUS_PENDING) {
                $order->save();
            } else {
                Order::withoutEvents(function () use ($order) {
                    $order->save();
                });
            }

            return $order;
        });
    }

    public function hasMediaProductBought(int $userId, int $mediaId): bool {
        $orderProduct = OrderProduct::query()
            ->select('order_products.*')
            ->join('orders', 'orders.id', '=', 'order_products.order_id')
            ->join('products', 'order_products.product_id', '=', 'products.id')
            ->where('orders.status', '=', Order::STATUS_COMPLETED)
            ->where('orders.user_id', '=', $userId)
            ->where('products.media_id', '=', $mediaId)
            ->first();
        return !empty($orderProduct);
    }

    public function hasProductBought(int $userId, int $productId): bool {
        $orderProduct = OrderProduct::query()
            ->select('order_products.*')
            ->join('orders', 'orders.id', '=', 'order_products.order_id')
            ->where('orders.status', '=', Order::STATUS_COMPLETED)
            ->where('orders.user_id', '=', $userId)
            ->where('order_products.product_id', '=', $productId)
            ->first();
        return !empty($orderProduct);
    }


    public function fetchAllCompletedOrderProducts(int $userId, int $productId): Builder {
        return OrderProduct::query()
            ->select('order_products.*')
            ->join('orders', 'orders.id', '=', 'order_products.order_id')
            ->where('orders.status', '=', Order::STATUS_COMPLETED)
            ->where('orders.user_id', '=', $userId)
            ->where('order_products.product_id', '=', $productId);
    }

}

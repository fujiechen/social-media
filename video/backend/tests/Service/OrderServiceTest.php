<?php

use App\Dtos\OrderDto;
use App\Dtos\OrderProductDto;
use App\Events\UserPayoutSavedEvent;
use App\Models\Media;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Role;
use App\Models\UserPayout;
use App\Services\MediaService;
use App\Services\OrderService;
use App\Services\PaymentGatewayService;
use App\Services\PaymentService;
use App\Services\UserFollowingService;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    public function testUpdateOrCreateOrder() {
        /**
         * @var OrderService $orderService
         */
        $orderService = app(OrderService::class);

        $visitor = $this->createVisitor();
        $membershipProduct1 = $this->createMembershipProduct('CNY', '10');

        $order = $orderService->updateOrCreateOrder(new OrderDto([
            'userId' => $visitor->id,
            'status' => Order::STATUS_PENDING,
            'orderProductDtos' => [
                new OrderProductDto([
                    'productId' => $membershipProduct1->id,
                    'qty' => 1,
                ]),
            ],
        ]));

        $this->assertEquals(Order::STATUS_PENDING, $order->status);
        $this->assertEquals(1, $order->orderProducts()->count());
        $this->assertEquals(10, $order->orderProducts->first()->product->unit_price);
        $this->assertEquals(0, $order->payments()->count());

        $membershipProduct2 = $this->createMembershipProduct('CNY', '20');
        $order = $orderService->updateOrCreateOrder(new OrderDto([
            'id' => $order->id,
            'userId' => $visitor->id,
            'status' => Order::STATUS_PENDING,
            'orderProductDtos' => [
                new OrderProductDto([
                    'productId' => $membershipProduct2->id,
                    'qty' => 1,
                ]),
            ],
        ]));

        $order->refresh();
        $this->assertEquals(Order::STATUS_PENDING, $order->status);
        $this->assertEquals(1, $order->orderProducts()->count());
        $this->assertEquals(20, $order->orderProducts->first()->product->unit_price);
        $this->assertEquals(0, $order->payments()->count());
    }

    public function testCompleteMembershipProductOrder() {
        /**
         * @var OrderService $orderService
         */
        $orderService = app(OrderService::class);
        $visitor = $this->createVisitor();

        // 1. create membership order
        $membershipProduct = $this->createMembershipProduct('CNY', '10');
        $order = $orderService->updateOrCreateOrder(new OrderDto([
            'userId' => $visitor->id,
            'status' => Order::STATUS_PENDING,
            'orderProductDtos' => [
                new OrderProductDto([
                    'productId' => $membershipProduct->id,
                    'qty' => 1,
                ]),
            ],
        ]));
        $this->assertEquals(Order::STATUS_PENDING, $order->status);

        // Mock an instance of the Response class
        $response = Mockery::mock(Response::class);
        $response->shouldReceive('status')->andReturn(200); // Mock status method
        $response->shouldReceive('json')->andReturn([
            'status' => Payment::STATUS_SUCCESSFUL,
        ]);

        $paymentGatewayService = Mockery::mock(PaymentGatewayService::class);
        $paymentGatewayService
            ->shouldReceive('transfer')
            ->once()
            ->andReturn($response);

        $paymentService = new PaymentService($paymentGatewayService);

        //2. pay the order successfully
        $payment = $paymentService->createOrderPayment($order->id);

        $this->assertEquals($order->id, $payment->order_id);
        $this->assertNull($payment->user_payout_id);
        $this->assertEquals(Payment::STATUS_SUCCESSFUL, $payment->status);
        $this->assertEquals('CNY', $payment->currency_name);
        $this->assertEquals(1000, $payment->amount_cents);

        //3. upgrade user role
        $order->refresh();
        $this->assertEquals(Order::STATUS_COMPLETED, $order->status);

        $visitor->refresh();
        $this->assertTrue($visitor->hasRole(Role::ROLE_MEMBERSHIP_ID));
    }

    public function testCompleteSubscriptionProductOrder() {
        Event::fake([UserPayoutSavedEvent::class]); // stop payout payment

        /**
         * @var OrderService $orderService
         */
        $orderService = app(OrderService::class);
        $user = $this->createUser();

        // 1. create membership order
        $publishUser = $this->createUser();
        $subscriptionProduct = $this->createSubscriptionProduct($publishUser->id, 'CNY', '10');
        $order = $orderService->updateOrCreateOrder(new OrderDto([
            'userId' => $user->id,
            'status' => Order::STATUS_PENDING,
            'orderProductDtos' => [
                new OrderProductDto([
                    'productId' => $subscriptionProduct->id,
                    'qty' => 1,
                ]),
            ],
        ]));
        $this->assertEquals(Order::STATUS_PENDING, $order->status);

        // Mock an instance of the Response class
        $response = Mockery::mock(Response::class);
        $response->shouldReceive('status')->andReturn(200); // Mock status method
        $response->shouldReceive('json')->andReturn([
            'status' => Payment::STATUS_SUCCESSFUL,
        ]);

        $paymentGatewayService = Mockery::mock(PaymentGatewayService::class);
        $paymentGatewayService
            ->shouldReceive('transfer')
            ->andReturn($response);

        $paymentService = new PaymentService($paymentGatewayService);

        //2. pay the order successfully
        $payment = $paymentService->createOrderPayment($order->id);

        $this->assertEquals($order->id, $payment->order_id);
        $this->assertNull($payment->user_payout_id);
        $this->assertEquals(Payment::STATUS_SUCCESSFUL, $payment->status);
        $this->assertEquals('CNY', $payment->currency_name);
        $this->assertEquals(1000, $payment->amount_cents);

        /**
         * @var UserPayout $userPayout
         */
        $userPayout = UserPayout::query()->where('user_id', '=', $publishUser->id)->first();
        $this->assertEquals($publishUser->id, $userPayout->user_id);
        $this->assertEquals(500, $userPayout->amount_cents);
        $this->assertEquals('CNY', $userPayout->currency_name);
        $this->assertEquals(UserPayout::TYPE_EARNING, $userPayout->type);
        $this->assertEquals(UserPayout::STATUS_PENDING, $userPayout->status);
        $this->assertEquals($order->orderProducts->first()->id, $userPayout->order_product_id);

        //3. visitor subscribed to publisher
        /**
         * @var UserFollowingService $userFollowingService
         */
        $userFollowingService = app(UserFollowingService::class);
        $this->assertTrue($userFollowingService->hasUserFollowedToPublisherUser($user->id, $publishUser->id));
    }


    public function testCompleteMediaProductOrder() {
        Event::fake([UserPayoutSavedEvent::class]); // stop payout payment

        /**
         * @var OrderService $orderService
         */
        $orderService = app(OrderService::class);
        $visitor = $this->createVisitor();

        // 1. create membership order
        $publishUser = $this->createUser();

        /**
         * @var Media $media
         */
        $media = $this->createMediaWithPurchasePermission($publishUser->id, 5);
        $mediaProduct = $media->mediaProduct();

        $order = $orderService->updateOrCreateOrder(new OrderDto([
            'userId' => $visitor->id,
            'status' => Order::STATUS_PENDING,
            'orderProductDtos' => [
                new OrderProductDto([
                    'productId' => $mediaProduct->id,
                    'qty' => 1,
                ]),
            ],
        ]));
        $this->assertEquals(Order::STATUS_PENDING, $order->status);

        // Mock an instance of the Response class
        $response = Mockery::mock(Response::class);
        $response->shouldReceive('status')->andReturn(200); // Mock status method
        $response->shouldReceive('json')->andReturn([
            'status' => Payment::STATUS_SUCCESSFUL,
        ]);

        $paymentGatewayService = Mockery::mock(PaymentGatewayService::class);
        $paymentGatewayService
            ->shouldReceive('transfer')
            ->andReturn($response);

        $paymentService = new PaymentService($paymentGatewayService);

        //2. pay the order successfully
        $payment = $paymentService->createOrderPayment($order->id);

        $this->assertEquals($order->id, $payment->order_id);
        $this->assertNull($payment->user_payout_id);
        $this->assertEquals(Payment::STATUS_SUCCESSFUL, $payment->status);
        $this->assertEquals('CNY', $payment->currency_name);
        $this->assertEquals(500, $payment->amount_cents);

        /**
         * @var UserPayout $userPayout
         */
        $userPayout = UserPayout::query()->where('user_id', '=', $publishUser->id)->first();
        $this->assertEquals($publishUser->id, $userPayout->user_id);
        $this->assertEquals(250, $userPayout->amount_cents);
        $this->assertEquals('CNY', $userPayout->currency_name);
        $this->assertEquals(UserPayout::TYPE_EARNING, $userPayout->type);
        $this->assertEquals(UserPayout::STATUS_PENDING, $userPayout->status);
        $this->assertEquals($order->orderProducts->first()->id, $userPayout->order_product_id);

        //3. visitor able to see the media
        /**
         * @var MediaService $mediaService
         */
        $mediaService = app(MediaService::class);
        $this->assertTrue($mediaService->isMediaAvailableToUser($visitor->id, $media->id));
    }

}

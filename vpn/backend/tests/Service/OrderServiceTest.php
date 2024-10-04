<?php

use App\Dtos\OrderDto;
use App\Dtos\OrderProductDto;
use App\Mail\CompleteOrderEmail;
use App\Models\File;
use App\Models\Order;
use App\Models\Product;
use App\Models\ServerUser;
use App\Services\CategoryUserService;
use App\Services\OpnsenseGatewayService;
use App\Services\OrderService;
use App\Services\PritunlGatewayService;
use App\Services\ServerUserService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    public function testUpdateOrCreateOrder() {

        Mail::fake();

        /**
         * @var OrderService $orderService
         */
        $orderService = app(OrderService::class);

        $user = $this->createUser();

        $category = $this->createCategory();
        $server = $this->createServer($category->id);
        $product = $this->createProduct($category->id, Product::MONTHLY, 10);


        //1. create order
        $order = $orderService->updateOrCreateOrder(new OrderDto([
            'userId' => $user->id,
            'status' => Order::STATUS_PENDING,
            'orderProductDtos' => [
                new OrderProductDto([
                    'productId' => $product->id,
                    'qty' => 1,
                ]),
            ],
        ]));

        $this->assertEquals(Order::STATUS_PENDING, $order->status);
        $this->assertEquals(1, $order->orderProducts()->count());
        $this->assertEquals(10, $order->orderProducts->first()->product->unit_price);
        $this->assertEquals(0, $order->payments()->count());

        //2. update order status to complete

        /**
         * @var File $ovpnFile
         */
        $ovpnFile = File::create([
            'name' => $category->id . '_' . $user->id . '_' . $server->id . '.ovpn',
            'bucket_type' => File::TYPE_LOCAL_BUCKET,
        ]);

        $pritunlGatewayServiceMock = Mockery::mock(PritunlGatewayService::class);
//        $pritunlGatewayServiceMock->shouldReceive('getOrCreateUserAndReturnFileIds')
//            ->andReturn([$ovpnFile->id]);
        $this->app->instance(PritunlGatewayService::class, $pritunlGatewayServiceMock);

        $uuid = Str::uuid();
        $opnsenseGatewayServiceMock = Mockery::mock(OpnsenseGatewayService::class);
        $opnsenseGatewayServiceMock->shouldReceive('createUser')->andReturn(true);
        $opnsenseGatewayServiceMock->shouldReceive('getUserUuid')->andReturn(null, $uuid);
        $opnsenseGatewayServiceMock->shouldReceive('resetConfiguration')->andReturn(true);
        $this->app->instance(OpnsenseGatewayService::class, $opnsenseGatewayServiceMock);

        $orderService->updateOrCreateOrder(new OrderDto([
            'orderId' => $order->id,
            'userId' => $user->id,
            'status' => Order::STATUS_COMPLETED,
        ]));

        Mail::assertQueued(CompleteOrderEmail::class);

        /**
         * @var CategoryUserService $categoryUserService
         */
        $categoryUserService = app(CategoryUserService::class);
        $categoryUser = $categoryUserService->findCategoryUser($category->id, $user->id);
        $this->assertTrue($categoryUser->vpn_server_synced);
        $this->assertEquals(30, $categoryUser->valid_until_at_days);
        $this->assertFalse($categoryUser->isExpired());

        /**
         * @var ServerUserService $serverUserService
         */
        $serverUserService = app(ServerUserService::class);
        $serverUsers = $serverUserService->fetchServerUsersQuery($user->id, $category->id);
        $this->assertEquals(1, $serverUsers->count());

        /**
         * @var ServerUser $serverUser
         */
        $serverUser = $serverUsers->first();

        $this->assertEquals($user->id, $serverUser->user_id);
        $this->assertEquals($server->id, $serverUser->server_id);

        $radiusUsername = $serverUserService->generateRadiusUsername($serverUser->server->category_id, $server->id, $user->id);
        $this->assertEquals($uuid, $serverUser->radius_uuid);
        $this->assertEquals($radiusUsername, $serverUser->radius_username);
    }
}

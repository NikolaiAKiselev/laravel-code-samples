<?php

namespace Tests\Unit\Models;

use App\Country;
use App\Currency;
use App\Movement;
use App\Order;
use App\PriceTier;
use App\ProductVariant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Queue;
use Tests\TestDatabaseCase;

/** @testdox Unit: Order */
class OrderTest extends TestDatabaseCase
{
    protected function setUp()
    {
        parent::setUp();

        Queue::fake();

        $this->createPriceTiers();
    }

    /** @test */
    function an_order_can_be_reordered()
    {
        Queue::fake();
        \Artisan::call('db:seed');

        $user = $this->signInAsCustomer();

        $variant = $this->create(ProductVariant::class, ['available_on_wholesale' => true]);

        /** @var Order $order */
        $order = factory(Order::class)->create();

        $quantity = 10;
        $order->addLineItem($variant, $quantity, 10);

        $order->reorder();

        $this->assertEquals($quantity, $user->cart[0]->pivot->quantity);
    }

    /** @test */
    function an_order_can_be_canceled()
    {
        $this->createDefaultLocation();

        $variant = factory('App\ProductVariant')->create(['track_stock' => true]);
        $variant->adjustStockOnHand(10);

        $order = factory('App\Order')->create();
        $order->addLineItem($variant, 5, 10);

        $order->cancel();

        $variant = ProductVariant::first();
        $this->assertEquals(10, $variant->s_o_h);
    }

    /** @test */
    function can_be_filtered_by_non_canceled_orders()
    {
        factory('App\Order')->create();
        $order = factory('App\Order')->create();

        $order->cancel();

        $orders = Order::notCancelled()->get();
        $this->assertCount(1, $orders);
        $this->assertNotEquals($order->id, $orders->first());
    }

    /** @test */
    function can_be_found_by_shopify_id()
    {
        $order = factory('App\Order')->create();
        $this->assertEquals($order->shopify_id, Order::findByShopifyId($order->shopify_id)->shopify_id);
    }

    /** @test */
    function a_line_item_can_be_added_to_a_product()
    {
        $this->createDefaultLocation();

        $variant = factory('App\ProductVariant')->create(['track_stock' => true]);
        $variant->adjustStockOnHand(10);

        $order = factory('App\Order')->create();
        $order->addLineItem($variant, 5, 10);

        $variant = ProductVariant::first();
        $this->assertEquals(5, $variant->s_o_h);

        $this->assertDatabaseHas('movements', [
            'code' => 'sale',
            'product_variant_id' => $variant->id,
        ]);
    }

    /** @test */
    function an_order_can_have_line_items()
    {
        $order = factory('App\Order')->create();
        $this->assertInstanceOf(Collection::class, $order->lineItems);
    }
}
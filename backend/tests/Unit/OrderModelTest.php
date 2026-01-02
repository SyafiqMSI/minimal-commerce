<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $order->user);
        $this->assertEquals($user->id, $order->user->id);
    }

    public function test_order_has_many_items(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $order = Order::factory()->create();
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => 'Test Product',
            'price' => 50.00,
            'quantity' => 2,
            'subtotal' => 100.00,
        ]);

        $this->assertCount(1, $order->items);
    }

    public function test_generate_order_number_creates_unique_format(): void
    {
        $orderNumber1 = Order::generateOrderNumber();
        $orderNumber2 = Order::generateOrderNumber();

        $this->assertStringStartsWith('ORD-', $orderNumber1);
        $this->assertStringStartsWith('ORD-', $orderNumber2);
        $this->assertNotEquals($orderNumber1, $orderNumber2);
    }

    public function test_order_number_format(): void
    {
        $orderNumber = Order::generateOrderNumber();

        $this->assertMatchesRegularExpression('/^ORD-\d{8}-[A-Z0-9]{6}$/', $orderNumber);
    }
}


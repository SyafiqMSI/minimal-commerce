<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $admin;
    private Category $category;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->category = Category::factory()->create();
        $this->product = Product::factory()->create([
            'category_id' => $this->category->id,
            'quantity' => 100,
            'price' => 50.00,
        ]);
    }

    public function test_can_create_order_from_cart(): void
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $token = $this->user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/orders', [
                'shipping_name' => 'John Doe',
                'shipping_phone' => '081234567890',
                'shipping_address' => '123 Main St',
                'payment_method' => 'bank_transfer',
            ]);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Order created successfully'])
            ->assertJsonStructure(['data' => ['id', 'order_number', 'total_amount']]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseMissing('cart_items', ['cart_id' => $cart->id]);
    }

    public function test_cannot_create_order_with_empty_cart(): void
    {
        $token = $this->user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/orders', [
                'shipping_name' => 'John Doe',
                'shipping_phone' => '081234567890',
                'shipping_address' => '123 Main St',
                'payment_method' => 'bank_transfer',
            ]);

        $response->assertStatus(400)
            ->assertJson(['message' => 'Cart not found']);
    }

    public function test_cannot_create_order_with_insufficient_stock(): void
    {
        $lowStockProduct = Product::factory()->create([
            'category_id' => $this->category->id,
            'quantity' => 5,
        ]);

        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $lowStockProduct->id,
            'quantity' => 10,
        ]);

        $token = $this->user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/orders', [
                'shipping_name' => 'John Doe',
                'shipping_phone' => '081234567890',
                'shipping_address' => '123 Main St',
                'payment_method' => 'bank_transfer',
            ]);

        $response->assertStatus(400)
            ->assertJsonStructure(['message']);
    }

    public function test_can_view_own_orders(): void
    {
        Order::factory()->count(3)->create(['user_id' => $this->user->id]);

        $token = $this->user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_filter_orders_by_status(): void
    {
        Order::factory()->create(['user_id' => $this->user->id, 'status' => 'pending']);
        Order::factory()->create(['user_id' => $this->user->id, 'status' => 'processing']);

        $token = $this->user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/orders?status=pending');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('pending', $response->json('data.0.status'));
    }

    public function test_can_view_single_order(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $token = $this->user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'order_number', 'status']]);
    }

    public function test_cannot_view_other_user_order(): void
    {
        $otherUser = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $otherUser->id]);

        $token = $this->user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/orders/{$order->id}");

        $response->assertStatus(403);
    }

    public function test_can_pay_order(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);

        $token = $this->user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/orders/{$order->id}/pay");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Payment successful']);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
            'status' => 'processing',
        ]);
    }

    public function test_cannot_pay_already_paid_order(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_status' => 'paid',
        ]);

        $token = $this->user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/orders/{$order->id}/pay");

        $response->assertStatus(400);
    }

    public function test_can_cancel_pending_order(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $token = $this->user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Order cancelled successfully']);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_admin_can_view_all_orders(): void
    {
        Order::factory()->count(5)->create();

        $token = $this->admin->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/admin/orders');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_admin_can_update_order_status(): void
    {
        $order = Order::factory()->create();

        $token = $this->admin->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/admin/orders/{$order->id}/status", [
                'status' => 'shipped',
            ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Order status updated successfully']);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'shipped',
        ]);
    }

    public function test_admin_can_view_order_stats(): void
    {
        Order::factory()->create(['status' => 'pending']);
        Order::factory()->create(['status' => 'processing', 'payment_status' => 'paid', 'total_amount' => 100]);

        $token = $this->admin->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/admin/orders/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_orders',
                    'pending_orders',
                    'processing_orders',
                    'total_revenue',
                ],
            ]);
    }
}


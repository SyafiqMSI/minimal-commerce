<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_is_admin_when_role_is_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($admin->isAdmin());
    }

    public function test_user_is_not_admin_when_role_is_user(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->assertFalse($user->isAdmin());
    }

    public function test_user_has_one_cart(): void
    {
        $user = User::factory()->create();
        $user->cart()->create();

        $this->assertNotNull($user->cart);
    }

    public function test_user_has_many_orders(): void
    {
        $user = User::factory()->create();
        $user->orders()->create([
            'order_number' => 'ORD-20260101-ABC123',
            'total_amount' => 100.00,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'bank_transfer',
            'shipping_name' => 'Test User',
            'shipping_phone' => '081234567890',
            'shipping_address' => 'Test Address',
        ]);

        $this->assertCount(1, $user->orders);
    }

    public function test_user_has_many_wishlists(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $user = User::factory()->create();
        $user->wishlists()->create(['product_id' => $product->id]);

        $this->assertCount(1, $user->wishlists);
    }
}


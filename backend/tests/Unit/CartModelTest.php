<?php

namespace Tests\Unit;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $cart->user);
        $this->assertEquals($user->id, $cart->user->id);
    }

    public function test_cart_has_many_items(): void
    {
        $cart = Cart::factory()->create();
        CartItem::factory()->count(3)->create(['cart_id' => $cart->id]);

        $this->assertCount(3, $cart->items);
    }

    public function test_cart_total_calculates_correctly(): void
    {
        $category = Category::factory()->create();
        $product1 = Product::factory()->create(['category_id' => $category->id, 'price' => 50.00]);
        $product2 = Product::factory()->create(['category_id' => $category->id, 'price' => 30.00]);

        $cart = Cart::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product1->id,
            'quantity' => 2,
        ]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 3,
        ]);

        $expectedTotal = (50.00 * 2) + (30.00 * 3);
        $this->assertEquals($expectedTotal, $cart->total);
    }

    public function test_cart_total_items_calculates_correctly(): void
    {
        $cart = Cart::factory()->create();
        CartItem::factory()->create(['cart_id' => $cart->id, 'quantity' => 2]);
        CartItem::factory()->create(['cart_id' => $cart->id, 'quantity' => 3]);
        CartItem::factory()->create(['cart_id' => $cart->id, 'quantity' => 1]);

        $this->assertEquals(6, $cart->total_items);
    }

    public function test_cart_total_is_zero_when_empty(): void
    {
        $cart = Cart::factory()->create();

        $this->assertEquals(0, $cart->total);
        $this->assertEquals(0, $cart->total_items);
    }
}


<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Category $category;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();
        $this->product = Product::factory()->create(['category_id' => $this->category->id]);
    }

    public function test_can_view_wishlist(): void
    {
        $product2 = Product::factory()->create(['category_id' => $this->category->id]);
        $product3 = Product::factory()->create(['category_id' => $this->category->id]);
        
        Wishlist::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);
        Wishlist::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product2->id,
        ]);
        Wishlist::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product3->id,
        ]);

        $token = $this->user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/wishlist');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_add_product_to_wishlist(): void
    {
        $token = $this->user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/wishlist', [
                'product_id' => $this->product->id,
            ]);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Product added to wishlist successfully']);

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);
    }

    public function test_cannot_add_duplicate_product_to_wishlist(): void
    {
        Wishlist::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $token = $this->user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/wishlist', [
                'product_id' => $this->product->id,
            ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Product already in wishlist']);
    }

    public function test_can_remove_product_from_wishlist(): void
    {
        $wishlist = Wishlist::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $token = $this->user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/wishlist/{$wishlist->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Product removed from wishlist successfully']);

        $this->assertDatabaseMissing('wishlists', ['id' => $wishlist->id]);
    }

    public function test_cannot_remove_other_user_wishlist(): void
    {
        $otherUser = User::factory()->create();
        $wishlist = Wishlist::factory()->create([
            'user_id' => $otherUser->id,
            'product_id' => $this->product->id,
        ]);

        $token = $this->user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/wishlist/{$wishlist->id}");

        $response->assertStatus(403);
    }

    public function test_can_remove_product_by_product_id(): void
    {
        Wishlist::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $token = $this->user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/wishlist/product/{$this->product->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Product removed from wishlist successfully']);
    }

    public function test_can_check_if_product_in_wishlist(): void
    {
        Wishlist::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $token = $this->user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/wishlist/check/{$this->product->id}");

        $response->assertStatus(200)
            ->assertJson(['in_wishlist' => true]);
    }

    public function test_wishlist_requires_authentication(): void
    {
        $response = $this->getJson('/api/wishlist');

        $response->assertStatus(401);
    }
}


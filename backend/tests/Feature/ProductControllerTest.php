<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
        $this->category = Category::factory()->create();
    }

    public function test_can_list_all_products(): void
    {
        Product::factory()->count(3)->create(['category_id' => $this->category->id]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'name', 'price', 'category']]]);
    }

    public function test_can_filter_products_by_category(): void
    {
        $category2 = Category::factory()->create();
        Product::factory()->create(['category_id' => $this->category->id]);
        Product::factory()->create(['category_id' => $category2->id]);

        $response = $this->getJson("/api/products?category_id={$this->category->id}");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_can_filter_products_by_price_range(): void
    {
        Product::factory()->create(['price' => 50, 'category_id' => $this->category->id]);
        Product::factory()->create(['price' => 150, 'category_id' => $this->category->id]);
        Product::factory()->create(['price' => 250, 'category_id' => $this->category->id]);

        $response = $this->getJson('/api/products?min_price=100&max_price=200');

        $response->assertStatus(200);
        $products = $response->json('data');
        foreach ($products as $product) {
            $this->assertGreaterThanOrEqual(100, $product['price']);
            $this->assertLessThanOrEqual(200, $product['price']);
        }
    }

    public function test_can_search_products_by_name(): void
    {
        Product::factory()->create(['name' => 'Laptop Gaming', 'category_id' => $this->category->id]);
        Product::factory()->create(['name' => 'Mouse Wireless', 'category_id' => $this->category->id]);
        Product::factory()->create(['name' => 'Keyboard Mechanical', 'category_id' => $this->category->id]);

        $response = $this->getJson('/api/products?search=laptop');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertStringContainsStringIgnoringCase('laptop', $response->json('data.0.name'));
    }

    public function test_can_view_single_product(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'name', 'price', 'category']])
            ->assertJson(['data' => ['id' => $product->id]]);
    }

    public function test_view_product_returns_404_for_nonexistent_product(): void
    {
        $response = $this->getJson('/api/products/99999');

        $response->assertStatus(404);
    }

    public function test_admin_can_create_product(): void
    {
        Storage::fake('public');
        $image = UploadedFile::fake()->image('product.jpg');

        $token = $this->admin->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/products', [
                'name' => 'New Product',
                'description' => 'Product description',
                'price' => 99.99,
                'quantity' => 10,
                'category_id' => $this->category->id,
                'image' => $image,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'data'])
            ->assertJson(['message' => 'Product created successfully']);

        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'price' => 99.99,
        ]);
    }

    public function test_user_cannot_create_product(): void
    {
        $token = $this->user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/products', [
                'name' => 'New Product',
                'description' => 'Product description',
                'price' => 99.99,
                'quantity' => 10,
                'category_id' => $this->category->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_create_product_validates_required_fields(): void
    {
        $token = $this->admin->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'description', 'price', 'quantity', 'category_id']);
    }

    public function test_admin_can_update_product(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);
        $token = $this->admin->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/products/{$product->id}", [
                'name' => 'Updated Product',
                'description' => 'Updated description',
                'price' => 149.99,
            ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Product updated successfully']);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'price' => 149.99,
        ]);
    }

    public function test_admin_can_delete_product(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'image' => 'products/test.jpg',
        ]);
        $token = $this->admin->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Product deleted successfully']);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_user_cannot_delete_product(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);
        $token = $this->user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(403);
    }
}


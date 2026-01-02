<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_belongs_to_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals($category->id, $product->category->id);
    }

    public function test_product_is_in_stock_when_quantity_greater_than_zero(): void
    {
        $product = Product::factory()->create(['quantity' => 10]);

        $this->assertTrue($product->isInStock());
    }

    public function test_product_is_not_in_stock_when_quantity_is_zero(): void
    {
        $product = Product::factory()->create(['quantity' => 0]);

        $this->assertFalse($product->isInStock());
    }

    public function test_product_has_stock_when_quantity_sufficient(): void
    {
        $product = Product::factory()->create(['quantity' => 10]);

        $this->assertTrue($product->hasStock(5));
        $this->assertTrue($product->hasStock(10));
        $this->assertFalse($product->hasStock(11));
    }

    public function test_product_can_reduce_stock(): void
    {
        $product = Product::factory()->create(['quantity' => 10]);

        $result = $product->reduceStock(5);

        $this->assertTrue($result);
        $this->assertEquals(5, $product->fresh()->quantity);
    }

    public function test_product_cannot_reduce_stock_when_insufficient(): void
    {
        $product = Product::factory()->create(['quantity' => 5]);

        $result = $product->reduceStock(10);

        $this->assertFalse($result);
        $this->assertEquals(5, $product->fresh()->quantity);
    }

    public function test_product_can_restore_stock(): void
    {
        $product = Product::factory()->create(['quantity' => 10]);

        $product->restoreStock(5);

        $this->assertEquals(15, $product->fresh()->quantity);
    }

    public function test_product_image_url_attribute(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create(['image' => 'products/test.jpg']);

        $this->assertNotNull($product->image_url);
        $this->assertStringContainsString('products/test.jpg', $product->image_url);
    }

    public function test_product_image_url_is_null_when_no_image(): void
    {
        $product = Product::factory()->create(['image' => null]);

        $this->assertNull($product->image_url);
    }
}


<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Smartphone Pro X', 'description' => 'Latest smartphone with advanced features, 128GB storage, and 5G connectivity.', 'price' => 999.99, 'category_id' => 1],
            ['name' => 'Wireless Earbuds', 'description' => 'Premium wireless earbuds with noise cancellation and 24-hour battery life.', 'price' => 149.99, 'category_id' => 1],
            ['name' => 'Laptop Ultra 15', 'description' => 'Powerful laptop with 16GB RAM, 512GB SSD, and stunning 4K display.', 'price' => 1299.99, 'category_id' => 1],
            ['name' => 'Classic Denim Jacket', 'description' => 'Timeless denim jacket made from premium cotton with stylish fit.', 'price' => 89.99, 'category_id' => 2],
            ['name' => 'Running Sneakers', 'description' => 'Lightweight running shoes with advanced cushioning technology.', 'price' => 129.99, 'category_id' => 2],
            ['name' => 'Smart Watch Elite', 'description' => 'Feature-packed smartwatch with health monitoring and GPS.', 'price' => 299.99, 'category_id' => 1],
            ['name' => 'Indoor Plant Set', 'description' => 'Beautiful set of 3 indoor plants perfect for home decoration.', 'price' => 45.99, 'category_id' => 3],
            ['name' => 'Yoga Mat Premium', 'description' => 'Non-slip yoga mat with extra cushioning for comfortable practice.', 'price' => 39.99, 'category_id' => 4],
            ['name' => 'Bestseller Novel Collection', 'description' => 'Collection of 5 bestselling novels from renowned authors.', 'price' => 59.99, 'category_id' => 5],
            ['name' => 'Garden Tool Set', 'description' => 'Complete 10-piece garden tool set for all your gardening needs.', 'price' => 79.99, 'category_id' => 3],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 为每个分类创建 3-7 个产品
        Category::all()->each(function ($category) {
            Product::factory()
                ->count(rand(3, 7))
                ->sequence(fn ($sequence) => [
                    'category_id' => $category->id,
                    'created_at' => now()->subDays(rand(1, 365)),
                    'updated_at' => now()->subDays(rand(1, 365)),
                ])
                ->create();
        });
    }
}

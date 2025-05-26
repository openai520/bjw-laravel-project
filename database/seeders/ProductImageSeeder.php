<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class ProductImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 为每个产品创建 1-4 张图片
        Product::all()->each(function ($product) {
            // 创建主图片
            ProductImage::factory()
                ->state([
                    'product_id' => $product->id,
                    'sort_order' => 1,
                    'is_main' => true,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ])
                ->create();

            // 创建额外的图片
            $additionalImages = rand(0, 3);
            if ($additionalImages > 0) {
                ProductImage::factory()
                    ->count($additionalImages)
                    ->sequence(fn ($sequence) => [
                        'product_id' => $product->id,
                        'sort_order' => $sequence->index + 2,
                        'is_main' => false,
                        'created_at' => $product->created_at,
                        'updated_at' => $product->updated_at,
                    ])
                    ->create();
            }
        });
    }
}

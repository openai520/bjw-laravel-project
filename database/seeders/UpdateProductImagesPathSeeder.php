<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateProductImagesPathSeeder extends Seeder
{
    /**
     * 修正产品图片路径
     */
    public function run(): void
    {
        $this->command->info('开始修复产品图片路径...');

        try {
            // 1. 修复product_images表中的图片路径
            $imageCount = DB::table('product_images')
                ->whereRaw("image_path LIKE 'public/%' OR image_path LIKE '/public/%' OR image_path LIKE 'storage/%' OR image_path LIKE '/storage/%'")
                ->count();

            $this->command->info("找到 {$imageCount} 个需要修复的图片路径");

            // 修复主图路径
            DB::table('product_images')
                ->whereRaw("image_path LIKE 'public/%'")
                ->update([
                    'image_path' => DB::raw("REPLACE(image_path, 'public/', '')"),
                    'updated_at' => now(),
                ]);

            DB::table('product_images')
                ->whereRaw("image_path LIKE '/public/%'")
                ->update([
                    'image_path' => DB::raw("REPLACE(image_path, '/public/', '')"),
                    'updated_at' => now(),
                ]);

            DB::table('product_images')
                ->whereRaw("image_path LIKE 'storage/%'")
                ->update([
                    'image_path' => DB::raw("REPLACE(image_path, 'storage/', '')"),
                    'updated_at' => now(),
                ]);

            DB::table('product_images')
                ->whereRaw("image_path LIKE '/storage/%'")
                ->update([
                    'image_path' => DB::raw("REPLACE(image_path, '/storage/', '')"),
                    'updated_at' => now(),
                ]);

            // 修复缩略图路径
            DB::table('product_images')
                ->whereRaw("thumbnail_path LIKE 'public/%'")
                ->update([
                    'thumbnail_path' => DB::raw("REPLACE(thumbnail_path, 'public/', '')"),
                    'updated_at' => now(),
                ]);

            DB::table('product_images')
                ->whereRaw("thumbnail_path LIKE '/public/%'")
                ->update([
                    'thumbnail_path' => DB::raw("REPLACE(thumbnail_path, '/public/', '')"),
                    'updated_at' => now(),
                ]);

            DB::table('product_images')
                ->whereRaw("thumbnail_path LIKE 'storage/%'")
                ->update([
                    'thumbnail_path' => DB::raw("REPLACE(thumbnail_path, 'storage/', '')"),
                    'updated_at' => now(),
                ]);

            DB::table('product_images')
                ->whereRaw("thumbnail_path LIKE '/storage/%'")
                ->update([
                    'thumbnail_path' => DB::raw("REPLACE(thumbnail_path, '/storage/', '')"),
                    'updated_at' => now(),
                ]);

            $this->command->info('图片路径修复完成！');
        } catch (\Exception $e) {
            $this->command->error('修复过程中发生错误：'.$e->getMessage());
            Log::error('修复产品图片路径错误：'.$e->getMessage());
        }
    }
}

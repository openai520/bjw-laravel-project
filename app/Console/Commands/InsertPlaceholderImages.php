<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InsertPlaceholderImages extends Command
{
    protected $signature = 'products:insert-placeholder-images';

    protected $description = '为没有图片的产品添加占位图记录';

    public function handle()
    {
        $this->info('开始为没有图片的产品添加占位图...');

        // 查找没有图片的产品
        $products = DB::table('products')
            ->whereRaw('NOT EXISTS (SELECT 1 FROM product_images WHERE product_images.product_id = products.id)')
            ->get(['id', 'name']);

        $count = count($products);
        $this->info("找到 {$count} 个没有图片的产品。");

        if ($count === 0) {
            $this->info('没有需要处理的产品。');

            return 0;
        }

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $inserted = 0;
        foreach ($products as $product) {
            // 为产品创建一个占位图记录
            try {
                DB::table('product_images')->insert([
                    'product_id' => $product->id,
                    'image_path' => 'placeholder.svg',
                    'thumbnail_path' => 'placeholder.svg',
                    'is_main' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $inserted++;
            } catch (\Exception $e) {
                $this->error("处理产品ID {$product->id} 时出错: ".$e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("处理完成，已为 {$inserted} 个产品添加占位图记录。");

        return 0;
    }
}

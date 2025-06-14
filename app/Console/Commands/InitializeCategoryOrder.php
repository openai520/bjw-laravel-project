<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;

class InitializeCategoryOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:initialize-category-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化现有分类的排序顺序';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('开始初始化分类排序...');

        $categories = Category::orderBy('id')->get();

        $order = 10;
        foreach ($categories as $category) {
            $category->sort_order = $order;
            $category->save();
            $order += 10;

            $this->info("分类 [{$category->name_en}] 的排序值设置为: {$category->sort_order}");
        }

        $this->info('所有分类的排序已初始化完成！');

        return Command::SUCCESS;
    }
}

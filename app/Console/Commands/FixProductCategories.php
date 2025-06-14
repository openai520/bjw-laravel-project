<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class FixProductCategories extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected $signature = 'fix:product-categories {--force : 不询问确认直接执行}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '修复产品分类关系和排序，确保所有产品都有正确的分类';

    /**
     * 执行命令
     */
    public function handle()
    {
        $this->info('开始修复产品分类关系...');

        // 确保categories表有sort_order字段
        $this->checkCategorySortOrderField();

        // 显示所有分类
        $categories = Category::all();
        if ($categories->isEmpty()) {
            $this->error('没有找到任何分类！请先创建分类。');

            if ($this->confirm('是否创建示例分类?')) {
                $this->createSampleCategories();
                $categories = Category::all();
                $this->info('已创建示例分类。');
            } else {
                return 1;
            }
        }

        $this->info('现有分类:');
        $this->table(
            ['ID', '英文名称', '法文名称', 'Slug', '排序', '产品数量'],
            $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name_en' => $category->name_en,
                    'name_fr' => $category->name_fr,
                    'slug' => $category->slug,
                    'sort_order' => $category->sort_order ?? 'N/A',
                    'products_count' => $category->products()->count(),
                ];
            })
        );

        // 检查无效分类的产品
        $productIds = Product::pluck('id');
        if ($productIds->isEmpty()) {
            $this->warn('没有找到任何产品。');

            return 0;
        }

        $invalidProducts = Product::whereNotIn('category_id', $categories->pluck('id'))->get();

        if ($invalidProducts->isNotEmpty()) {
            $this->warn('发现 '.$invalidProducts->count().' 个产品的分类ID无效:');
            $this->table(
                ['产品ID', '产品名称', '无效的分类ID'],
                $invalidProducts->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'category_id' => $product->category_id,
                    ];
                })
            );

            $force = $this->option('force');
            if ($force || $this->confirm('是否修复这些产品的分类?')) {
                $defaultCategory = $categories->first();
                foreach ($invalidProducts as $product) {
                    $product->category_id = $defaultCategory->id;
                    $product->save();
                    $this->info("已修复产品 {$product->id} ({$product->name}): 分类更改为 {$defaultCategory->name_en} (ID: {$defaultCategory->id})");
                }
                $this->info('所有无效分类的产品已修复。');
            }
        } else {
            $this->info('所有产品都有有效的分类。');
        }

        // 清除缓存
        if ($this->confirm('是否清除所有产品和分类缓存?')) {
            $this->clearCaches();
            $this->info('缓存已清除。');
        }

        $this->info('产品分类修复完成。');

        return 0;
    }

    /**
     * 检查分类表是否有排序字段，如果没有则添加
     */
    private function checkCategorySortOrderField()
    {
        if (! Schema::hasColumn('categories', 'sort_order')) {
            $this->info('正在添加分类排序字段...');
            Schema::table('categories', function ($table) {
                $table->integer('sort_order')->default(0)->after('slug');
            });
            $this->info('已添加sort_order字段到categories表。');

            // 更新现有分类的排序
            $categories = Category::all();
            foreach ($categories as $index => $category) {
                $category->sort_order = $index + 1;
                $category->save();
            }
            $this->info('已为所有现有分类设置顺序。');
        }
    }

    /**
     * 创建示例分类
     */
    private function createSampleCategories()
    {
        $categories = [
            [
                'name_en' => 'Samsung Charger',
                'name_fr' => 'Chargeur Samsung',
                'slug' => 'samsung-charger',
                'sort_order' => 1,
            ],
            [
                'name_en' => 'iPhone Charger',
                'name_fr' => 'Chargeur iPhone',
                'slug' => 'iphone-charger',
                'sort_order' => 2,
            ],
            [
                'name_en' => 'Cable',
                'name_fr' => 'Câble',
                'slug' => 'cable',
                'sort_order' => 3,
            ],
            [
                'name_en' => 'Watch',
                'name_fr' => 'Montre',
                'slug' => 'watch',
                'sort_order' => 4,
            ],
            [
                'name_en' => 'Headphones',
                'name_fr' => 'Écouteurs',
                'slug' => 'headphones',
                'sort_order' => 5,
            ],
            [
                'name_en' => 'TWS Earphones',
                'name_fr' => 'Écouteurs TWS',
                'slug' => 'tws-earphones',
                'sort_order' => 6,
            ],
            [
                'name_en' => 'Sports Headphones',
                'name_fr' => 'Écouteurs de Sport',
                'slug' => 'sports-headphones',
                'sort_order' => 7,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }

    /**
     * 清除所有相关缓存
     */
    private function clearCaches()
    {
        // 清除所有产品相关缓存
        Cache::flush();

        // 或者更有针对性地清除
        // Cache::tags(['products'])->flush();
        // foreach (Category::pluck('id') as $categoryId) {
        //     Cache::tags(['category-'.$categoryId])->flush();
        // }
    }
}

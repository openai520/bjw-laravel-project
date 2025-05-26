<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UpdateImageExtensions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:fix-extensions {--dry-run : 不实际更新数据库，只显示将要更改的内容}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '检查并修复产品图片扩展名，确保数据库记录与服务器上的实际文件扩展名一致';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->info('正在执行干运行模式（不会实际更新数据库）');
        }

        $this->info('开始检查产品图片文件...');
        
        // 从数据库中获取所有图片记录
        $images = DB::table('product_images')->select('id', 'image_path', 'thumbnail_path')->get();
        
        $this->info("找到 {$images->count()} 条图片记录需要检查");
        
        $updatedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        
        $updates = [];
        
        // 进度条
        $bar = $this->output->createProgressBar(count($images));
        $bar->start();
        
        foreach ($images as $image) {
            $bar->advance();
            
            // 处理主图
            $mainPath = $image->image_path;
            $updatedMainPath = $this->checkAndFixExtension($mainPath);
            
            // 处理缩略图
            $thumbPath = $image->thumbnail_path;
            $updatedThumbPath = $this->checkAndFixExtension($thumbPath);
            
            // 如果路径有变更，记录更新
            if ($mainPath !== $updatedMainPath || $thumbPath !== $updatedThumbPath) {
                $updates[] = [
                    'id' => $image->id,
                    'old_main' => $mainPath,
                    'new_main' => $updatedMainPath,
                    'old_thumb' => $thumbPath,
                    'new_thumb' => $updatedThumbPath
                ];
                
                // 更新数据库
                if (!$dryRun) {
                    try {
                        DB::table('product_images')
                            ->where('id', $image->id)
                            ->update([
                                'image_path' => $updatedMainPath,
                                'thumbnail_path' => $updatedThumbPath
                            ]);
                        $updatedCount++;
                    } catch (\Exception $e) {
                        $this->error("更新ID={$image->id}的记录时出错: " . $e->getMessage());
                        Log::error("更新产品图片扩展名失败", [
                            'id' => $image->id,
                            'error' => $e->getMessage()
                        ]);
                        $errorCount++;
                    }
                } else {
                    $updatedCount++;
                }
            } else {
                $skippedCount++;
            }
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // 显示结果统计
        $this->info("检查完成!");
        $this->info("总记录数: " . count($images));
        $this->info("需要更新: $updatedCount");
        $this->info("无需更新: $skippedCount");
        $this->info("错误数: $errorCount");
        
        // 如果是干运行模式，显示将要更新的内容
        if ($dryRun && count($updates) > 0) {
            $this->info("\n以下是将要更新的记录:");
            $this->table(
                ['ID', '原主图路径', '新主图路径', '原缩略图路径', '新缩略图路径'],
                collect($updates)->map(function ($item) {
                    return [
                        $item['id'],
                        $item['old_main'],
                        $item['new_main'],
                        $item['old_thumb'],
                        $item['new_thumb']
                    ];
                })->toArray()
            );
            $this->info("使用 php artisan images:fix-extensions 命令实际执行更新");
        }
        
        return 0;
    }
    
    /**
     * 检查并修复文件扩展名
     * 
     * @param string $path 文件路径
     * @return string 修复后的路径
     */
    private function checkAndFixExtension($path)
    {
        if (empty($path)) {
            return $path;
        }
        
        // 提取扩展名和路径部分
        $pathInfo = pathinfo($path);
        $extension = strtolower($pathInfo['extension'] ?? '');
        
        // 如果没有扩展名，直接返回
        if (empty($extension)) {
            return $path;
        }
        
        // 标准化路径
        $standardPath = $path;
        if (!str_starts_with($standardPath, 'products/')) {
            $standardPath = 'products/' . $standardPath;
        }
        
        // 检查数据库中记录的扩展名的文件是否存在
        $fileExists = Storage::disk('public')->exists($standardPath);
        
        if ($fileExists) {
            // 文件存在，无需修改
            return $path;
        }
        
        // 文件不存在，尝试其他常见扩展名
        $alternativeExtensions = ['jpeg', 'jpg', 'png', 'webp', 'gif'];
        $dirname = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        
        foreach ($alternativeExtensions as $ext) {
            if ($ext === $extension) {
                continue; // 跳过原始扩展名
            }
            
            $alternativePath = "{$dirname}/{$filename}.{$ext}";
            
            // 标准化替代路径
            $standardAltPath = $alternativePath;
            if (!str_starts_with($standardAltPath, 'products/')) {
                $standardAltPath = 'products/' . $standardAltPath;
            }
            
            if (Storage::disk('public')->exists($standardAltPath)) {
                // 找到了具有不同扩展名的文件
                return $alternativePath;
            }
        }
        
        // 尝试所有替代扩展名后仍未找到匹配的文件，返回原始路径
        return $path;
    }
}

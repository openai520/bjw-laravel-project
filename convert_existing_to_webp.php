<?php
/**
 * 将现有的JPEG图片转换为WebP格式
 * 用于修复批量上传未正确转换WebP的产品
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ImageService;

// 启动Laravel应用
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$imageService = new ImageService();

// 需要转换的产品ID
$productIds = [731, 733, 734];

echo "=== 开始转换现有JPEG图片为WebP格式 ===\n";

foreach ($productIds as $productId) {
    $product = Product::with('images')->find($productId);
    
    if (!$product) {
        echo "产品ID {$productId} 未找到\n";
        continue;
    }
    
    echo "\n处理产品ID: {$productId} - {$product->name}\n";
    
    // 转换主图片
    if ($product->main_image_url && !str_ends_with($product->main_image_url, '.webp')) {
        echo "  转换主图片: {$product->main_image_url}\n";
        
        $currentPath = str_replace('/storage/', 'storage/app/public/', $product->main_image_url);
        $fullPath = __DIR__ . '/' . $currentPath;
        
        if (file_exists($fullPath)) {
            try {
                // 使用ImageService重新优化现有图片
                $newPath = $imageService->reoptimizeExistingImage(
                    $currentPath,
                    'products',
                    true,
                    'webp'
                );
                
                // 更新产品的主图片URL
                $product->main_image_url = '/storage/' . $newPath;
                $product->save();
                
                echo "    ✅ 主图片转换成功: /storage/{$newPath}\n";
                
                // 删除旧的JPEG文件
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                    echo "    🗑️ 删除旧JPEG文件: {$fullPath}\n";
                }
                
            } catch (Exception $e) {
                echo "    ❌ 主图片转换失败: " . $e->getMessage() . "\n";
            }
        } else {
            echo "    ⚠️ 主图片文件不存在: {$fullPath}\n";
        }
    } else {
        echo "  主图片已是WebP格式或不存在\n";
    }
    
    // 转换关联图片
    foreach ($product->images as $index => $image) {
        if (!str_ends_with($image->image_path, '.webp')) {
            echo "  转换关联图片 {$index}: {$image->image_path}\n";
            
            $currentPath = 'storage/app/public/' . $image->image_path;
            $fullPath = __DIR__ . '/' . $currentPath;
            
            if (file_exists($fullPath)) {
                try {
                    // 转换主图片
                    $newMainPath = $imageService->reoptimizeExistingImage(
                        $currentPath,
                        'products',
                        true,
                        'webp'
                    );
                    
                    // 转换缩略图
                    $newThumbnailPath = null;
                    if ($image->thumbnail_path) {
                        $thumbnailCurrentPath = 'storage/app/public/' . $image->thumbnail_path;
                        $thumbnailFullPath = __DIR__ . '/' . $thumbnailCurrentPath;
                        
                        if (file_exists($thumbnailFullPath)) {
                            $newThumbnailPath = $imageService->reoptimizeExistingImage(
                                $thumbnailCurrentPath,
                                'products',
                                false, // 不需要再创建缩略图
                                'webp'
                            );
                            
                            // 删除旧的缩略图
                            if (file_exists($thumbnailFullPath)) {
                                unlink($thumbnailFullPath);
                            }
                        }
                    }
                    
                    // 更新数据库记录
                    $image->image_path = $newMainPath;
                    if ($newThumbnailPath) {
                        $image->thumbnail_path = $newThumbnailPath;
                    }
                    $image->save();
                    
                    echo "    ✅ 关联图片转换成功: {$newMainPath}\n";
                    if ($newThumbnailPath) {
                        echo "    ✅ 缩略图转换成功: {$newThumbnailPath}\n";
                    }
                    
                    // 删除旧的主图片
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                        echo "    🗑️ 删除旧JPEG文件: {$fullPath}\n";
                    }
                    
                } catch (Exception $e) {
                    echo "    ❌ 关联图片转换失败: " . $e->getMessage() . "\n";
                }
            } else {
                echo "    ⚠️ 关联图片文件不存在: {$fullPath}\n";
            }
        } else {
            echo "  关联图片 {$index} 已是WebP格式\n";
        }
    }
}

echo "\n=== WebP转换完成 ===\n";
echo "请检查转换结果:\n";

foreach ($productIds as $productId) {
    $product = Product::find($productId);
    if ($product) {
        $extension = pathinfo($product->main_image_url, PATHINFO_EXTENSION);
        echo "产品ID {$productId}: {$product->main_image_url} (格式: {$extension})\n";
    }
} 
<?php

/**
 * 转换特定产品的JPEG图片为WebP格式
 * 专门处理产品733和734
 */

require_once __DIR__.'/vendor/autoload.php';

use App\Models\Product;
use App\Services\ImageService;
use Illuminate\Support\Facades\Storage;

// 启动Laravel应用
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$imageService = new ImageService;

// 需要转换的产品ID
$productIds = [733, 734];

echo "=== 开始转换指定产品的JPEG图片为WebP格式 ===\n";

foreach ($productIds as $productId) {
    $product = Product::with('images')->find($productId);

    if (! $product) {
        echo "产品ID {$productId} 未找到\n";

        continue;
    }

    echo "\n处理产品ID: {$productId} - {$product->name}\n";

    // 转换关联图片
    foreach ($product->images as $index => $image) {
        if (! str_ends_with($image->image_path, '.webp')) {
            echo "  转换关联图片 {$index}: {$image->image_path}\n";

            $mainImageFullPath = Storage::disk('public')->path($image->image_path);

            if (file_exists($mainImageFullPath)) {
                try {
                    // 使用临时文件名创建UploadedFile对象
                    $tempFileName = basename($image->image_path);
                    $uploadedFile = new \Illuminate\Http\UploadedFile(
                        $mainImageFullPath,
                        $tempFileName,
                        mime_content_type($mainImageFullPath),
                        null,
                        true // test mode
                    );

                    // 使用ImageService转换图片
                    $imagePaths = $imageService->saveOptimizedImage(
                        $uploadedFile,
                        'products',
                        true, // 创建缩略图
                        true, // 调整尺寸
                        'webp' // 转换为WebP格式
                    );

                    if (isset($imagePaths['main']) && isset($imagePaths['thumbnail'])) {
                        // 删除旧的JPEG文件
                        if (Storage::disk('public')->exists($image->image_path)) {
                            Storage::disk('public')->delete($image->image_path);
                            echo "    🗑️ 删除旧JPEG主图: {$image->image_path}\n";
                        }

                        if ($image->thumbnail_path && Storage::disk('public')->exists($image->thumbnail_path)) {
                            Storage::disk('public')->delete($image->thumbnail_path);
                            echo "    🗑️ 删除旧JPEG缩略图: {$image->thumbnail_path}\n";
                        }

                        // 更新数据库记录
                        $image->image_path = $imagePaths['main'];
                        $image->thumbnail_path = $imagePaths['thumbnail'];
                        $image->save();

                        echo "    ✅ 主图转换成功: {$imagePaths['main']}\n";
                        echo "    ✅ 缩略图转换成功: {$imagePaths['thumbnail']}\n";
                    } else {
                        echo "    ❌ ImageService转换失败，返回路径无效\n";
                    }

                } catch (Exception $e) {
                    echo '    ❌ 关联图片转换失败: '.$e->getMessage()."\n";
                }
            } else {
                echo "    ⚠️ 关联图片文件不存在: {$mainImageFullPath}\n";
            }
        } else {
            echo "  关联图片 {$index} 已是WebP格式\n";
        }
    }
}

echo "\n=== WebP转换完成 ===\n";
echo "检查转换结果:\n";

foreach ($productIds as $productId) {
    $product = Product::with('images')->find($productId);
    if ($product) {
        echo "产品ID {$productId} - {$product->name}:\n";
        foreach ($product->images as $index => $image) {
            $extension = pathinfo($image->image_path, PATHINFO_EXTENSION);
            echo "  图片{$index}: {$image->image_path} (格式: {$extension})\n";
        }
    }
}

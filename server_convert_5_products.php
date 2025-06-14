<?php
/**
 * 服务器端WebP转换脚本
 * 专门转换ID为694, 695, 696, 697, 698的产品图片
 *
 * 上传到服务器后运行: php server_convert_5_products.php
 */

// Laravel Bootstrap
require_once __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\ProductImage;

// 指定要转换的产品ID
$target_product_ids = [694, 695, 696, 697, 698];

echo "=== 服务器端WebP转换 - 指定5个产品 ===\n";
echo '目标产品ID: '.implode(', ', $target_product_ids)."\n";
echo '服务器时间: '.date('Y-m-d H:i:s')."\n\n";

// 转换函数
function convertToWebP($source_path, $dest_path, $quality = 92)
{
    if (! file_exists($source_path)) {
        return ['success' => false, 'error' => 'Source file not found: '.$source_path];
    }

    $image_info = getimagesize($source_path);
    if (! $image_info) {
        return ['success' => false, 'error' => 'Invalid image file'];
    }

    // 根据MIME类型创建图像资源
    switch ($image_info['mime']) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source_path);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source_path);
            // 保持PNG透明度
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($source_path);
            break;
        case 'image/webp':
            return ['success' => false, 'error' => 'Already WebP format'];
        default:
            return ['success' => false, 'error' => 'Unsupported image format: '.$image_info['mime']];
    }

    if (! $image) {
        return ['success' => false, 'error' => 'Failed to create image resource'];
    }

    // 创建目标目录
    $dest_dir = dirname($dest_path);
    if (! is_dir($dest_dir)) {
        mkdir($dest_dir, 0755, true);
    }

    // 转换为WebP
    $success = imagewebp($image, $dest_path, $quality);
    imagedestroy($image);

    if ($success) {
        $original_size = filesize($source_path);
        $webp_size = filesize($dest_path);
        $compression_ratio = round((1 - $webp_size / $original_size) * 100, 1);

        return [
            'success' => true,
            'original_size' => $original_size,
            'webp_size' => $webp_size,
            'compression_ratio' => $compression_ratio,
        ];
    } else {
        return ['success' => false, 'error' => 'WebP conversion failed'];
    }
}

$image_base_path = public_path('images/products/');
$total_processed = 0;
$total_converted = 0;
$total_errors = 0;
$log_file = 'webp_conversion_server_'.date('Y-m-d_H-i-s').'.log';

foreach ($target_product_ids as $product_id) {
    echo "处理产品ID: {$product_id}\n";

    try {
        // 获取产品信息
        $product = Product::find($product_id);

        if (! $product) {
            echo "  ❌ 产品不存在: ID {$product_id}\n";
            $total_errors++;

            continue;
        }

        echo "  📱 产品名称: {$product->name}\n";
        echo "  💰 价格: \${$product->price}\n";

        // 获取产品的所有图片
        $images = ProductImage::where('product_id', $product_id)->get();

        if ($images->isEmpty()) {
            echo "  ⚠️  没有找到产品图片\n";

            continue;
        }

        echo '  🖼️  找到 '.count($images)." 张图片\n";

        foreach ($images as $image) {
            $total_processed++;
            $image_path = $image->image_path;

            // 检查是否已经是WebP格式
            if (pathinfo($image_path, PATHINFO_EXTENSION) === 'webp') {
                echo "    ✅ 已是WebP格式: {$image_path}\n";

                continue;
            }

            $original_path = $image_base_path.$image_path;
            $webp_path = $image_base_path.pathinfo($image_path, PATHINFO_DIRNAME).'/'.
                        pathinfo($image_path, PATHINFO_FILENAME).'.webp';

            echo "    🔄 转换: {$image_path}\n";
            echo "       源文件: {$original_path}\n";
            echo "       目标文件: {$webp_path}\n";

            $result = convertToWebP($original_path, $webp_path);

            if ($result['success']) {
                // 更新数据库中的图片路径
                $new_image_path = pathinfo($image_path, PATHINFO_DIRNAME).'/'.
                                 pathinfo($image_path, PATHINFO_FILENAME).'.webp';

                $image->update([
                    'image_path' => $new_image_path,
                    'updated_at' => now(),
                ]);

                echo "    ✅ 转换成功!\n";
                echo '       原始大小: '.round($result['original_size'] / 1024, 1)." KB\n";
                echo '       WebP大小: '.round($result['webp_size'] / 1024, 1)." KB\n";
                echo "       压缩率: {$result['compression_ratio']}%\n";
                echo "       数据库已更新: {$new_image_path}\n";

                // 删除原始文件
                if (file_exists($original_path)) {
                    unlink($original_path);
                    echo "       🗑️  删除原始文件: {$image_path}\n";
                }

                $total_converted++;

                // 记录到日志文件
                file_put_contents($log_file,
                    '['.date('Y-m-d H:i:s')."] 成功转换: 产品{$product_id} - {$image_path} -> {$new_image_path} (压缩率: {$result['compression_ratio']}%)\n",
                    FILE_APPEND
                );

            } else {
                echo '    ❌ 转换失败: '.$result['error']."\n";
                $total_errors++;

                // 记录错误到日志文件
                file_put_contents($log_file,
                    '['.date('Y-m-d H:i:s')."] 转换失败: 产品{$product_id} - {$image_path} - {$result['error']}\n",
                    FILE_APPEND
                );
            }
        }

    } catch (Exception $e) {
        echo "  ❌ 处理产品 {$product_id} 时出错: ".$e->getMessage()."\n";
        $total_errors++;

        // 记录错误到日志文件
        file_put_contents($log_file,
            '['.date('Y-m-d H:i:s')."] 产品处理错误: {$product_id} - {$e->getMessage()}\n",
            FILE_APPEND
        );
    }

    echo "\n"; // 空行分隔
}

echo "=== 转换完成 ===\n";
echo "总处理图片: {$total_processed}\n";
echo "成功转换: {$total_converted}\n";
echo "错误数量: {$total_errors}\n";
echo "日志文件: {$log_file}\n";

// 显示最终结果
echo "\n🎉 5个产品的WebP转换任务完成!\n";
echo "📊 处理统计:\n";
echo "   - 总处理图片: {$total_processed}\n";
echo "   - 成功转换: {$total_converted}\n";
echo "   - 错误数量: {$total_errors}\n";
echo "📝 详细日志: {$log_file}\n";

// 检查是否需要清理缓存
if ($total_converted > 0) {
    echo "\n🔄 清理Laravel缓存...\n";
    try {
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        echo "✅ 缓存清理完成\n";
    } catch (Exception $e) {
        echo '⚠️ 缓存清理失败: '.$e->getMessage()."\n";
    }
}

function now()
{
    return date('Y-m-d H:i:s');
}
?> 
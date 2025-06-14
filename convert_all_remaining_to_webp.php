<?php
/**
 * 批量转换所有剩余产品图片为WebP格式
 * 分批处理避免内存溢出和超时
 */

// Laravel Bootstrap
require_once __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// 设置更长的执行时间
set_time_limit(0);
ini_set('memory_limit', '1G');

function log_message($message)
{
    echo '['.date('Y-m-d H:i:s').'] '.$message."\n";
}

function convertToWebP($source, $dest, $quality = 92)
{
    $info = getimagesize($source);
    if ($info === false) {
        return ['success' => false, 'error' => 'Not a valid image'];
    }

    $image = null;
    switch ($info[2]) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($source);
            imagealphablending($image, false);
            imagesavealpha($image, true);
            break;
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($source);
            break;
        default:
            return ['success' => false, 'error' => 'Unsupported image type'];
    }

    if (! $image) {
        return ['success' => false, 'error' => 'Failed to create image resource'];
    }

    // 确保目标目录存在
    $dest_dir = dirname($dest);
    if (! is_dir($dest_dir)) {
        mkdir($dest_dir, 0755, true);
    }

    // 转换为WebP
    $success = imagewebp($image, $dest, $quality);
    imagedestroy($image);

    if ($success) {
        $original_size = filesize($source);
        $webp_size = filesize($dest);
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

function findImageFile($imagePath, $basePaths)
{
    foreach ($basePaths as $basePath) {
        $fullPath = $basePath.$imagePath;
        if (file_exists($fullPath)) {
            return $fullPath;
        }
    }

    return null;
}

log_message('=== 开始批量转换剩余产品图片为WebP格式 ===');

// 可能的图片基础路径
$image_base_paths = [
    '/www/wwwroot/kalala.me/public/storage/',
    '/www/wwwroot/kalala.me/public/build/storage/',
    '/www/wwwroot/kalala.me/storage/app/public/',
];

// 新的WebP存储路径
$webp_storage_path = '/www/wwwroot/kalala.me/storage/app/public/';

// 统计变量
$total_processed = 0;
$total_converted = 0;
$total_errors = 0;
$total_skipped = 0;
$batch_size = 20; // 每批处理20个产品

// 获取需要转换的产品（分批处理）
$need_conversion_count = DB::table('products')
    ->join('product_images', 'products.id', '=', 'product_images.product_id')
    ->where('product_images.image_path', 'not like', '%.webp')
    ->where('product_images.image_path', 'not like', '%placeholder%')
    ->where('product_images.image_path', '!=', '')
    ->where('product_images.image_path', 'is not null')
    ->distinct('products.id')
    ->count('products.id');

log_message("需要转换的产品总数: {$need_conversion_count}");

$processed_products = 0;
$offset = 0;

while ($processed_products < $need_conversion_count) {
    log_message("\n--- 处理批次 ".(intval($offset / $batch_size) + 1).' ---');

    // 获取当前批次的产品
    $products = DB::table('products')
        ->join('product_images', 'products.id', '=', 'product_images.product_id')
        ->where('product_images.image_path', 'not like', '%.webp')
        ->where('product_images.image_path', 'not like', '%placeholder%')
        ->where('product_images.image_path', '!=', '')
        ->where('product_images.image_path', 'is not null')
        ->select('products.id', 'products.name')
        ->distinct('products.id')
        ->offset($offset)
        ->limit($batch_size)
        ->get();

    if ($products->isEmpty()) {
        log_message('没有更多产品需要处理，退出循环');
        break;
    }

    foreach ($products as $product) {
        try {
            log_message("\n🔄 处理产品ID: {$product->id} - {$product->name}");

            // 获取该产品的所有非WebP图片（排除占位符）
            $images = DB::table('product_images')
                ->where('product_id', $product->id)
                ->where('image_path', 'not like', '%.webp')
                ->where('image_path', 'not like', '%placeholder%')
                ->where('image_path', '!=', '')
                ->where('image_path', 'is not null')
                ->get();

            if ($images->isEmpty()) {
                log_message("  ✅ 产品 {$product->id} 所有图片已是WebP格式");
                $total_skipped++;

                continue;
            }

            foreach ($images as $image) {
                $total_processed++;
                $image_path = $image->image_path;

                log_message("    🔄 转换图片: {$image_path}");

                // 查找原始图片文件
                $original_file = findImageFile($image_path, $image_base_paths);

                if (! $original_file) {
                    log_message("    ❌ 原始文件未找到: {$image_path}");
                    $total_errors++;

                    continue;
                }

                // 生成WebP文件路径
                $path_info = pathinfo($image_path);
                $webp_filename = $path_info['filename'].'.webp';
                $webp_relative_path = $path_info['dirname'].'/'.$webp_filename;
                $webp_full_path = $webp_storage_path.$webp_relative_path;

                // 确保WebP目录存在
                $webp_dir = dirname($webp_full_path);
                if (! is_dir($webp_dir)) {
                    mkdir($webp_dir, 0755, true);
                }

                // 执行转换
                $result = convertToWebP($original_file, $webp_full_path);

                if ($result['success']) {
                    // 更新数据库中的图片路径
                    DB::table('product_images')
                        ->where('id', $image->id)
                        ->update([
                            'image_path' => $webp_relative_path,
                            'updated_at' => now(),
                        ]);

                    log_message('    ✅ 转换成功!');
                    log_message('       原始大小: '.round($result['original_size'] / 1024, 1).' KB');
                    log_message('       WebP大小: '.round($result['webp_size'] / 1024, 1).' KB');
                    log_message("       压缩率: {$result['compression_ratio']}%");
                    log_message("       数据库已更新: {$webp_relative_path}");

                    $total_converted++;
                } else {
                    log_message('    ❌ 转换失败: '.$result['error']);
                    $total_errors++;
                }
            }

            $processed_products++;

        } catch (Exception $e) {
            log_message("  ❌ 处理产品 {$product->id} 时出错: ".$e->getMessage());
            $total_errors++;
        }

        // 每10个产品显示一次进度
        if ($processed_products % 10 == 0) {
            log_message("\n📊 进度报告:");
            log_message("已处理产品: {$processed_products}/{$need_conversion_count}");
            log_message("已转换图片: {$total_converted}");
            log_message("错误数量: {$total_errors}");
            log_message("跳过数量: {$total_skipped}");
        }
    }

    $offset += $batch_size;

    // 短暂休息避免服务器过载
    sleep(1);
}

// 清理Laravel缓存
try {
    log_message("\n🧹 清理Laravel缓存...");
    shell_exec('cd /www/wwwroot/kalala.me && php artisan cache:clear');
    log_message('✅ 缓存清理完成');
} catch (Exception $e) {
    log_message('⚠️ 缓存清理失败: '.$e->getMessage());
}

log_message("\n=== 批量转换完成 ===");
log_message('📊 最终统计:');
log_message("总处理产品: {$processed_products}");
log_message("总处理图片: {$total_processed}");
log_message("成功转换: {$total_converted}");
log_message("错误数量: {$total_errors}");
log_message("跳过数量: {$total_skipped}");

$success_rate = $total_processed > 0 ? round(($total_converted / $total_processed) * 100, 1) : 0;
log_message("转换成功率: {$success_rate}%");

if ($total_converted > 0) {
    log_message("\n🎉 批量转换任务成功完成!");
    log_message('现在可以安全地修改图片访问路径逻辑了');
} else {
    log_message("\n⚠️ 没有图片被转换，请检查路径配置");
}
?> 
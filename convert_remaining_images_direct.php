<?php

/**
 * 直接转换剩余图片为WebP格式
 * 不依赖产品查询，直接处理图片记录
 */

// Laravel Bootstrap
require_once __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProductImage;

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

    // 创建目标目录
    $dest_dir = dirname($dest);
    if (! is_dir($dest_dir)) {
        mkdir($dest_dir, 0755, true);
    }

    $success = imagewebp($image, $dest, $quality);
    imagedestroy($image);

    if ($success) {
        $original_size = filesize($source);
        $webp_size = filesize($dest);
        $compression_ratio = round(($original_size - $webp_size) / $original_size * 100, 1);

        return [
            'success' => true,
            'original_size' => $original_size,
            'webp_size' => $webp_size,
            'compression_ratio' => $compression_ratio,
        ];
    }

    return ['success' => false, 'error' => 'Failed to convert to WebP'];
}

log_message('=== 开始直接转换剩余图片为WebP格式 ===');

// 直接获取所有需要转换的图片记录
$remaining_images = ProductImage::where('image_path', 'not like', '%.webp')
    ->where('image_path', 'not like', '%placeholder%')
    ->where('image_path', '!=', '')
    ->whereNotNull('image_path')
    ->orderBy('id')
    ->get();

$total_images = $remaining_images->count();
log_message("需要转换的图片总数: {$total_images}");

if ($total_images == 0) {
    log_message('✅ 所有图片已完成转换!');
    exit(0);
}

$converted_count = 0;
$error_count = 0;
$batch_size = 50;

// 可能的存储路径
$storage_paths = [
    storage_path('app/public/'),
    storage_path('app/'),
    public_path('storage/'),
    public_path('build/storage/'),
    public_path(''),
];

foreach ($remaining_images as $index => $image) {
    $progress = $index + 1;
    log_message("🔄 处理图片 {$progress}/{$total_images}: {$image->image_path}");

    // 尝试找到图片文件
    $source_path = null;
    foreach ($storage_paths as $base_path) {
        $test_path = $base_path.$image->image_path;
        if (file_exists($test_path)) {
            $source_path = $test_path;
            break;
        }
    }

    if (! $source_path) {
        log_message('    ❌ 跳过: 文件不存在');
        $error_count++;

        continue;
    }

    // 生成WebP文件路径
    $path_info = pathinfo($image->image_path);
    $webp_relative_path = $path_info['dirname'].'/'.$path_info['filename'].'.webp';
    $webp_full_path = dirname($source_path).'/'.$path_info['filename'].'.webp';

    // 转换为WebP
    $result = convertToWebP($source_path, $webp_full_path);

    if ($result['success']) {
        // 更新数据库记录
        $image->update(['image_path' => $webp_relative_path]);

        log_message('    ✅ 转换成功!');
        log_message('       原始大小: '.round($result['original_size'] / 1024, 1).' KB');
        log_message('       WebP大小: '.round($result['webp_size'] / 1024, 1).' KB');
        log_message("       压缩率: {$result['compression_ratio']}%");
        log_message("       数据库已更新: {$webp_relative_path}");

        // 删除原始文件
        if (file_exists($source_path)) {
            unlink($source_path);
        }

        $converted_count++;
    } else {
        log_message("    ❌ 转换失败: {$result['error']}");
        $error_count++;
    }

    // 每50张图片报告一次进度
    if ($progress % $batch_size == 0) {
        log_message('');
        log_message('📊 进度报告:');
        log_message("已转换图片: {$converted_count}");
        log_message("错误数量: {$error_count}");
        log_message('剩余图片: '.($total_images - $progress));
        log_message('');
    }
}

log_message('');
log_message('🧹 清理Laravel缓存...');
// 清理缓存
try {
    \Artisan::call('cache:clear');
    \Artisan::call('view:clear');
    log_message('✅ 缓存清理完成');
} catch (Exception $e) {
    log_message('⚠️ 缓存清理失败: '.$e->getMessage());
}

log_message('');
log_message('=== 直接转换完成 ===');
log_message('📊 最终统计:');
log_message("总处理图片: {$total_images}");
log_message("成功转换: {$converted_count}");
log_message("错误数量: {$error_count}");
log_message('转换成功率: '.round(($converted_count / $total_images) * 100, 1).'%');
log_message('');

if ($error_count == 0) {
    log_message('🎉 所有图片转换任务成功完成!');
} else {
    log_message("⚠️ 转换完成，但有 {$error_count} 张图片转换失败");
}

log_message('现在可以安全地修改图片访问路径逻辑了');

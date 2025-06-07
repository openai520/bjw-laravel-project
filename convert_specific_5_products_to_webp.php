<?php
require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Facades\DB;

// 设置数据库连接
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'kalala_laravel',
    'username'  => 'root',
    'password'  => 'root',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// 指定要转换的产品ID
$target_product_ids = [694, 695, 696, 697, 698];

echo "=== 开始转换指定5个产品的图片为WebP格式 ===\n";
echo "目标产品ID: " . implode(', ', $target_product_ids) . "\n\n";

// 设置路径
$image_base_path = '/Users/lailai/Desktop/bjw-laravel-project/public/images/products/';
$log_file = 'webp_conversion_5_products_' . date('Y-m-d_H-i-s') . '.log';

// 转换函数
function convertToWebP($source_path, $dest_path, $quality = 92) {
    if (!file_exists($source_path)) {
        return ['success' => false, 'error' => 'Source file not found'];
    }
    
    $image_info = getimagesize($source_path);
    if (!$image_info) {
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
            return ['success' => false, 'error' => 'Unsupported image format: ' . $image_info['mime']];
    }
    
    if (!$image) {
        return ['success' => false, 'error' => 'Failed to create image resource'];
    }
    
    // 创建目标目录
    $dest_dir = dirname($dest_path);
    if (!is_dir($dest_dir)) {
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
            'compression_ratio' => $compression_ratio
        ];
    } else {
        return ['success' => false, 'error' => 'WebP conversion failed'];
    }
}

// 记录日志函数
function log_message($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    echo $log_entry;
}

$total_processed = 0;
$total_converted = 0;
$total_errors = 0;

foreach ($target_product_ids as $product_id) {
    log_message("处理产品ID: $product_id");
    
    try {
        // 获取产品信息
        $product = Capsule::table('products')->where('id', $product_id)->first();
        
        if (!$product) {
            log_message("  ❌ 产品不存在: ID $product_id");
            $total_errors++;
            continue;
        }
        
        log_message("  📱 产品名称: {$product->name}");
        
        // 获取产品的所有图片
        $images = Capsule::table('product_images')
            ->where('product_id', $product_id)
            ->get();
        
        if ($images->isEmpty()) {
            log_message("  ⚠️  没有找到产品图片");
            continue;
        }
        
        log_message("  🖼️  找到 " . count($images) . " 张图片");
        
        foreach ($images as $image) {
            $total_processed++;
            $image_path = $image->image_path;
            
            // 检查是否已经是WebP格式
            if (pathinfo($image_path, PATHINFO_EXTENSION) === 'webp') {
                log_message("    ✅ 已是WebP格式: $image_path");
                continue;
            }
            
            $original_path = $image_base_path . $image_path;
            $webp_path = $image_base_path . pathinfo($image_path, PATHINFO_DIRNAME) . '/' . 
                        pathinfo($image_path, PATHINFO_FILENAME) . '.webp';
            
            log_message("    🔄 转换: $image_path");
            
            $result = convertToWebP($original_path, $webp_path);
            
            if ($result['success']) {
                // 更新数据库中的图片路径
                $new_image_path = str_replace($image_path, pathinfo($image_path, PATHINFO_DIRNAME) . '/' . 
                                             pathinfo($image_path, PATHINFO_FILENAME) . '.webp', $image_path);
                
                Capsule::table('product_images')
                    ->where('id', $image->id)
                    ->update([
                        'image_path' => $new_image_path,
                        'updated_at' => now()
                    ]);
                
                log_message("    ✅ 转换成功!");
                log_message("       原始大小: " . round($result['original_size']/1024, 1) . " KB");
                log_message("       WebP大小: " . round($result['webp_size']/1024, 1) . " KB");
                log_message("       压缩率: {$result['compression_ratio']}%");
                log_message("       数据库已更新: $new_image_path");
                
                // 删除原始文件
                if (file_exists($original_path)) {
                    unlink($original_path);
                    log_message("       🗑️  删除原始文件: $image_path");
                }
                
                $total_converted++;
            } else {
                log_message("    ❌ 转换失败: " . $result['error']);
                $total_errors++;
            }
        }
        
    } catch (Exception $e) {
        log_message("  ❌ 处理产品 $product_id 时出错: " . $e->getMessage());
        $total_errors++;
    }
    
    log_message(""); // 空行分隔
}

log_message("=== 转换完成 ===");
log_message("总处理图片: $total_processed");
log_message("成功转换: $total_converted");
log_message("错误数量: $total_errors");
log_message("日志文件: $log_file");

// 显示最终结果
echo "\n🎉 5个产品的WebP转换任务完成!\n";
echo "📊 处理统计:\n";
echo "   - 总处理图片: $total_processed\n";
echo "   - 成功转换: $total_converted\n";
echo "   - 错误数量: $total_errors\n";
echo "📝 详细日志: $log_file\n";

function now() {
    return date('Y-m-d H:i:s');
}
?> 
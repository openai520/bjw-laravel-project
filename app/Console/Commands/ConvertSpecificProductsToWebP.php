<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Console\Command;

class ConvertSpecificProductsToWebP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:convert-specific-webp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '转换指定5个产品的图片为WebP格式';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 指定要转换的产品ID
        $target_product_ids = [694, 695, 696, 697, 698];

        $this->info('=== 开始转换指定5个产品的图片为WebP格式 ===');
        $this->info('目标产品ID: '.implode(', ', $target_product_ids));
        $this->newLine();

        $image_base_path = public_path('images/products/');
        $total_processed = 0;
        $total_converted = 0;
        $total_errors = 0;

        foreach ($target_product_ids as $product_id) {
            $this->info("处理产品ID: {$product_id}");

            try {
                // 获取产品信息
                $product = Product::find($product_id);

                if (! $product) {
                    $this->error("  ❌ 产品不存在: ID {$product_id}");
                    $total_errors++;

                    continue;
                }

                $this->info("  📱 产品名称: {$product->name}");

                // 获取产品的所有图片
                $images = ProductImage::where('product_id', $product_id)->get();

                if ($images->isEmpty()) {
                    $this->warn('  ⚠️  没有找到产品图片');

                    continue;
                }

                $this->info('  🖼️  找到 '.count($images).' 张图片');

                foreach ($images as $image) {
                    $total_processed++;
                    $image_path = $image->image_path;

                    // 检查是否已经是WebP格式
                    if (pathinfo($image_path, PATHINFO_EXTENSION) === 'webp') {
                        $this->info("    ✅ 已是WebP格式: {$image_path}");

                        continue;
                    }

                    $original_path = $image_base_path.$image_path;
                    $webp_path = $image_base_path.pathinfo($image_path, PATHINFO_DIRNAME).'/'.
                                pathinfo($image_path, PATHINFO_FILENAME).'.webp';

                    $this->info("    🔄 转换: {$image_path}");

                    $result = $this->convertToWebP($original_path, $webp_path);

                    if ($result['success']) {
                        // 更新数据库中的图片路径
                        $new_image_path = pathinfo($image_path, PATHINFO_DIRNAME).'/'.
                                         pathinfo($image_path, PATHINFO_FILENAME).'.webp';

                        $image->update([
                            'image_path' => $new_image_path,
                        ]);

                        $this->info('    ✅ 转换成功!');
                        $this->info('       原始大小: '.round($result['original_size'] / 1024, 1).' KB');
                        $this->info('       WebP大小: '.round($result['webp_size'] / 1024, 1).' KB');
                        $this->info("       压缩率: {$result['compression_ratio']}%");
                        $this->info("       数据库已更新: {$new_image_path}");

                        // 删除原始文件
                        if (file_exists($original_path)) {
                            unlink($original_path);
                            $this->info("       🗑️  删除原始文件: {$image_path}");
                        }

                        $total_converted++;
                    } else {
                        $this->error('    ❌ 转换失败: '.$result['error']);
                        $total_errors++;
                    }
                }

            } catch (\Exception $e) {
                $this->error("  ❌ 处理产品 {$product_id} 时出错: ".$e->getMessage());
                $total_errors++;
            }

            $this->newLine(); // 空行分隔
        }

        $this->info('=== 转换完成 ===');
        $this->info("总处理图片: {$total_processed}");
        $this->info("成功转换: {$total_converted}");
        $this->info("错误数量: {$total_errors}");

        // 显示最终结果
        $this->newLine();
        $this->info('🎉 5个产品的WebP转换任务完成!');
        $this->info('📊 处理统计:');
        $this->info("   - 总处理图片: {$total_processed}");
        $this->info("   - 成功转换: {$total_converted}");
        $this->info("   - 错误数量: {$total_errors}");

        return Command::SUCCESS;
    }

    /**
     * 转换图片为WebP格式
     */
    private function convertToWebP($source_path, $dest_path, $quality = 92)
    {
        if (! file_exists($source_path)) {
            return ['success' => false, 'error' => 'Source file not found'];
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
}

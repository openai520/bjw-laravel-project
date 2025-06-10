<?php

namespace App\Console\Commands;

use App\Models\ProductImage;
use App\Services\ImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;

class GenerateThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-thumbnails {--format=webp : 输出格式 (webp, original)} {--force : 强制重新生成所有缩略图}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '为所有没有缩略图的产品图片生成缩略图，默认使用WebP格式';

    /**
     * 图片服务
     */
    protected $imageService;

    /**
     * 图片管理器
     */
    protected $imageManager;

    /**
     * 构造函数
     */
    public function __construct(ImageService $imageService)
    {
        parent::__construct();
        $this->imageService = $imageService;
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');
        
        if ($force) {
            $images = ProductImage::all();
            $this->info("强制模式：将重新生成所有 {$images->count()} 张图片的缩略图");
        } else {
            $images = ProductImage::whereNull('thumbnail_path')->get();
            $this->info("找到 {$images->count()} 张需要生成缩略图的产品图片");
        }
        
        $totalCount = $images->count();
        
        $format = $this->option('format') ?: 'webp';
        $this->info("使用格式: {$format}");
        
        if ($totalCount === 0) {
            $this->info("所有图片都已有缩略图，无需操作");
            return 0;
        }
        
        $bar = $this->output->createProgressBar($totalCount);
        $bar->start();
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($images as $image) {
            try {
                // 检查源图片是否存在
                if (!Storage::disk('public')->exists($image->image_path)) {
                    $this->error("源图片不存在: {$image->image_path}");
                    $errorCount++;
                    continue;
                }
                
                // 如果是强制模式，并且已有缩略图，先删除旧的缩略图
                if ($force && $image->thumbnail_path && Storage::disk('public')->exists($image->thumbnail_path)) {
                    Storage::disk('public')->delete($image->thumbnail_path);
                    $this->line("删除旧缩略图: {$image->thumbnail_path}");
                }
                
                // 获取原始图片扩展名
                $originalExtension = strtolower(pathinfo($image->image_path, PATHINFO_EXTENSION));
                
                // 决定输出格式和质量
                $outputFormat = $format;
                $quality = $format === 'webp' ? ImageService::WEBP_QUALITY : ImageService::JPEG_QUALITY;
                
                // 保留原始格式选项
                if ($format === 'original') {
                    $outputFormat = in_array($originalExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp']) 
                        ? ($originalExtension === 'jpg' ? 'jpeg' : $originalExtension)
                        : 'jpeg';
                    $quality = $outputFormat === 'webp' ? ImageService::WEBP_QUALITY : ImageService::JPEG_QUALITY;
                }
                
                // 创建缩略图文件名
                $thumbnailFilename = Str::uuid() . '_thumb.' . $outputFormat;
                $path = dirname($image->image_path);
                $thumbnailPath = $path . '/' . $thumbnailFilename;
                
                // 创建缩略图 - 使用更好的处理方式
                $img = $this->imageManager->read(Storage::disk('public')->path($image->image_path));
                
                // 先调整到合适的大小保持比例
                $img = $img->resize(ImageService::THUMBNAIL_WIDTH, ImageService::THUMBNAIL_HEIGHT, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                
                // 对于更好的品质，使用更高的质量设置
                $thumbnailQuality = $quality + 3; // 比主图质量略高一些
                if ($thumbnailQuality > 100) {
                    $thumbnailQuality = 100; // 确保不超过最大值
                }
                
                $encodedImage = $img->encodeByExtension($outputFormat, $thumbnailQuality);
                
                // 保存缩略图
                Storage::disk('public')->put($thumbnailPath, $encodedImage);
                
                // 更新数据库记录
                $image->thumbnail_path = $thumbnailPath;
                $image->save();
                
                $successCount++;
            } catch (\Exception $e) {
                Log::error('缩略图生成失败', [
                    'image_id' => $image->id,
                    'image_path' => $image->image_path,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                $errorCount++;
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info("缩略图生成完成:");
        $this->info("- 成功: {$successCount}");
        $this->info("- 失败: {$errorCount}");
        
        return 0;
    }
} 
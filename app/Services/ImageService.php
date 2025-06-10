<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;

class ImageService
{
    /**
     * 允许的图片最大尺寸（像素）
     */
    const MAX_WIDTH = 1600;
    const MAX_HEIGHT = 1600;
    
    /**
     * 缩略图尺寸（像素）
     * 增加尺寸以提高清晰度
     */
    const THUMBNAIL_WIDTH = 500;
    const THUMBNAIL_HEIGHT = 500;
    
    /**
     * 图片质量（0-100）
     * 优化压缩以提升性能和节省流量
     */
    const JPEG_QUALITY = 75;
    const WEBP_QUALITY = 68;
    
    /**
     * 图片管理器
     */
    protected $imageManager;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }
    
    /**
     * 保存上传的图片并进行优化
     *
     * @param UploadedFile $file 上传的文件
     * @param string $path 存储路径
     * @param bool $createThumbnail 是否创建缩略图
     * @param bool $resizeIfNeeded 是否在超过最大尺寸时调整图片尺寸
     * @param string $format 输出图片格式，默认为webp
     * @return array 返回保存的文件路径信息 ['main' => '主图路径', 'thumbnail' => '缩略图路径']
     */
    public function saveOptimizedImage(
        UploadedFile $file, 
        string $path = 'products', 
        bool $createThumbnail = true,
        bool $resizeIfNeeded = true,
        string $format = 'webp'
    ): array
    {
        try {
            // 获取原始扩展名
            $originalExtension = strtolower($file->getClientOriginalExtension());
            
            // 决定输出格式和质量
            $outputFormat = $format;
            $quality = $format === 'webp' ? self::WEBP_QUALITY : self::JPEG_QUALITY;
            
            // 保留原始格式选项
            $keepOriginalFormat = false;
            if ($format === 'original') {
                $keepOriginalFormat = true;
                $outputFormat = in_array($originalExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp']) 
                    ? ($originalExtension === 'jpg' ? 'jpeg' : $originalExtension)
                    : 'jpeg';
                $quality = $outputFormat === 'webp' ? self::WEBP_QUALITY : self::JPEG_QUALITY;
            }
            
            // 强制使用UUID文件名，不再保留原始文件名
            $filename = Str::uuid() . '.' . $outputFormat;
            $fullPath = $path . '/' . $filename;
            
            // 使用Intervention Image加载图片
            $img = $this->imageManager->read($file->getPathname());
            
            // 调整尺寸（如果需要且图片超过最大尺寸）
            if ($resizeIfNeeded && ($img->width() > self::MAX_WIDTH || $img->height() > self::MAX_HEIGHT)) {
                $img = $img->resize(self::MAX_WIDTH, self::MAX_HEIGHT, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                
                Log::debug('图片已调整尺寸', [
                    'original_width' => $img->width(),
                    'original_height' => $img->height(),
                    'new_width' => min($img->width(), self::MAX_WIDTH),
                    'new_height' => min($img->height(), self::MAX_HEIGHT)
                ]);
            } else {
                Log::debug('图片保持原始尺寸', [
                    'width' => $img->width(),
                    'height' => $img->height()
                ]);
            }
            
            // 编码并保存图片
            $encodedImage = $img->encodeByExtension($outputFormat);
            Storage::disk('public')->put($fullPath, $encodedImage);
            
            Log::debug('保存了优化的图片', [
                'format' => $outputFormat,
                'quality' => $quality,
                'path' => $fullPath
            ]);
            
            $result = ['main' => $fullPath];
            
            // 如果需要创建缩略图
            if ($createThumbnail) {
                $thumbnailFilename = Str::uuid() . '_thumb.' . $outputFormat;
                $thumbnailPath = $path . '/' . $thumbnailFilename;
                
                // 创建缩略图 - 改为使用fit而不是cover，以保持图像比例
                $thumbnail = $this->imageManager->read($file->getPathname());
                
                // 先调整到合适的大小保持比例
                $thumbnail = $thumbnail->resize(self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                
                // 对于更好的品质，使用更高的质量设置
                // $thumbnailQuality = $quality + 3; 
                // if ($thumbnailQuality > 100) {
                //     $thumbnailQuality = 100; 
                // }
                
                // $encodedThumbnail = $thumbnail->encodeByExtension($outputFormat, $thumbnailQuality);
                $encodedThumbnail = $thumbnail->encodeByExtension($outputFormat);
                
                Storage::disk('public')->put($thumbnailPath, $encodedThumbnail);
                $result['thumbnail'] = $thumbnailPath;
                
                Log::debug('创建了缩略图', [
                    'format' => $outputFormat,
                    'quality' => $quality,
                    'path' => $thumbnailPath,
                    'width' => $thumbnail->width(),
                    'height' => $thumbnail->height()
                ]);
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error('图片优化失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // 如果图片处理失败，使用原始方法保存
            $fallbackPath = $file->store($path, 'public');
            return ['main' => $fallbackPath];
        }
    }
    
    /**
     * 仅压缩图片质量，不改变尺寸
     *
     * @param UploadedFile $file 上传的文件
     * @param string $path 存储路径
     * @param bool $createThumbnail 是否创建缩略图
     * @param string $format 输出图片格式，默认为original(保持原格式)
     * @return array 返回保存的文件路径信息 ['main' => '主图路径', 'thumbnail' => '缩略图路径']
     */
    public function saveCompressedImage(
        UploadedFile $file, 
        string $path = 'products', 
        bool $createThumbnail = true,
        string $format = 'original'
    ): array
    {
        return $this->saveOptimizedImage($file, $path, $createThumbnail, false, $format);
    }
    
    /**
     * 重新优化现有图片
     *
     * @param string $existingPath 现有图片路径
     * @param string $path 存储路径
     * @param bool $resizeIfNeeded 是否在超过最大尺寸时调整图片尺寸
     * @param string $format 输出图片格式，默认为webp
     * @return string 返回新的图片路径
     */
    public function reoptimizeExistingImage(
        string $existingPath, 
        string $path = 'products',
        bool $resizeIfNeeded = true,
        string $format = 'webp'
    ): string
    {
        try {
            // 检查图片是否存在
            if (!Storage::disk('public')->exists($existingPath)) {
                Log::warning('图片不存在，无法重新优化', ['path' => $existingPath]);
                return $existingPath;
            }
            
            // 获取原始扩展名
            $originalExtension = strtolower(pathinfo($existingPath, PATHINFO_EXTENSION));
            
            // 决定输出格式和质量
            $outputFormat = $format;
            $quality = $format === 'webp' ? self::WEBP_QUALITY : self::JPEG_QUALITY;
            
            // 保留原始格式选项
            if ($format === 'original') {
                $outputFormat = in_array($originalExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp']) 
                    ? ($originalExtension === 'jpg' ? 'jpeg' : $originalExtension)
                    : 'jpeg';
                $quality = $outputFormat === 'webp' ? self::WEBP_QUALITY : self::JPEG_QUALITY;
            }
            
            // 生成新的文件名
            $filename = Str::uuid() . '.' . $outputFormat;
            $newPath = $path . '/' . $filename;
            
            // 使用Intervention Image加载图片
            $img = $this->imageManager->read(Storage::disk('public')->path($existingPath));
            
            // 调整尺寸（如果需要且图片超过最大尺寸）
            if ($resizeIfNeeded && ($img->width() > self::MAX_WIDTH || $img->height() > self::MAX_HEIGHT)) {
                $img = $img->resize(self::MAX_WIDTH, self::MAX_HEIGHT, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            
            // 编码并保存图片
            $encodedImage = $img->encodeByExtension($outputFormat);
            Storage::disk('public')->put($newPath, $encodedImage);
            
            return $newPath;
        } catch (\Exception $e) {
            Log::error('重新优化图片失败', [
                'original_path' => $existingPath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // 如果处理失败，返回原始路径
            return $existingPath;
        }
    }
} 
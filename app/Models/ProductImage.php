<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * 产品图片模型
 * 根据 v6 文档 Section 5.1 - 产品图片表
 */
class ProductImage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'image_path',
        'thumbnail_path',
        'is_main',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_main' => 'boolean',
    ];

    /**
     * 获取图片所属的产品
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * 获取主图的完整 URL。
     */
    public function getMainImageUrlAttribute(): string
    {
        if (!$this->image_path) {
            return asset('img/placeholder.svg');
        }
        // 清理路径并生成 URL
        return $this->generateStorageUrl($this->image_path);
    }

    /**
     * 获取缩略图的完整 URL，如果不存在则回退到主图 URL。
     */
    public function getThumbnailUrlAttribute(): string
    {
        $thumbPath = $this->thumbnail_path;
        
        if ($thumbPath) {
            return $this->generateStorageUrl($thumbPath);
        }
        
        // 如果没有缩略图路径，尝试使用主图路径
        if ($this->image_path) {
            return $this->generateStorageUrl($this->image_path);
        }

        // 如果两者都没有，返回占位符
        return asset('img/placeholder.svg');
    }

    /**
     * 辅助方法：清理路径并生成 Storage URL。
     */
    protected function generateStorageUrl(string $path): string
    {
        $cleanedPath = ltrim($path, '/');
        $cleanedPath = preg_replace('#^(public/|storage/)#', '', $cleanedPath);
        // 确保以 'products/' 开头，如果它还不是
        if (!str_starts_with($cleanedPath, 'products/') && !str_contains($cleanedPath, '/products/')) {
            $cleanedPath = 'products/' . $cleanedPath;
        } elseif (str_contains($cleanedPath, '/products/')) {
            // 如果是 something/products/image.jpg, 提取 products/image.jpg
            $cleanedPath = substr($cleanedPath, strpos($cleanedPath, 'products/'));
        }
         // 再次清理以防万一
        $cleanedPath = preg_replace('#^(public/|storage/)#', '', $cleanedPath);
        
        // 使用 Storage::url() 生成相对路径
        return Storage::disk('public')->url($cleanedPath);
    }

    /**
     * 更新所有图片路径，添加 products/ 前缀
     */
    public static function updateAllImagePaths()
    {
        $images = self::all();
        foreach ($images as $image) {
            if (!str_starts_with($image->image_path, 'products/')) {
                $image->image_path = 'products/' . $image->image_path;
                $image->save();
            }
        }
    }
} 
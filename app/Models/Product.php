<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 产品模型
 * 根据 v6 文档 Section 5.1 - 产品表
 */
class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'min_order_quantity',
        'status',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'price' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    protected $appends = ['main_image_url', 'thumbnail_url'];

    /**
     * 获取产品所属的分类
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * 获取产品的所有图片
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * 获取产品的主图 (HasOne relationship)
     */
    public function mainImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_main', true);
    }

    /**
     * 获取产品的主图URL
     */
    public function getMainImageUrlAttribute()
    {
        try {
            // 优先使用预加载的 mainImage 关系，避免重复查询
            $mainImg = $this->relationLoaded('mainImage') ? $this->mainImage : $this->mainImage()->first();

            if ($mainImg && $mainImg->image_path) {
                // 使用 asset() 确保生成完整的URL
                $cleanPath = ltrim($mainImg->image_path, '/');

                return asset('storage/'.$cleanPath);
            }

            // 如果没有主图，尝试获取第一个图片
            // 优先使用预加载的 images 关系
            $firstImg = null;
            if ($this->relationLoaded('images')) {
                $firstImg = $this->images->first(); // images 应该是 collection
            } else {
                $firstImg = $this->images()->first(); // Query if not loaded
            }

            if ($firstImg && $firstImg->image_path) {
                // 同样使用 asset() 生成完整的URL
                $cleanPath = ltrim($firstImg->image_path, '/');

                return asset('storage/'.$cleanPath);
            }

            // 没有找到任何图片，返回默认SVG
            return asset('img/placeholder.svg');

        } catch (\Exception $e) {
            \Log::error("Error in getMainImageUrlAttribute for product {$this->id}: ".$e->getMessage(), ['exception' => $e]);

            return asset('img/placeholder.svg'); // Fallback to default SVG on any error
        }
    }

    /**
     * 获取产品的缩略图URL
     */
    public function getThumbnailUrlAttribute()
    {
        try {
            // 优先使用预加载的 mainImage 关系，获取缩略图
            $mainImg = $this->relationLoaded('mainImage') ? $this->mainImage : $this->mainImage()->first();

            if ($mainImg && $mainImg->thumbnail_path) {
                $cleanPath = ltrim($mainImg->thumbnail_path, '/');

                return asset('storage/'.$cleanPath);
            }

            // 如果主图没有缩略图，尝试获取第一个图片的缩略图
            $firstImg = null;
            if ($this->relationLoaded('images')) {
                $firstImg = $this->images->first();
            } else {
                $firstImg = $this->images()->first();
            }

            if ($firstImg && $firstImg->thumbnail_path) {
                $cleanPath = ltrim($firstImg->thumbnail_path, '/');

                return asset('storage/'.$cleanPath);
            }

            // 如果没有缩略图，回退到主图
            return $this->main_image_url;

        } catch (\Exception $e) {
            \Log::error("Error in getThumbnailUrlAttribute for product {$this->id}: ".$e->getMessage());

            return asset('img/placeholder.svg');
        }
    }

    /**
     * 获取默认的SVG图片
     */
    protected function getDefaultImageSvg()
    {
        return 'data:image/svg+xml;base64,'.base64_encode('
            <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
                <rect width="100" height="100" fill="#f3f4f6"/>
                <text x="50" y="50" font-family="Arial" font-size="12" fill="#9ca3af" text-anchor="middle" dy=".3em">暂无图片</text>
            </svg>
        ');
    }

    /**
     * 获取产品的询价项
     */
    public function inquiryItems()
    {
        return $this->hasMany(InquiryItem::class);
    }

    /**
     * 获取产品的访问记录
     */
    public function views()
    {
        return $this->hasMany(ProductView::class);
    }

    /**
     * 获取产品总访问次数
     */
    public function getViewCountAttribute()
    {
        return $this->views()->count();
    }

    /**
     * 获取产品今日访问次数
     */
    public function getTodayViewCountAttribute()
    {
        return $this->views()->whereDate('viewed_at', today())->count();
    }

    /**
     * 临时方法，用于处理视图中的 getTranslation 调用
     * TODO: 后续需要实现真正的多语言支持
     */
    public function getTranslation(string $field, ?string $locale = null, ?string $fallbackLocale = null)
    {
        // 简单地返回字段的原始值，忽略 locale
        // 假设 name 和 description 字段存储的是当前期望的语言
        if (in_array($field, ['name', 'description'])) {
            return $this->{$field};
        }

        // 对于其他字段，可以返回 null 或抛出异常
        // 这里为了简单起见，我们返回 null
        return null;
    }
}

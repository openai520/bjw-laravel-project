<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 产品分类模型
 * 根据 v6 文档 Section 5.1 - 产品分类表
 */
class Category extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name_en',
        'name_fr',
        'slug',
        'sort_order',
        'show_on_home',
        'display_order'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'deleted_at' => 'datetime',
        'sort_order' => 'integer',
        'show_on_home' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * 获取该分类下的所有产品
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * 获取该分类设置的首页推荐产品
     */
    public function homeFeaturedProducts()
    {
        return $this->hasMany(HomeCategoryFeaturedProduct::class)->orderBy('display_order');
    }

    /**
     * 临时方法，用于处理视图中的 getTranslation 调用
     * TODO: 后续需要实现真正的多语言支持
     */
    public function getTranslation(string $field, ?string $locale = null, ?string $fallbackLocale = null)
    {
        if ($field === 'name') {
            $localeField = 'name_' . strtolower($locale ?: 'en'); // 默认为 en
            if (array_key_exists($localeField, $this->attributes)) {
                return $this->{$localeField};
            } elseif (array_key_exists('name_en', $this->attributes)) {
                // 如果特定语言的字段不存在，回退到英文
                return $this->name_en;
            }
            // 如果连 name_en 都没有，可以返回一个默认值或 null
            return $this->slug; // 或者返回 slug 作为备用
        }

        if ($field === 'slug') {
            return $this->slug;
        }

        // 对于其他字段，可以返回 null 或抛出异常
        return null;
    }
}
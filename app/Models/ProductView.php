<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductView extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'ip_address',
        'user_agent',
        'referer',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    /**
     * 获取被访问的产品
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * 获取产品的访问次数
     */
    public static function getProductViewCount($productId)
    {
        return static::where('product_id', $productId)->count();
    }

    /**
     * 获取产品今日访问次数
     */
    public static function getTodayViewCount($productId)
    {
        return static::where('product_id', $productId)
            ->whereDate('viewed_at', today())
            ->count();
    }

    /**
     * 获取热门产品排行
     */
    public static function getPopularProducts($limit = 10, $days = 30)
    {
        return static::select('product_id', \DB::raw('COUNT(*) as view_count'))
            ->where('viewed_at', '>=', now()->subDays($days))
            ->groupBy('product_id')
            ->orderByDesc('view_count')
            ->limit($limit)
            ->with('product')
            ->get();
    }

    /**
     * 检查是否为重复访问（同一IP在指定时间内访问同一产品）
     */
    public static function isDuplicateView($productId, $ipAddress, $minutes = 60)
    {
        return static::where('product_id', $productId)
            ->where('ip_address', $ipAddress)
            ->where('viewed_at', '>=', now()->subMinutes($minutes))
            ->exists();
    }
}

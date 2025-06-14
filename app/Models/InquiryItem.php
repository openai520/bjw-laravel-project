<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 询价单商品项模型
 * 根据 v6 文档 Section 5.1 - 询价单商品详情表
 */
class InquiryItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'inquiry_id',
        'product_id',
        'quantity',
        'price',
        'remark',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * 获取所属的询价单
     */
    public function inquiry()
    {
        return $this->belongsTo(Inquiry::class);
    }

    /**
     * 获取关联的产品
     * 注意：由于产品可能被软删除，我们使用 withTrashed 确保能加载被删除的产品
     */
    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 询价单模型
 * 根据 v6 文档 Section 5.1 - 询价单主表
 */
class Inquiry extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'inquiry_no',
        'name',
        'email',
        'country',
        'phone',
        'whatsapp',
        'wechat',
        'ip_address',
        'message',
        'total_quantity',
        'total_amount',
        'status',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'status' => 'string',
    ];

    /**
     * 模型的默认属性值
     */
    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * 获取询价单的所有商品项
     */
    public function items()
    {
        return $this->hasMany(InquiryItem::class);
    }
}

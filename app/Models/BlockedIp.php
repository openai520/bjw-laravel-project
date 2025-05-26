<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedIp extends Model
{
    use HasFactory;

    /**
     * 指示模型不使用时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 可以批量赋值的属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ip_address',
        'blocked_at',
    ];

    /**
     * 应该转换的属性
     *
     * @var array<string, string>
     */
    protected $casts = [
        'blocked_at' => 'datetime',
    ];
}

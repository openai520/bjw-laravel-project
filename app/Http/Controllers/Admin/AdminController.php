<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * 创建一个新的控制器实例
     *
     * @return void
     */
    public function __construct()
    {
        // 应用管理员中间件
        $this->middleware('auth');
        $this->middleware('admin');
        
        // 确保不记录IP地址
        // 注意：这是双重保险，LogVisitor中间件已经跳过了admin路由
    }
    
    // ... existing code ...
}
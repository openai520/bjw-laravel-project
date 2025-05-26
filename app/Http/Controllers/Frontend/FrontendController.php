<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    /**
     * 创建一个新的控制器实例
     *
     * @return void
     */
    public function __construct()
    {
        // 应用前台中间件
        // LogVisitor中间件已在全局或路由组中应用
        // 现在已优化为每个IP每天只记录一次
    }
    
    // ... existing code ...
}
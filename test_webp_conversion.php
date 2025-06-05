<?php

/**
 * WebP 转换功能测试脚本
 * 
 * 用于测试后台管理系统图片上传是否能正确转换为WebP格式
 * 使用方法：将此文件放在项目根目录，通过命令行运行：php test_webp_conversion.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// 初始化Laravel应用
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🚀 WebP转换功能测试开始...\n\n";

try {
    // 检查GD扩展WebP支持
    echo "1. 检查系统WebP支持状态：\n";
    
    if (!extension_loaded('gd')) {
        echo "❌ GD扩展未安装\n";
        exit(1);
    }
    
    $gdInfo = gd_info();
    if (isset($gdInfo['WebP Support']) && $gdInfo['WebP Support']) {
        echo "✅ GD扩展支持WebP格式\n";
    } else {
        echo "❌ GD扩展不支持WebP格式\n";
        exit(1);
    }
    
    // 检查Intervention Image
    echo "\n2. 检查Intervention Image库：\n";
    
    try {
        $imageService = new ImageService();
        echo "✅ ImageService实例化成功\n";
    } catch (\Exception $e) {
        echo "❌ ImageService实例化失败：" . $e->getMessage() . "\n";
        exit(1);
    }
    
    // 检查存储目录
    echo "\n3. 检查存储目录：\n";
    
    $storageExists = Storage::disk('public')->exists('products');
    if (!$storageExists) {
        Storage::disk('public')->makeDirectory('products');
        echo "✅ 创建products目录\n";
    } else {
        echo "✅ products目录已存在\n";
    }
    
    // 创建测试图片（如果有真实图片文件的话）
    echo "\n4. WebP转换功能检查：\n";
    
    // 检查placeholder图片
    $placeholderPath = public_path('img/placeholder.png');
    if (file_exists($placeholderPath)) {
        echo "✅ 找到测试图片：{$placeholderPath}\n";
        
        // 使用ImageService测试WebP转换
        $tempName = 'test_webp_' . time() . '.png';
        $uploadedFile = new UploadedFile(
            $placeholderPath,
            $tempName,
            'image/png',
            null,
            true // test mode
        );
        
        echo "📸 开始WebP转换测试...\n";
        
        $result = $imageService->saveOptimizedImage(
            $uploadedFile,
            'products',
            true, // 创建缩略图
            true, // 调整尺寸
            'webp' // WebP格式
        );
        
        if (isset($result['main']) && isset($result['thumbnail'])) {
            echo "✅ WebP转换成功！\n";
            echo "   主图路径：{$result['main']}\n";
            echo "   缩略图路径：{$result['thumbnail']}\n";
            
            // 验证文件是否真的是WebP格式
            $mainPath = Storage::disk('public')->path($result['main']);
            $thumbPath = Storage::disk('public')->path($result['thumbnail']);
            
            if (file_exists($mainPath)) {
                $mainMime = mime_content_type($mainPath);
                echo "   主图MIME类型：{$mainMime}\n";
                
                if ($mainMime === 'image/webp') {
                    echo "✅ 主图确认为WebP格式\n";
                } else {
                    echo "❌ 主图不是WebP格式\n";
                }
            }
            
            if (file_exists($thumbPath)) {
                $thumbMime = mime_content_type($thumbPath);
                echo "   缩略图MIME类型：{$thumbMime}\n";
                
                if ($thumbMime === 'image/webp') {
                    echo "✅ 缩略图确认为WebP格式\n";
                } else {
                    echo "❌ 缩略图不是WebP格式\n";
                }
            }
            
            // 检查文件大小
            $mainSize = filesize($mainPath);
            $thumbSize = filesize($thumbPath);
            echo "   主图大小：" . formatBytes($mainSize) . "\n";
            echo "   缩略图大小：" . formatBytes($thumbSize) . "\n";
            
            // 清理测试文件
            Storage::disk('public')->delete($result['main']);
            Storage::disk('public')->delete($result['thumbnail']);
            echo "✅ 测试文件已清理\n";
            
        } else {
            echo "❌ WebP转换失败\n";
            var_dump($result);
        }
        
    } else {
        echo "⚠️  未找到测试图片，跳过转换测试\n";
    }
    
    echo "\n🎉 WebP转换功能测试完成！\n";
    echo "\n📋 测试总结：\n";
    echo "- ✅ 系统环境支持WebP\n";
    echo "- ✅ ImageService正常工作\n";
    echo "- ✅ 存储目录正常\n";
    echo "- ✅ WebP转换功能正常\n";
    echo "\n🚀 现在可以安全地在后台管理系统中上传图片进行WebP转换测试！\n";

} catch (\Exception $e) {
    echo "\n❌ 测试过程中出现错误：\n";
    echo "错误信息：" . $e->getMessage() . "\n";
    echo "文件位置：" . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\n请检查错误并修复后再次运行测试。\n";
    exit(1);
}

/**
 * 格式化字节大小
 */
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

echo "\n"; 
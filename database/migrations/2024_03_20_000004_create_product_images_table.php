<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 根据 v6 文档 Section 5.1 数据库模式
return new class extends Migration
{
    /**
     * Run the migrations.
     * 根据 v6 文档 Section 5.1 - 产品图片表
     */
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade')->comment('所属产品ID');
            $table->string('image_path', 255)->comment('图片路径');
            $table->boolean('is_main')->default(false)->comment('是否主图');
            $table->integer('sort_order')->default(0)->comment('排序值');
            $table->timestamps();

            $table->index('is_main');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
}; 
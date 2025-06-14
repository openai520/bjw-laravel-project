<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 根据 v6 文档 Section 5.1 数据库模式
return new class extends Migration
{
    /**
     * Run the migrations.
     * 根据 v6 文档 Section 5.1 - 询价单商品详情表
     */
    public function up(): void
    {
        Schema::create('inquiry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inquiry_id')->constrained()->onDelete('cascade')->comment('所属询价单ID');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null')->comment('产品ID');
            $table->integer('quantity')->comment('询价数量');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();

            $table->index(['inquiry_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiry_items');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 根据 v6 文档 Section 5.1 数据库模式
return new class extends Migration
{
    /**
     * Run the migrations.
     * 根据 v6 文档 Section 5.1 - 产品表
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade')->comment('所属分类ID');
            $table->string('name', 100)->comment('产品名称');
            $table->text('description_en')->comment('英文产品描述');
            $table->text('description_fr')->comment('法文产品描述');
            $table->decimal('price', 10, 2)->comment('产品价格');
            $table->integer('min_order_quantity')->default(1)->comment('最小订购数量');
            $table->enum('status', ['draft', 'published'])->default('draft')->comment('产品状态');
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

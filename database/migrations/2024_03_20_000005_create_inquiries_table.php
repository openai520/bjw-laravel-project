<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 根据 v6 文档 Section 5.1 数据库模式
return new class extends Migration
{
    /**
     * Run the migrations.
     * 根据 v6 文档 Section 5.1 - 询价单主表
     */
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('inquiry_no', 20)->unique()->comment('询价单号');
            $table->string('customer_name', 100)->comment('客户姓名');
            $table->string('customer_email', 100)->comment('客户邮箱');
            $table->string('customer_phone', 20)->nullable()->comment('客户电话');
            $table->string('customer_company', 100)->nullable()->comment('客户公司');
            $table->text('customer_message')->nullable()->comment('客户留言');
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled'])->default('pending')->comment('询价单状态');
            $table->timestamps();

            $table->index('inquiry_no');
            $table->index('customer_email');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};

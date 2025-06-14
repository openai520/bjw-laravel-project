<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 修复迁移不一致问题：
     * 1. 确保country列存在
     * 2. 确保email字段存在（如果customer_email存在则重命名，否则新建）
     */
    public function up(): void
    {
        // 先检查表中是否存在特定的列
        $hasCountry = Schema::hasColumn('inquiries', 'country');
        $hasEmail = Schema::hasColumn('inquiries', 'email');
        $hasCustomerEmail = Schema::hasColumn('inquiries', 'customer_email');

        // 第一步：添加缺失的列
        Schema::table('inquiries', function (Blueprint $table) use ($hasCountry, $hasEmail) {
            // 如果不存在country列，则添加
            if (! $hasCountry) {
                $table->string('country', 100)->nullable()->after('name')->comment('客户国家/地区');
            }

            // 如果不存在email列，添加它
            if (! $hasEmail) {
                $table->string('email', 100)->nullable()->after('name')->comment('客户邮箱');
            }
        });

        // 第二步：如果两个email字段都存在，复制数据
        if (! $hasEmail && $hasCustomerEmail) {
            // 复制customer_email数据到新的email字段
            DB::statement('UPDATE inquiries SET email = customer_email');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 不执行回滚操作，因为这是修复迁移
    }
};

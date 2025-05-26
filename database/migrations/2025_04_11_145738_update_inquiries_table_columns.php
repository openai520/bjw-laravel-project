<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 第一步：重命名列
        Schema::table('inquiries', function (Blueprint $table) {
            $table->renameColumn('customer_name', 'name');
            $table->renameColumn('customer_phone', 'phone');
        });

        // 第二步：添加新列并删除旧列
        Schema::table('inquiries', function (Blueprint $table) {
            // 添加新列
            $table->string('country', 100)->after('name')->comment('客户国家/地区');
            $table->string('whatsapp', 50)->nullable()->after('phone')->comment('WhatsApp 号码');
            $table->string('wechat', 50)->nullable()->after('whatsapp')->comment('微信 ID');
            $table->integer('total_quantity')->default(0)->after('wechat')->comment('总数量');
            $table->decimal('total_amount', 10, 2)->default(0)->after('total_quantity')->comment('总金额');
            
            // 删除不需要的列
            $table->dropColumn(['customer_email', 'customer_company', 'customer_message']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 第一步：恢复被删除的列并删除新增的列
        Schema::table('inquiries', function (Blueprint $table) {
            // 恢复被删除的列
            $table->string('customer_email', 100)->after('name')->comment('客户邮箱');
            $table->string('customer_company', 100)->nullable()->after('phone')->comment('客户公司');
            $table->text('customer_message')->nullable()->after('customer_company')->comment('客户留言');
            
            // 删除新增的列
            $table->dropColumn(['country', 'whatsapp', 'wechat', 'total_quantity', 'total_amount']);
        });

        // 第二步：恢复列名
        Schema::table('inquiries', function (Blueprint $table) {
            $table->renameColumn('name', 'customer_name');
            $table->renameColumn('phone', 'customer_phone');
        });
    }
};

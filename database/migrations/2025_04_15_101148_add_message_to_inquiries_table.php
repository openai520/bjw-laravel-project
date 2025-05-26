<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 检查message和customer_message列
        $hasMessage = Schema::hasColumn('inquiries', 'message');
        $hasCustomerMessage = Schema::hasColumn('inquiries', 'customer_message');
        
        if (!$hasMessage) {
            Schema::table('inquiries', function (Blueprint $table) {
                // 如果ip_address列存在，则在其后添加message列
                if (Schema::hasColumn('inquiries', 'ip_address')) {
                    $table->text('message')->nullable()->after('ip_address')->comment('客户留言');
                } else {
                    // 否则在wechat列后添加
                    $table->text('message')->nullable()->after('wechat')->comment('客户留言');
                }
            });
        }
        
        // 如果两者都存在，从customer_message复制数据到message
        if (!$hasMessage && $hasCustomerMessage) {
            DB::statement('UPDATE inquiries SET message = customer_message');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('inquiries', 'message')) {
            Schema::table('inquiries', function (Blueprint $table) {
                $table->dropColumn('message');
            });
        }
    }
};

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
        // 检查表是否存在
        if (Schema::hasTable('visitor_logs')) {
            // 检查url列是否已经存在
            if (!Schema::hasColumn('visitor_logs', 'url')) {
                Schema::table('visitor_logs', function (Blueprint $table) {
                    // 添加url字段，允许为空
                    $table->string('url', 2048)->nullable()->comment('访问的URL')->after('user_agent');
                });
            } else {
                // 如果列已存在但不是nullable，修改它为nullable
        Schema::table('visitor_logs', function (Blueprint $table) {
                    $table->string('url', 2048)->nullable()->change();
        });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 不执行任何操作，因为这是修复操作
        // 如果想要回滚，可以取消注释下面的代码
        /*
        Schema::table('visitor_logs', function (Blueprint $table) {
            // 我们不实际删除字段，只是让它变为必填
            if (Schema::hasColumn('visitor_logs', 'url')) {
                $table->string('url', 2048)->nullable(false)->change();
            }
        });
        */
    }
};

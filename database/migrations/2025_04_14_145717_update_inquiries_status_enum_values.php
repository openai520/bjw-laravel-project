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
        // 使用 DB 原生查询来修改 ENUM 类型
        DB::statement("ALTER TABLE inquiries MODIFY COLUMN status ENUM('pending', 'processing', 'completed', 'cancelled', 'processed') NOT NULL DEFAULT 'pending'");

        // 将所有 processing, completed, cancelled 状态更新为 processed
        DB::statement("UPDATE inquiries SET status = 'processed' WHERE status IN ('processing', 'completed', 'cancelled')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 恢复原来的 ENUM 类型
        DB::statement("ALTER TABLE inquiries MODIFY COLUMN status ENUM('pending', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");

        // 将所有 processed 状态更新为 completed
        DB::statement("UPDATE inquiries SET status = 'completed' WHERE status = 'processed'");
    }
};

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
        // 更新所有现有的询价单状态
        DB::statement("UPDATE inquiries SET status = 'pending' WHERE status NOT IN ('pending', 'processed')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 不需要回滚操作，因为我们不知道原始状态
    }
};

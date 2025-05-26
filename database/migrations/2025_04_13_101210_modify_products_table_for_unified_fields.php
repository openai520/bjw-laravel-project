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
        Schema::table('products', function (Blueprint $table) {
            // 删除旧的描述字段
            $table->dropColumn(['description_en', 'description_fr']);

            // 添加新的描述字段
            $table->text('description')->after('name')->comment('产品描述（中文）');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // 删除新的描述字段
            $table->dropColumn('description');

            // 恢复旧的描述字段
            $table->text('description_en')->comment('英文产品描述');
            $table->text('description_fr')->comment('法文产品描述');
        });
    }
};

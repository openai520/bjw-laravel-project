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
        Schema::create('product_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('ip_address', 45)->nullable(); // 支持IPv6
            $table->text('user_agent')->nullable();
            $table->string('referer')->nullable();
            $table->timestamp('viewed_at');
            $table->timestamps();
            
            // 索引优化
            $table->index(['product_id', 'viewed_at']);
            $table->index(['ip_address', 'product_id', 'viewed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_views');
    }
};

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
        Schema::table('categories', function (Blueprint $table) {
            // Ensure sort_order column exists before trying to place columns after it.
            // If it might not exist, consider adding it first or placing after another guaranteed column like 'slug'.
            if (Schema::hasColumn('categories', 'sort_order')) {
                $table->boolean('show_on_home')->default(false)->after('sort_order');
                $table->integer('display_order')->nullable()->default(999)->after('show_on_home');
            } else {
                // Fallback if sort_order doesn't exist for some reason, place after slug
                $table->boolean('show_on_home')->default(false)->after('slug');
                $table->integer('display_order')->nullable()->default(999)->after('show_on_home');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('show_on_home');
            $table->dropColumn('display_order');
        });
    }
};

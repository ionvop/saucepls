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
        Schema::table('sauce_requests', function (Blueprint $table) {
            // GIFs skip the pre-post pipeline and are stored without a
            // perceptual hash, so the column must be nullable.
            $table->string('phash64')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sauce_requests', function (Blueprint $table) {
            $table->string('phash64')->nullable(false)->change();
        });
    }
};

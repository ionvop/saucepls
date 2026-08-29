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
        Schema::table('sauce_request_tagging_history', function (Blueprint $table) {
            // The full resulting tag state immediately after this change was
            // applied, so the history can show what the tags looked like.
            $table->json('tags_snapshot')->nullable()->after('removed_tags');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sauce_request_tagging_history', function (Blueprint $table) {
            $table->dropColumn('tags_snapshot');
        });
    }
};
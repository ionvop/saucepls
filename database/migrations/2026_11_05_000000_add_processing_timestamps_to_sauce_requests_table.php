<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sauce_requests', function (Blueprint $table) {
            $table->timestamp('saucenao_checked_at')->nullable();
            $table->timestamp('enriched_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sauce_requests', function (Blueprint $table) {
            $table->dropColumn(['saucenao_checked_at', 'enriched_at']);
        });
    }
};
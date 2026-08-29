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
        Schema::create('sauce_request_tagging_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sauce_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // The tags added and removed by the user in this change.
            $table->json('added_tags')->nullable();
            $table->json('removed_tags')->nullable();
            $table->timestamps();

            $table->index('sauce_request_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sauce_request_tagging_history');
    }
};

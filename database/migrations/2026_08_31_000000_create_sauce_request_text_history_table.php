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
        Schema::create('sauce_request_text_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sauce_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // The full extracted text immediately after this change was
            // applied, so the history can show what the text looked like.
            $table->text('text_snapshot')->nullable();
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
        Schema::dropIfExists('sauce_request_text_history');
    }
};
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
        Schema::create('sauce_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sauce_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('content');
            // Link to the source if applicable such as Pixiv, Twitter, etc.
            // Not applicable for manga panels, anime screenshots, etc. since
            // links to possible piracy sites are not allowed.
            $table->string('url')->nullable();
            $table->timestamp('deleted_at')->nullable();
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
        Schema::dropIfExists('sauce_answers');
    }
};

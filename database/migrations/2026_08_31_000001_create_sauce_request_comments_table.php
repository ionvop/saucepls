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
        Schema::create('sauce_request_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sauce_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // The comment this is a reply to. A null value means it is a
            // top-level comment. Replies are limited to one level deep.
            $table->foreignId('parent_id')->nullable()->constrained('sauce_request_comments')->cascadeOnDelete();
            $table->text('content');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index('sauce_request_id');
            $table->index('user_id');
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sauce_request_comments');
    }
};
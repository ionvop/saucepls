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
        Schema::create('user_comments', function (Blueprint $table) {
            $table->id();
            // The user whose profile the comment was left on.
            $table->foreignId('profile_user_id')->constrained('users')->cascadeOnDelete();
            // The user who wrote the comment.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // The comment this is a reply to. A null value means it is a
            // top-level comment. Replies are limited to one level deep.
            $table->foreignId('parent_id')->nullable()->constrained('user_comments')->cascadeOnDelete();
            $table->text('content');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index('profile_user_id');
            $table->index('user_id');
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_comments');
    }
};
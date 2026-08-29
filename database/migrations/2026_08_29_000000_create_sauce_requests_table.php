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
        Schema::create('sauce_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->default('Sauce pls');
            $table->text('description')->default('');
            $table->text('text')->default('');
            $table->string('image_path');
            // The accepted sauce answer. The foreign key is intentionally
            // omitted for now because it points back to `sauce_answers`,
            // which does not exist yet (circular reference). It will be
            // added once the `sauce_answers` table is created.
            $table->unsignedBigInteger('accepted_sauce')->nullable();
            $table->string('phash64');
            $table->boolean('is_explicit')->default(true);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sauce_requests');
    }
};
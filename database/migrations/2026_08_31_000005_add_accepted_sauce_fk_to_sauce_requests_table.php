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
        // The `accepted_sauce` column was added in the original
        // `sauce_requests` migration without a foreign key because it
        // pointed back to `sauce_answers`, which did not exist yet
        // (circular reference). Now that `sauce_answers` exists, add the
        // foreign key. If the accepted answer is deleted, the request
        // becomes unsolved again.
        Schema::table('sauce_requests', function (Blueprint $table) {
            $table->foreign('accepted_sauce')
                ->references('id')
                ->on('sauce_answers')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sauce_requests', function (Blueprint $table) {
            $table->dropForeign(['accepted_sauce']);
        });
    }
};

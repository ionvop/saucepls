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
            // When the request was actually posted. Drafts created during
            // the pre-post pipeline have a null value until the user clicks
            // "Post request" on the details page.
            $table->timestamp('published_at')->nullable()->after('is_explicit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sauce_requests', function (Blueprint $table) {
            $table->dropColumn('published_at');
        });
    }
};
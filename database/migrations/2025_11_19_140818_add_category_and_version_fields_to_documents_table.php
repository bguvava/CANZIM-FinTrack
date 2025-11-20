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
        Schema::table('documents', function (Blueprint $table) {
            $table->string('category')->after('description');
            $table->unsignedInteger('version_number')->default(1)->after('category');
            $table->foreignId('current_version_id')->nullable()->after('version_number');

            // Add indexes
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn(['category', 'version_number', 'current_version_id']);
        });
    }
};

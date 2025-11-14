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
        Schema::create('project_donors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('donor_id')->constrained('donors')->onDelete('cascade');
            $table->decimal('funding_amount', 15, 2)->default(0);
            $table->date('funding_period_start')->nullable();
            $table->date('funding_period_end')->nullable();
            $table->boolean('is_restricted')->default(false);
            $table->timestamps();

            // Indexes
            $table->index('project_id');
            $table->index('donor_id');
            $table->unique(['project_id', 'donor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_donors');
    }
};

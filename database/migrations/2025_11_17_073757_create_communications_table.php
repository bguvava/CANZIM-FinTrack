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
        Schema::create('communications', function (Blueprint $table) {
            $table->id();
            $table->morphs('communicable'); // donor_id, donor_type polymorphic
            $table->string('type'); // email, phone_call, meeting, letter, other
            $table->dateTime('communication_date');
            $table->string('subject');
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->date('next_action_date')->nullable();
            $table->text('next_action_notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Indexes
            $table->index('communication_date');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communications');
    }
};

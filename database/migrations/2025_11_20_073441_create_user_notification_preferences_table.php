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
        Schema::create('user_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('expense_approvals')->default(true);
            $table->boolean('budget_alerts')->default(true);
            $table->boolean('project_milestones')->default(true);
            $table->boolean('comment_mentions')->default(true);
            $table->boolean('report_generation')->default(true);
            $table->string('email_frequency')->default('instant'); // instant, daily, weekly
            $table->timestamps();
            
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notification_preferences');
    }
};

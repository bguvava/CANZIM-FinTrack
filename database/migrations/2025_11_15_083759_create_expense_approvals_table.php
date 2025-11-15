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
        Schema::create('expense_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained()->onDelete('cascade');
            $table->enum('approval_level', ['Finance Officer', 'Programs Manager']);
            $table->enum('action', ['Approved', 'Rejected', 'Returned']);
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->text('comments')->nullable();
            $table->timestamp('action_date');
            $table->timestamps();

            $table->index(['expense_id', 'approval_level']);
            $table->index('action_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_approvals');
    }
};

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
        Schema::create('cash_flow', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date');
            $table->enum('type', ['cash_in', 'cash_out']);
            $table->decimal('amount', 15, 2);
            $table->text('description');
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null');
            $table->foreignId('donor_id')->nullable()->constrained('donors')->onDelete('set null');
            $table->foreignId('expense_id')->nullable()->constrained('expenses')->onDelete('set null');
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->onDelete('cascade');
            $table->decimal('balance_before', 15, 2)->default(0);
            $table->decimal('balance_after', 15, 2)->default(0);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Indexes
            $table->index('transaction_date');
            $table->index('type');
            $table->index('project_id');
            $table->index('bank_account_id');
            $table->index(['transaction_date', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_flow');
    }
};

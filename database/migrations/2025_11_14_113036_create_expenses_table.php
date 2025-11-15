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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number', 50)->unique();
            $table->foreignId('project_id')->constrained('projects')->onDelete('restrict');
            $table->foreignId('budget_item_id')->nullable()->constrained('budget_items')->onDelete('set null');
            $table->foreignId('expense_category_id')->constrained('expense_categories')->onDelete('restrict');
            $table->date('expense_date');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->text('description');
            $table->string('receipt_path')->nullable();
            $table->string('receipt_original_name')->nullable();
            $table->unsignedInteger('receipt_file_size')->nullable();

            // Workflow tracking
            $table->enum('status', [
                'Draft',
                'Submitted',
                'Under Review',
                'Approved',
                'Rejected',
                'Paid',
            ])->default('Draft');

            // Submitter
            $table->foreignId('submitted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('submitted_at')->nullable();

            // Finance Officer Review
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_comments')->nullable();

            // Programs Manager Approval
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_comments')->nullable();

            // Rejection
            $table->text('rejection_reason')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('rejected_at')->nullable();

            // Payment Tracking
            $table->foreignId('paid_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_reference', 100)->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->text('payment_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('expense_date');
            $table->index('status');
            $table->index('submitted_by');
            $table->index(['project_id', 'status']);
            $table->index(['expense_category_id', 'expense_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};

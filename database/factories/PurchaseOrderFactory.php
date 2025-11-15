<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $counter = 0;
        $counter++;
        $subtotal = fake()->randomFloat(2, 5000, 200000);
        $taxAmount = $subtotal * 0.15; // 15% tax
        $totalAmount = $subtotal + $taxAmount;

        return [
            'po_number' => 'PO-'.date('Y').'-'.str_pad((string) $counter, 4, '0', STR_PAD_LEFT),
            'vendor_id' => Vendor::factory(),
            'project_id' => Project::factory(),
            'order_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'expected_delivery_date' => fake()->dateTimeBetween('now', '+60 days'),
            'status' => 'Draft',
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'notes' => fake()->optional()->paragraph(),
            'terms_conditions' => fake()->optional()->paragraph(),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the PO is pending approval.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Draft',
        ]);
    }

    /**
     * Indicate that the PO is pending approval.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Pending',
            'submitted_by' => User::factory(),
            'submitted_at' => fake()->dateTimeBetween($attributes['order_date'], 'now'),
        ]);
    }

    /**
     * Indicate that the PO is approved.
     */
    public function approved(): static
    {
        return $this->state(function (array $attributes) {
            $submittedAt = fake()->dateTimeBetween($attributes['order_date'], 'now');

            return [
                'status' => 'Approved',
                'submitted_by' => User::factory(),
                'submitted_at' => $submittedAt,
                'approved_by' => User::factory(),
                'approved_at' => fake()->dateTimeBetween($submittedAt, 'now'),
            ];
        });
    }

    /**
     * Indicate that the PO is rejected.
     */
    public function rejected(): static
    {
        return $this->state(function (array $attributes) {
            $submittedAt = fake()->dateTimeBetween($attributes['order_date'], 'now');

            return [
                'status' => 'Rejected',
                'submitted_by' => User::factory(),
                'submitted_at' => $submittedAt,
                'rejected_by' => User::factory(),
                'rejected_at' => fake()->dateTimeBetween($submittedAt, 'now'),
                'rejection_reason' => fake()->sentence(),
            ];
        });
    }

    /**
     * Indicate that the PO is received.
     */
    public function received(): static
    {
        return $this->state(function (array $attributes) {
            $submittedAt = fake()->dateTimeBetween($attributes['order_date'], 'now');
            $approvedAt = fake()->dateTimeBetween($submittedAt, 'now');

            return [
                'status' => 'Received',
                'submitted_by' => User::factory(),
                'submitted_at' => $submittedAt,
                'approved_by' => User::factory(),
                'approved_at' => $approvedAt,
                'actual_delivery_date' => fake()->dateTimeBetween($approvedAt, 'now'),
            ];
        });
    }

    /**
     * Indicate that the PO is completed.
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $submittedAt = fake()->dateTimeBetween($attributes['order_date'], 'now');
            $approvedAt = fake()->dateTimeBetween($submittedAt, 'now');
            $deliveredAt = fake()->dateTimeBetween($approvedAt, 'now');

            return [
                'status' => 'Completed',
                'submitted_by' => User::factory(),
                'submitted_at' => $submittedAt,
                'approved_by' => User::factory(),
                'approved_at' => $approvedAt,
                'actual_delivery_date' => $deliveredAt,
                'completed_at' => fake()->dateTimeBetween($deliveredAt, 'now'),
            ];
        });
    }
}

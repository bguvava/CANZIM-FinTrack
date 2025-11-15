<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseOrderItem>
 */
class PurchaseOrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->randomFloat(2, 1, 100);
        $unitPrice = fake()->randomFloat(2, 50, 5000);
        $totalPrice = $quantity * $unitPrice;

        $units = ['pcs', 'kg', 'liters', 'boxes', 'units', 'sets'];

        return [
            'purchase_order_id' => PurchaseOrder::factory(),
            'line_number' => 1,
            'description' => fake()->sentence(6),
            'quantity' => $quantity,
            'unit' => fake()->randomElement($units),
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'quantity_received' => 0,
        ];
    }

    /**
     * Indicate that the item is partially received.
     */
    public function partiallyReceived(): static
    {
        return $this->state(function (array $attributes) {
            $quantity = $attributes['quantity'];
            $receivedQuantity = fake()->randomFloat(2, 1, $quantity - 1);

            return [
                'quantity_received' => $receivedQuantity,
                'received_date' => fake()->dateTimeBetween('-1 month', 'now'),
            ];
        });
    }

    /**
     * Indicate that the item is fully received.
     */
    public function received(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity_received' => $attributes['quantity'],
            'received_date' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }
}

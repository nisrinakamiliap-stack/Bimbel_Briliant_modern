<?php

namespace Database\Factories;

use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;
use App\PaymentMethod;
use App\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'enrollment_id' => Enrollment::factory(),
            'amount' => 100000,
            'method' => fake()->randomElement(PaymentMethod::cases()),
            'paid_on' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'status' => PaymentStatus::WaitingVerification,
            'notes' => null,
            'recorded_by' => null,
        ];
    }

    /**
     * Indicate that the payment has been verified.
     */
    public function paid(?User $admin = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Paid,
            'processed_by' => $admin?->id,
            'processed_at' => now(),
        ]);
    }

    /**
     * Indicate that the payment has been rejected.
     */
    public function rejected(string $reason = 'Bukti transfer tidak terbaca.'): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Rejected,
            'processed_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }
}

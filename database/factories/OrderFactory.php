<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'first_name'      => $this->faker->firstName,
            'last_name'       => $this->faker->lastName,
            'email'           => $this->faker->unique()->safeEmail,
            'phone'           => $this->faker->phoneNumber,
            'address'         => $this->faker->streetAddress,
            'city'            => $this->faker->city,
            'zip'             => $this->faker->postcode,
            'carrier'         => $this->faker->randomElement(['Balikovna', 'Zasilkovna', 'Ppl', 'osobni']),
            'carrier_id'      => $this->faker->uuid,
            'carrier_address' => $this->faker->address,
        ];
    }
}

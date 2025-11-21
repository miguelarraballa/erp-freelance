<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    protected $model = \App\Models\Cliente::class;

    public function definition(): array
    {
        return [
            'nombre'     => $this->faker->company(),
            'razon_social'=> $this->faker->company(),
            'nif'        => 'B12345674',
            'direccion'  => $this->faker->streetAddress(),
            'cp'         => $this->faker->postcode(),
            'ciudad'     => $this->faker->city(),
            'provincia'  => $this->faker->state(),
            'pais'       => 'ES',
            'cliente'    => true,
            'proveedor'  => false,
            'irpf'       => false,
            'observaciones' => null,
        ];
    }
}
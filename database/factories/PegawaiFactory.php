<?php

namespace Database\Factories;

use App\Models\Satker;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pegawai>
 */
class PegawaiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'nik' => (string) fake()->unique()->numerify('################'),
            'pendidikan' => fake()->randomElement(['SMA/SMK', 'D3', 'S1', 'S2']),
            'satker_id' => Satker::query()->inRandomOrder()->value('id') ?? Satker::factory(),
            'status' => fake()->randomElement(['aktif', 'non_aktif']),
        ];
    }
}

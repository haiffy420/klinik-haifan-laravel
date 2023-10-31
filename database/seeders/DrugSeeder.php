<?php

namespace Database\Seeders;

use App\Models\Drug;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DrugSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Drug::factory()->create([
            'name' => 'Bodrex',
            'price' => 2000,
            'stock' => 150,
            'expiration_date' => Carbon::create('2025', '01', '01'),
        ]);
        Drug::factory()->create([
            'name' => 'Paracetamol',
            'price' => 4000,
            'stock' => 150,
            'expiration_date' => Carbon::create('2025', '01', '01'),
        ]);
        Drug::factory()->create([
            'name' => 'Antimo',
            'price' => 5000,
            'stock' => 150,
            'expiration_date' => Carbon::create('2025', '01', '01'),
        ]);

    }
}

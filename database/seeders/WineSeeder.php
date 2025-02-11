<?php

namespace Database\Seeders;

use App\Models\Wine;
use Illuminate\Database\Seeder;

class WineSeeder extends Seeder
{
    public function run(): void
    {
        Wine::factory(10)->create();
    }
}
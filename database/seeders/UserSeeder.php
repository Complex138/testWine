<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wine;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $wines = Wine::all();
        
        User::factory(20)
            ->create()
            ->each(function ($user) use ($wines) {
                // Randomly assign favorite wine to some users
                if (fake()->boolean(80)) { // 80% chance to have favorite wine
                    $user->favorite_wine_id = $wines->random()->id;
                    $user->save();
                }
            });
    }
}
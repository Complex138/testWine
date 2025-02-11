<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Каберне Совиньон',
                'Мерло',
                'Пино Нуар',
                'Шардоне',
                'Совиньон Блан',
                'Сира',
                'Мальбек',
                'Рислинг',
                'Красностоп Золотовский',
                'Саперави'
            ])
        ];
    }
}
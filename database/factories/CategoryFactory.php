<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word, // Random category name
            'name_ar' => $this->faker->randomElement(['جوال', 'براد', 'لابتوب', 'غاز']),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Store;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'store_id' => Store::factory(), 
            'category_id' => Category::factory(), 
            'name' => $this->faker->word, 
            'description' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 10, 100), 
            'stock_quantity' => $this->faker->numberBetween(1, 100), 
            'image_url' => $this->faker->imageUrl(640, 480, 'product'), 
            'discount_type' => $this->faker->randomElement(['percentage', 'fixed']), 
            'discount_value' => $this->faker->randomFloat(2, 1, 50), 
            'discount_start' => $this->faker->dateTimeThisYear(), 
            'discount_end' => $this->faker->dateTimeBetween('now', '+1 month'), 
        ];
    }
}

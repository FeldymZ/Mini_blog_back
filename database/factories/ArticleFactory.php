<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'titre' => $this->faker->sentence,
            'contenu' => $this->faker->paragraphs(5, true),
            'image_url' => $this->faker->imageUrl(640, 480, 'cats', true),
        ];
    }
}

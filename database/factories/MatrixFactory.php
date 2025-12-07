<?php

namespace Database\Factories;

use App\Models\Matrix;
use Illuminate\Database\Eloquent\Factories\Factory;

class MatrixFactory extends Factory
{
    protected $model = Matrix::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'size' => 3,
            'grid' => "1 2 3\n4 5 6\n7 8 9",
            'cells' => '[]',   // adjust if needed
            'user_id' => null,
        ];
    }
}

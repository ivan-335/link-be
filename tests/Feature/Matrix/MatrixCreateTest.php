<?php

namespace Tests\Feature\Matrix;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatrixCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_matrix()
    {
        $user = User::factory()->create();

        $payload = [
            'name' => 'My Matrix',
            'size' => 3,
            'grid' => "1 2 3\n4 5 6\n7 8 9",
        ];

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/matrix', $payload);

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'name' => 'My Matrix',
            'size' => 3,
        ]);

        $this->assertDatabaseHas('matrices', [
            'name' => 'My Matrix',
            'user_id' => $user->id,
        ]);
    }
}

<?php

namespace Tests\Feature\Matrix;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatrixVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_visibility_calculation_for_matrix()
    {
        $user = User::factory()->create();
        $payload = [
            'name' => 'My Matrix',
            'size' => 4,
            'grid' => "3 5 2 1\n4 1 8 2\n3 2 0 0\n3 1 2 1"
        ];
        $token = $user->createToken('test')->plainTextToken;
        $matrix = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/matrix', $payload)->json();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)->getJson("/api/matrix/{$matrix['matrix']['id']}/calculate");

        $response->assertOk();
        $result = $response->json();
        $this->assertEquals(13, $result['visible_book_stacks'], "Visibility calculation did not match expected value");
    }
}

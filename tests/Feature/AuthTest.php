<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthTest extends TestCase
{
    /**
     * A basic feature test example.
     */
  //  use DatabaseTransactions;
    public function test_can_register_a_user()
    {

        $data = [
            'name' => 'Test User2',
            'email' => 'testuser2@example.com',
            'password' => 'password123',
            'password_confirmation' =>'password123',
            'tenant_name' => 'Tenant A'
        ];

        $response = $this->postJson('/api/register', $data);
        dd($response->json());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'tenant_id'],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'testuser2@example.com',
        ]);
    }

    public function test_can_login_a_user()
{
    $tenant = Tenant::create(['name' => 'Test Tenant']);
    $user = User::factory()->create([
        'email' => 'testuser2@example.com',
        'password' => bcrypt('password123'),
        'tenant_id' => $tenant->id,
    ]);

    $data = [
        'email' => 'testuser@example.com',
        'password' => 'password123'
    ];

    $response = $this->postJson('/api/login', $data);
  //  dd($response->json());
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'token',
                'user' => ['id', 'name', 'email', 'tenant_id'],
            ],
        ]);
}

}

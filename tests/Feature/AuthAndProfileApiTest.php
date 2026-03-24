<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthAndProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_dietary_profile(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Alice Customer',
            'email' => 'alice@example.com',
            'password' => 'password123',
            'dietary_tags' => ['vegan', 'gluten_free'],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('user.email', 'alice@example.com')
            ->assertJsonPath('user.role', User::ROLE_CUSTOMER)
            ->assertJsonPath('profile.dietary_tags.0', 'vegan');

        $this->assertDatabaseHas('users', [
            'email' => 'alice@example.com',
            'role' => User::ROLE_CUSTOMER,
        ]);
        $this->assertDatabaseCount('profiles', 1);
    }

    public function test_user_can_login_view_me_update_profile_and_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);
        $user->profile()->create([
            'dietary_tags' => ['no_sugar'],
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('user.profile.dietary_tags.0', 'no_sugar');

        Sanctum::actingAs($user);

        $this->putJson('/api/profile', [
            'dietary_tags' => ['gluten_free', 'no_lactose'],
        ])
            ->assertOk()
            ->assertJsonPath('profile.dietary_tags.1', 'no_lactose');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Logout successful.');
    }
}

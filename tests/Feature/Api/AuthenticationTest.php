<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_as_employer(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Employer',
            'email' => 'john@employer.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'employer',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email', 'role'],
                    'token',
                ],
            ])
            ->assertJsonPath('data.user.email', 'john@employer.com')
            ->assertJsonPath('data.user.role', 'employer');

        $this->assertDatabaseHas('users', [
            'email' => 'john@employer.com',
            'role' => 'employer',
        ]);
    }

    public function test_user_can_register_as_applicant(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Jane Applicant',
            'email' => 'jane@applicant.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'applicant',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.user.role', 'applicant');

        $this->assertDatabaseHas('users', [
            'email' => 'jane@applicant.com',
            'role' => 'applicant',
        ]);
    }

    public function test_registration_requires_valid_role(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'invalid_role',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);
    }

    public function test_registration_requires_all_fields(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password', 'role']);
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
            'role' => 'applicant',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }


    public function test_registration_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'applicant',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }


    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email', 'role'],
                    'token',
                ],
            ])
            ->assertJsonPath('data.user.id', $user->id);
    }


    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertUnauthorized()
            ->assertJson([
                'message' => 'Invalid credentials'
            ]);
    }


    public function test_authenticated_user_can_get_their_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/user');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'role'],
            ])
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email);
    }


    public function test_unauthenticated_user_cannot_get_profile(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertUnauthorized();
    }


    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $response->assertOk()
            ->assertJson(['message' => 'Logged out successfully']);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}

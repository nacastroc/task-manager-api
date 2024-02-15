<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test registration.
     *
     * @return void
     */
    public function testRegister()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123*'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201) // 201 New entity created
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
                'token',
            ]);
    }

    /**
     * Test registration with existing email.
     *
     * @return void
     */
    public function testRegisterWithExistingEmail()
    {
        $email = 'test@example.com';
        $password = 'Password123*';

        User::factory()->create([
            'email' => $email,
            'password' => $password,
        ]);

        $userData = [
            'name' => 'Test User',
            'email' => $email, // Invalid email: alreading in use
            'password' => $password
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422) // 422 Unprocessable Entity
            ->assertJsonValidationErrors('email');
    }

    /**
     * Test registration with invalid name.
     *
     * @return void
     */
    public function testRegisterWithInvalidName()
    {
        $userData = [
            'name' => '', // Invalid user name: empty string
            'email' => 'test@example.com',
            'password' => 'Password123*'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422) // 422 Unprocessable Entity
            ->assertJsonValidationErrors('name');
    }

    /**
     * Test registration with invalid email.
     *
     * @return void
     */
    public function testRegisterWithInvalidEmail()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'testexample.com', // Invalid email: no @ character
            'password' => 'Password123*'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422) // 422 Unprocessable Entity
            ->assertJsonValidationErrors('email');
    }

    /**
     * Test registration with invalid password.
     *
     * @return void
     */
    public function testRegisterWithInvalidPassword()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password' // Invalid password: no uppercase letter, number, or special character
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422) // 422 Unprocessable Entity
            ->assertJsonValidationErrors('password');
    }

    /**
     * Test login.
     *
     * @return void
     */
    public function testLogin()
    {
        $email = 'test@example.com';
        $password = 'Password123*';

        $user = User::factory()->create([
            'email' => $email,
            'password' => $password,
        ]);

        $loginData = [
            'email' => $email,
            'password' => $password,
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(200) // 200 Success
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
                'token',
            ])
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    // 'created_at' and 'updated_at' are not compared because they are set at the time of user creation
                ],
            ]);
    }

    /**
     * Test login with incorrect password.
     *
     * @return void
     */
    public function testLoginWithIncorrectPassword()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'Password123*',
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'WrongPassword123*',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(401) // 401 Unauthorized
            ->assertJson(['message' => 'Invalid email or password']);
    }

    /**
     * Test login with non-existent user email.
     *
     * @return void
     */
    public function testLoginWithNonExistentEmail()
    {
        $loginData = [
            'email' => 'nonexistent@example.com',
            'password' => 'Password123*',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(401) // 401 Unauthorized
            ->assertJson(['message' => 'Invalid email or password']);
    }

    /**
     * Test login with missing email.
     *
     * @return void
     */
    public function testLoginWithMissingEmail()
    {
        $loginData = [
            'password' => 'Password123*',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(401) // 401 Unauthorized
            ->assertJson(['message' => 'Invalid email or password']);
    }

    /**
     * Test login with missing password.
     *
     * @return void
     */
    public function testLoginWithMissingPassword()
    {
        $loginData = [
            'email' => 'test@example.com',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(401) // 401 Unauthorized
            ->assertJson(['message' => 'Invalid email or password']);
    }

    /**
     * Test logout.
     *
     * @return void
     */
    public function testLogout()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'Password123*',
        ]);

        $token = $user->createToken('token-name')->plainTextToken;

        $header = [
            'Authorization' => "Bearer $token"
        ];

        $response = $this->postJson('/api/logout', [], $header);

        $response->assertStatus(200) // 200 Success
            ->assertExactJson(['message' => 'Logged out successfully']);
    }

    /**
     * Test logout attempt without authenticated user.
     *
     * @return void
     */
    public function testLogoutWithoutAuthenticatedUser()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401); // 401 Unauthorized
    }

    /**
     * Test logout attempt without verified email.
     *
     * @return void
     */
    public function testLogoutWithoutVerifiedEmail()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'Password123*',
        ]);

        $user->email_verified_at = null;
        $user->save();

        // Manually create an access token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->postJson('/api/logout', [],
            [
                'Authorization' => 'Bearer ' . $token
            ]);

        $response->assertStatus(403); // 403 Forbidden (Unauthorized)
    }
}

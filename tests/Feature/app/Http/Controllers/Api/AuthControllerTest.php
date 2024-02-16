<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    // Constants.
    const TEST_USER_DATA = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password123*'
    ];

    protected $user;

    // Helper methods.
    private function assertUnprocessableEntity($response, $field)
    {
        $response->assertStatus(422)->assertJsonValidationErrors($field);
    }

    private function assertUnauthorized($response)
    {
        $response->assertStatus(401)->assertJson(['message' => 'Invalid email or password']);
    }

    // Test setup.
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(self::TEST_USER_DATA);
    }

    // Data providers.

    /**
     * Data provider for user registration test.
     *
     * @return array
     */
    public function registrationDataProvider()
    {
        return [
            // Valid registration
            [
                ['name' => 'Test User 0', 'email' => 'test0@example.com', 'password' => 'Password123*'],
                201,
            ],
            // Taken Email
            [
                self::TEST_USER_DATA,
                422,
                'email',
            ],
            // Invalid name
            [
                ['name' => '', 'email' => 'test1@example.com', 'password' => 'Password123*'],
                422,
                'name',
            ],
            // Invalid email
            [
                ['name' => 'Test User 2', 'email' => 'test2example.com', 'password' => 'Password123*'],
                422,
                'email',
            ],
            // Invalid password
            [
                ['name' => 'Test User 3', 'email' => 'test3@example.com', 'password' => 'password'],
                422,
                'password',
            ],
        ];
    }

    /**
     * Data provider for user login test.
     *
     * @return array
     */
    public function loginDataProvider()
    {
        return [
            // Valid login
            [
                ['email' => self::TEST_USER_DATA['email'], 'password' => self::TEST_USER_DATA['password']],
                200,
            ],
            // Wrong password
            [
                ['email' => self::TEST_USER_DATA['email'], 'password' => 'WrongPassword123*'],
                401,
            ],
            // Wrong email
            [
                ['email' => 'wrong@example.com', 'password' => self::TEST_USER_DATA['password']],
                401,
            ],
            // Invalid email
            [
                ['email' => 'example.com', 'password' => self::TEST_USER_DATA['password']],
                422,
                'email'
            ],
            // Missing email
            [
                ['password' => self::TEST_USER_DATA['password']],
                422,
                'email'
            ],
            // Missing password
            [
                ['email' => self::TEST_USER_DATA['email']],
                422,
                'password'
            ],
        ];
    }

    /**
     * Data provider for logout tests.
     *
     * @return array
     */
    public function logoutDataProvider()
    {
        return [
            'logout_success' => [
                'user' => [
                    'email_verified_at' => now(),
                ],
                'expectedStatus' => 200,
                'token' => 'token-name',
                'message' => 'Logged out successfully',
            ],
            'logout_unauthenticated' => [
                'user' => [],
                'expectedStatus' => 401,
                'token' => null,
                'message' => null,
            ],
            'logout_unverified_email' => [
                'user' => [
                    'email_verified_at' => null,
                ],
                'expectedStatus' => 403,
                'token' => 'test-token',
                'message' => null,
            ],
        ];
    }

    /**
     * Test user registration.
     *
     * @dataProvider registrationDataProvider
     *
     * @param array $userData
     * @param int $expectedStatus
     * @param string|null $expectedValidationErrors
     *
     * @return void
     */
    public function testRegister($userData, $expectedStatus, $expectedValidationErrors = null)
    {
        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus($expectedStatus);

        if ($expectedValidationErrors) {
            $this->assertUnprocessableEntity($response, $expectedValidationErrors);
        } else {
            $response->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
                'token',
            ])->assertJson([
                'user' => [
                    'name' => $userData['name'],
                    'email' => $userData['email']
                ]
            ]);
        }
    }

    /**
     * Test user login.
     *
     * @dataProvider loginDataProvider
     *
     * @param array $loginData
     * @param int $expectedStatus
     * @param string|null $expectedValidationErrors
     *
     * @return void
     */
    public function testLogin($loginData, $expectedStatus, $expectedValidationErrors = null)
    {
        $response = $this->postJson('/api/login', $loginData);

        switch ($expectedStatus) {
            case 401:
                $this->assertUnauthorized($response);
                break;
            case 422:
                $this->assertUnprocessableEntity($response, $expectedValidationErrors);
                break;
            default:
                $response->assertStatus(200) // 200 Success
                    ->assertJsonStructure([
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'email_verified_at',
                            'created_at',
                            'updated_at',
                            'admin'
                        ],
                        'token',
                    ])
                    ->assertJson([
                        'user' => [
                            'id' => $this->user->id,
                            'name' => $this->user->name,
                            'email' => $this->user->email,
                        ],
                    ]);
                break;
        }
    }

    /**
     * Test logout with different scenarios.
     *
     * @dataProvider logoutDataProvider
     *
     * @param array $userAttributes
     * @param int $expectedStatus
     * @param string|null $tokenName
     * @param string|null $expectedMessage
     *
     * @return void
     */
    public function testLogout($userAttributes, $expectedStatus, $tokenName, $expectedMessage)
    {
        $user = User::factory()->create($userAttributes);

        if ($tokenName) {
            $token = $user->createToken($tokenName)->plainTextToken;
            $header = ['Authorization' => "Bearer $token"];
        } else {
            $header = [];
        }

        $response = $this->postJson('/api/logout', [], $header);

        $response->assertStatus($expectedStatus);

        if ($expectedMessage) {
            $response->assertExactJson(['message' => $expectedMessage]);
        }
    }
}

<?php

namespace Tests\Feature\Http\Controller\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    // Constants.
    const TEST_USER_DATA = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password123*'
    ];

    // Attributes.
    protected $user;

    // Helper methods.
    private function assertUnprocessableEntity($response, $field)
    {
        $response->assertStatus(422)->assertJsonValidationErrors($field);
    }

    private function assertUnauthorized($response)
    {
        $response->assertStatus(401)->assertJson(['message' => config('constants.messages.http_401_invalid_credentials')]);
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
            'valid_registration' => [
                ['name' => 'Test User 0', 'email' => 'test0@example.com', 'password' => 'Password123*'],
                201,
            ],
            'taken_email' => [
                self::TEST_USER_DATA,
                422,
                'email',
            ],
            'invalid_name' => [
                ['name' => '', 'email' => 'test1@example.com', 'password' => 'Password123*'],
                422,
                'name',
            ],
            'invalid_email' => [
                ['name' => 'Test User 2', 'email' => 'test2example.com', 'password' => 'Password123*'],
                422,
                'email',
            ],
            'invalid_password' => [
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
            'valid_login' => [
                ['email' => self::TEST_USER_DATA['email'], 'password' => self::TEST_USER_DATA['password']],
                200,
            ],
            'wrong_password' => [
                ['email' => self::TEST_USER_DATA['email'], 'password' => 'WrongPassword123*'],
                401,
            ],
            'wrong_email' => [
                ['email' => 'wrong@example.com', 'password' => self::TEST_USER_DATA['password']],
                401,
            ],
            'invalid_email' => [
                ['email' => 'example.com', 'password' => self::TEST_USER_DATA['password']],
                422,
                'email'
            ],
            'missing_email' => [
                ['password' => self::TEST_USER_DATA['password']],
                422,
                'email'
            ],
            'missing_password' => [
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
                'expectedStatus' => 200,
                'token' => 'token-name',
                'messageKey' => 'constants.messages.http_200_logout',
            ],
            'logout_unauthenticated' => [
                'expectedStatus' => 401,
                'token' => null,
                'messageKey' => 'constants.messages.http_401'
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
     * @param string|null $messageKey
     *
     * @return void
     */
    public function testLogout($expectedStatus, $tokenName, $messageKey)
    {
        $user = User::factory()->create();

        if ($tokenName) {
            $token = $user->createToken($tokenName)->plainTextToken;
            $header = ['Authorization' => "Bearer $token"];
        } else {
            $header = [];
        }

        $response = $this->postJson('/api/logout', [], $header);

        $response->assertStatus($expectedStatus);

        if ($messageKey) {
            $expectedMessage = config($messageKey);
            $response->assertExactJson(['message' => $expectedMessage]);
        }
    }
}

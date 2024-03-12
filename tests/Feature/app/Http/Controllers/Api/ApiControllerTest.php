<?php

namespace Tests\Feature\app\Http\Controller\Api;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApiControllerTest extends TestCase
{
    use DatabaseMigrations, RefreshDatabase;

    protected $baseRoute;
    protected $tasks;
    protected $users;

    // Helper functions

    // Test data providers

    /**
     * Data provider for list tests.
     *
     * @return array
     */
    public function listDataProvider()
    {
        $commonPaginatedJsonStructure = [
            'current_page',
            'first_page_url',
            'from',
            'last_page',
            'last_page_url',
            'links',
            'next_page_url',
            'path',
            'per_page',
            'prev_page_url',
            'to',
            'total'
        ];
        $userJsonStructure = [
            'id',
            'name',
            'email',
            'email_verified_at',
            'created_at',
            'updated_at',
            'admin'
        ];
        $taskJsonStructure = [
            'id',
            'user_id',
            'title',
            'description',
            'due_date',
            'created_at',
            'updated_at',
        ];

        return [
            'paginated tasks' => [
                'model' => 'tasks',
                'query' => '',
                'admin' => false,
                'expectedJsonStructure' => [
                    'data' => [$taskJsonStructure]
                ] + $commonPaginatedJsonStructure,
                'expectedStatus' => 200,
                'messageKey' => null
            ],
            'paginated tasks with user' => [
                'model' => 'tasks',
                'query' => '?with=user',
                'admin' => false,
                'expectedJsonStructure' => [
                    'data' => [
                        $taskJsonStructure + [['user' => [$userJsonStructure]]]
                    ]
                ] + $commonPaginatedJsonStructure,
                'expectedStatus' => 200,
                'messageKey' => null
            ],
            'paginated users' => [
                'model' => 'user',
                'query' => '',
                'admin' => false,
                'expectedJsonStructure' => [
                    'data' => [$userJsonStructure]
                ] + $commonPaginatedJsonStructure,
                'expectedStatus' => 200,
                'messageKey' => null
            ],
            'paginated users columns name with tasks' => [
                'model' => 'user',
                'query' => '?columns=name&with=tasks',
                'admin' => false,
                'expectedJsonStructure' => [
                    'data' => [[
                        'id',
                        'name',
                        'tasks' => [$taskJsonStructure]
                    ]],
                ] + $commonPaginatedJsonStructure,
                'expectedStatus' => 200,
                'messageKey' => null
            ],
            'non paginated' => [
                'model' => 'tasks',
                'query' => '?per_page=0',
                'admin' => false,
                'expectedJsonStructure' => [$taskJsonStructure],
                'expectedStatus' => 200,
                'messageKey' => null
            ],
            'unauthenticated' => [
                'model' => 'tasks',
                'query' => '',
                'admin' => false,
                'expectedJsonStructure' => null,
                'expectedStatus' => 401,
                'messageKey' => 'constants.messages.http_401'
            ]
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->baseRoute = '/api/';
        $this->tasks = Task::factory(10)->create();
        $this->users = User::all();
    }

    /**
     * A basic feature test example.
     *
     * @dataProvider listDataProvider
     *
     * @return void
     */
    public function testList($model, $query, $admin, $expectedJsonStructure, $expectedStatus, $messageKey)
    {
        $user = $this->users->firstWhere('admin', $admin);

        // Ensure that $user is not null before trying to create a token
        if ($user) {
            $token = $user->createToken('token-name')->plainTextToken;
        } else {
            $token = '';
        }

        $headers = [
            'Accept' => 'application/json',
        ];

        // Add Authorization header only if the status code is not expected to be 401
        if ($expectedStatus !== 401) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $response = $this->getJson($this->baseRoute . $model . '/' . $query, $headers);

        if ($messageKey) {
            // Assert response message
            $response->assertExactJson(['message' => config($messageKey)]);
        }

        if ($expectedJsonStructure) {
            // Assert response data structure
            $response->assertJsonStructure($expectedJsonStructure);
        }

        $response->assertStatus($expectedStatus);
    }
}

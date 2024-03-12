<?php

namespace Tests\Feature\app\Http\Controller\Api;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiControllerTest extends TestCase
{
    use DatabaseMigrations, RefreshDatabase;

    // Constants.
    const PAGINATED_JSON_STRUCTURE = [
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
    const TASK_JSON_STRUCTURE = [
        'id',
        'user_id',
        'title',
        'description',
        'due_date',
        'created_at',
        'updated_at',
    ];
    const USER_JSON_STRUCTURE = [
        'id',
        'name',
        'email',
        'email_verified_at',
        'created_at',
        'updated_at',
        'admin'
    ];

    // Attributes
    protected $baseRoute;
    protected $tasks;
    protected $users;

    // Test data providers

    /**
     * Data provider for list tests.
     *
     * @return array
     */
    public function listDataProvider()
    {
        return [
            'paginated tasks' => [
                'model' => 'tasks',
                'query' => '',
                'admin' => false,
                'expectedJsonStructure' => [
                    'data' => [self::TASK_JSON_STRUCTURE]
                ] + self::PAGINATED_JSON_STRUCTURE,
                'expectedStatus' => 200,
                'messageKey' => null
            ],
            'paginated tasks with user' => [
                'model' => 'tasks',
                'query' => '?with=user',
                'admin' => false,
                'expectedJsonStructure' => [
                    'data' => [
                        self::TASK_JSON_STRUCTURE + [['user' => [self::USER_JSON_STRUCTURE]]]
                    ]
                ] + self::PAGINATED_JSON_STRUCTURE,
                'expectedStatus' => 200,
                'messageKey' => null
            ],
            'paginated users' => [
                'model' => 'user',
                'query' => '',
                'admin' => false,
                'expectedJsonStructure' => [
                    'data' => [self::USER_JSON_STRUCTURE]
                ] + self::PAGINATED_JSON_STRUCTURE,
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
                        'tasks' => [self::TASK_JSON_STRUCTURE]
                    ]],
                ] + self::PAGINATED_JSON_STRUCTURE,
                'expectedStatus' => 200,
                'messageKey' => null
            ],
            'non paginated' => [
                'model' => 'tasks',
                'query' => '?per_page=0',
                'admin' => false,
                'expectedJsonStructure' => [self::TASK_JSON_STRUCTURE],
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

    /**
     * Data provider for show tests.
     *
     * @return array
     */
    public function showDataProvider()
    {
        return [
            'valid object' => [
                'model' => 'tasks',
                'query' => '',
                'admin' => false,
                'expectedJsonStructure' => self::TASK_JSON_STRUCTURE,
                'expectedStatus' => 200,
            ],
            'valid object columns' => [
                'model' => 'tasks',
                'query' => '?columns=id,description',
                'admin' => false,
                'expectedJsonStructure' => ['id', 'description'],
                'expectedStatus' => 200,
            ],
            'valid object association' => [
                'model' => 'tasks',
                'query' => '?with=user',
                'admin' => false,
                'expectedJsonStructure' => [
                    'user' => self::USER_JSON_STRUCTURE,
                ] + self::TASK_JSON_STRUCTURE,
                'expectedStatus' => 200,
            ],
            'object not found' => [
                'model' => 'tasks',
                'query' => '',
                'admin' => false,
                'expectedJsonStructure' => null,
                'expectedStatus' => 404,
            ],
            'unauthenticated' => [
                'model' => 'tasks',
                'query' => '',
                'admin' => false,
                'expectedJsonStructure' => null,
                'expectedStatus' => 401,
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
     * Test list endpoint.
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

        // Assert response message
        if ($messageKey) {
            $response->assertExactJson(['message' => config($messageKey)]);
        }

        // Assert response data structure
        if ($expectedJsonStructure) {
            $response->assertJsonStructure($expectedJsonStructure);
        }

        // Assert response status
        $response->assertStatus($expectedStatus);
    }

    /**
     * Test show endpoint.
     *
     * @dataProvider showDataProvider
     *
     * @return void
     */
    public function testShow($model, $query, $admin, $expectedJsonStructure, $expectedStatus)
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

        $id = 'null';

        if ($expectedStatus !== 404) {
            // Get random valid id
            $data = $model == 'user' ? $this->users : $this->tasks;
            $id = $data->random()->id;
        }

        $response = $this->getJson($this->baseRoute . $model . '/' . $id . '/' . $query, $headers);

        // Assert response data structure
        if ($expectedJsonStructure) {
            $response->assertJsonStructure($expectedJsonStructure);
        }

        // Assert response status
        $response->assertStatus($expectedStatus);
    }
}

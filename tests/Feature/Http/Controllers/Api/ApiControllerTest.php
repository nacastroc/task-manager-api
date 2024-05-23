<?php

namespace Tests\Feature\Http\Controller\Api;

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
                'admin' => true,
                'expectedJsonStructure' => [
                    'data' => [self::TASK_JSON_STRUCTURE]
                ] + self::PAGINATED_JSON_STRUCTURE,
                'expectedStatus' => 200,
                'messageKey' => null
            ],
            'paginated tasks and filter' => [
                'model' => 'tasks',
                'query' => '?filter=[user_id=1]',
                'admin' => true,
                'expectedJsonStructure' => [
                    'data' => [self::TASK_JSON_STRUCTURE]
                ] + self::PAGINATED_JSON_STRUCTURE,
                'expectedStatus' => 200,
                'messageKey' => null
            ],
            'non-admin cannot get paginated tasks without filter' => [
                'model' => 'tasks',
                'query' => '',
                'admin' => false,
                'expectedJsonStructure' => null,
                'expectedStatus' => 403,
                'messageKey' => 'constants.messages.http_403'
            ],
            'paginated tasks with user' => [
                'model' => 'tasks',
                'query' => '?with=user',
                'admin' => true,
                'expectedJsonStructure' => [
                    'data' => [
                        self::TASK_JSON_STRUCTURE + [['user' => [self::USER_JSON_STRUCTURE]]]
                    ]
                ] + self::PAGINATED_JSON_STRUCTURE,
                'expectedStatus' => 200,
                'messageKey' => null
            ],
            'admin can get paginated users' => [
                'model' => 'user',
                'query' => '',
                'admin' => true,
                'expectedJsonStructure' => [
                    'data' => [self::USER_JSON_STRUCTURE]
                ] + self::PAGINATED_JSON_STRUCTURE,
                'expectedStatus' => 200,
                'messageKey' => null
            ],
            'admin can get paginated users columns name with tasks' => [
                'model' => 'user',
                'query' => '?columns=name&with=tasks',
                'admin' => true,
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
            'non-admin cannot get paginated users' => [
                'model' => 'user',
                'query' => '',
                'admin' => false,
                'expectedJsonStructure' => null,
                'expectedStatus' => 403,
                'messageKey' => 'constants.messages.http_403'
            ],
            'non-admin cannot get paginated users columns name with tasks' => [
                'model' => 'user',
                'query' => '?columns=name&with=tasks',
                'admin' => false,
                'expectedJsonStructure' => null,
                'expectedStatus' => 403,
                'messageKey' => 'constants.messages.http_403'
            ],
            'non paginated' => [
                'model' => 'tasks',
                'query' => '?per_page=0',
                'admin' => true,
                'expectedJsonStructure' => [self::TASK_JSON_STRUCTURE],
                'expectedStatus' => 200,
                'messageKey' => null
            ],
            'non admin cannot get non paginated without filter' => [
                'model' => 'tasks',
                'query' => '?per_page=0',
                'admin' => false,
                'expectedJsonStructure' => null,
                'expectedStatus' => 403,
                'messageKey' => 'constants.messages.http_403'
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

    /**
     * Data provider for delete tests.
     *
     * @return array
     */
    public function deleteDataProvider()
    {
        return [
            'user delete by non-admin' => [
                'model' => 'user',
                'admin' => false,
                'expectedStatus' => 403,
                'messageKey' => 'constants.messages.http_403'
            ],
            'user cannot self-delete' => [
                'model' => 'user',
                'admin' => true,
                'expectedStatus' => 403,
                'messageKey' => 'constants.messages.http_403_self_delete'
            ],
            'admin can delete users' => [
                'model' => 'user',
                'admin' => true,
                'expectedStatus' => 200,
                'messageKey' => null
            ],
            'admin can delete tasks' => [
                'model' => 'tasks',
                'admin' => true,
                'expectedStatus' => 200,
                'messageKey' => null
            ],
            'user can delete his tasks' => [
                'model' => 'tasks',
                'admin' => false,
                'expectedStatus' => 200,
                'messageKey' => null
            ],
            'user cannot delete another user\'s tasks' => [
                'model' => 'tasks',
                'admin' => false,
                'expectedStatus' => 403,
                'messageKey' => 'constants.messages.http_403'
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
        $token = $user ? $user->createToken('token-name')->plainTextToken : '';

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
        $token = $user ? $user->createToken('token-name')->plainTextToken : '';

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
            $data = $model === 'user' ? $this->users : $this->tasks;
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

    /**
     * Test delete endpoint.
     *
     * @dataProvider deleteDataProvider
     */
    public function testDelete($model, $admin, $expectedStatus, $messageKey)
    {
        $user = $this->users->firstWhere('admin', $admin);

        // Ensure that $user is not null before trying to create a token
        $token = $user ? $user->createToken('token-name')->plainTextToken : '';

        $headers = [
            'Accept' => 'application/json',
        ];

        switch ($messageKey) {
            case 'constants.messages.http_403_self_delete':
                $data['ids'] = $this->users->firstWhere('id', $user->id)->id;
                break;
            case 'constants.messages.http_403':
                $ids = $model === 'user'
                    ? User::where('id', '!=', $user->id)->first()->id
                    : Task::where('user_id', '!=', $user->id)->first()->id;
                $data['ids'] = "{$ids}";
                break;
            default:
                $collection = $model === 'user'
                    ? User::where('id', '!=', $user->id)->limit(5)->get()->toArray()
                    : Task::where('user_id', '=', $user->id)->limit(5)->get()->toArray();
                $ids = array_map(function ($n) {
                    return $n['id'];
                }, $collection);
                $data['ids'] = implode(',', $ids);
                break;
        }

        // Add Authorization header only if the status code is not expected to be 401
        if ($expectedStatus !== 401) $headers['Authorization'] = 'Bearer ' . $token;

        $response = $this->deleteJson($this->baseRoute . $model, $data, $headers);

        // Assert response message
        if ($messageKey) {
            $response->assertExactJson(['message' => config($messageKey)]);
        }

        // Assert response status
        $response->assertStatus($expectedStatus);
    }
}

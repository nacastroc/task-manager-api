<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Task;
use App\Services\QueryService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new QueryService();
    }

    // Data set providers

    public function getModelInstanceForRouteProvider()
    {
        return [
            'user route returns User instance' => ['user', User::class],
            'tasks route returns Task instance' => ['tasks', Task::class],
            'unknown route returns null' => ['unknown', null],
        ];
    }

    public function validColumnsProvider()
    {
        return [
            'users table returns valid string columns' => [
                'users', 'string',
                ['name', 'email', 'password', 'remember_token']
            ],
            'users table returns valid types array columns' => [
                'users', ['bigint', 'boolean'],
                ['id', 'admin']
            ],
            'users table returns all columns on null type' => [
                'users', null,
                ['id', 'name', 'email', 'email_verified_at', 'password', 'remember_token', 'created_at', 'updated_at', 'admin']
            ],
            'throws error on invalid types argument' => [
                'users', [1,2],
                null
            ],
        ];
    }

    public function appendKeysToSelectProvider()
    {
        return [
            'users table returns adds id to selected columns' => [
                'users', ['name', 'email'],
                ['name', 'email', 'id']
            ],
            'tasks table returns adds id, user_id to selected columns' => [
                'tasks', ['title'],
                ['title', 'id', 'user_id']
            ],
        ];
    }

    public function setColumnValueTypeProvider()
    {
        return [
            'integer column type returns integer value' => [
                'users', 'id', '123', 123
            ],
            'float column type returns float value' => [
                'users', 'balance', '12.34', 12.34
            ],
            'boolean column type returns boolean value' => [
                'users', 'is_active', '1', true
            ],
            'string column type returns string value' => [
                'users', 'name', 'John Doe', 'John Doe'
            ],
            'datetime column type returns string value' => [
                'users', 'created_at', '2020-12-01', '2020-12-01'
            ],
            'invalid column throws exception' => [
                'users', 'number', '2020-12-01', '2020-12-01'
            ],
        ];
    }

    // Test cases

    /**
     * @dataProvider getModelInstanceForRouteProvider
     */
    public function test_getModelInstanceForRoute($route, $expectedInstance)
    {
        // Act
        $model = $this->service->getModelInstanceForRoute($route);

        // Assert
        if ($expectedInstance === null) {
            $this->assertNull($model);
        } else {
            $this->assertInstanceOf($expectedInstance, $model);
        }
    }

    /**
     * @dataProvider validColumnsProvider
     */
    public function test_getValidColumns($table, $type, $expectedColumns)
    {
        try {
            $actualColumns = $this->service->getValidColumns($table, $type);
        } catch (\Throwable $th) {
            $this->expectException(Exception::class);
            $this->assertEquals($th->getMessage(), 'Invalid type. Type must be a string or an array of strings.');
        }
        $this->assertEquals($expectedColumns, $actualColumns);
    }

    /**
     * @dataProvider appendKeysToSelectProvider
     */
    public function test_appendKeysToSelect($table, $columns, $expectedColumns)
    {
        try {
            $validColumns = $this->service->getValidColumns($table);
            $actualColumns = $this->service->appendKeysToSelect($validColumns, $columns);
        } catch (\Throwable $th) {
            $this->expectException(Exception::class);
            $this->assertEquals($th->getMessage(), 'Invalid type. Type must be a string or an array of strings.');
        }
        $this->assertEquals($expectedColumns, $actualColumns);
    }

    /**
     * @dataProvider setColumnValueTypeProvider
     */
    public function test_setColumnValueType($table, $column, $value, $expectedResult)
    {
        // Act
        try {
            $result = $this->service->setColumnValueType($table, $column, $value);
        } catch (\Throwable $th) {
            $this->expectException(Exception::class);
            $this->assertEquals($th->getMessage(), 'No column of given name found on table.');
        }

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

}

<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Task;
use App\Services\QueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueryServiceTest extends TestCase
{
    use RefreshDatabase;

    // Data set providers

    public function getModelInstanceForRouteProvider()
    {
        return [
            'user route returns User instance' => ['user', User::class],
            'tasks route returns Task instance' => ['tasks', Task::class],
            'unknown route returns null' => ['unknown', null],
        ];
    }

    // Test cases

    /**
     * @dataProvider getModelInstanceForRouteProvider
     */
    public function test_getModelInstanceForRoute($route, $expectedInstance)
    {
        // Arrange
        $service = new QueryService();

        // Act
        $model = $service->getModelInstanceForRoute($route);

        // Assert
        if ($expectedInstance === null) {
            $this->assertNull($model);
        } else {
            $this->assertInstanceOf($expectedInstance, $model);
        }
    }
}

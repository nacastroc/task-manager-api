<?php

namespace Tests\Unit\Http\Middleware\Api;

use App\Http\Middleware\Api\AddModelInstance;
use App\Services\QueryService;
use App\Models\Task;
use PHPUnit\Framework\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;

class AddModelInstanceTest extends TestCase
{
    /** @test */
    public function it_adds_model_instance_to_request_data()
    {
        // Mock the service
        $serviceMock = Mockery::mock(QueryService::class);

        // Set up expectation for the getModelInstanceForRoute() method
        $serviceMock->shouldReceive('getModelInstanceForRoute')
            ->andReturn(Task::class); // You can return whatever is appropriate for your test

        // Mock the request and response
        $request = Request::create('/api/tasks', 'GET');
        $response = new Response();

        // Create an instance of your middleware
        $middleware = new AddModelInstance($serviceMock);

        // Execute the middleware
        $result = $middleware->handle($request, function () use ($response) {
            // Mock the closure call, if any
            return $response;
        });

        // Assert that the middleware returned a response
        $this->assertInstanceOf(Response::class, $result);

        // Assert that the response status code is 403 (or any other expected code)
        $this->assertEquals(200, $result->getStatusCode());

        // sert the required model instance is added to the request
        $this->assertEquals(Task::class, $request->input('data-model'));
    }
}

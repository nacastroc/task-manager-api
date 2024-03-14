<?php

namespace Tests\Feature\Http\Controller\Api;

use Tests\TestCase;

class PublicControllerTest extends TestCase
{
    protected $composer;
    protected $data;

    protected function setUp(): void
    {
        parent::setUp();

        $this->composer = json_decode(file_get_contents(base_path('composer.json')), true);

        $this->data = [
            'app' => 'Task Manager API',
            'version' => $this->composer['version'],
            'overview' => 'The TaskManager API is a simple task management system that allows users to create, update, delete, and retrieve tasks. Users need to authenticate to access the API.',
            'author' => 'Nestor Castro',
            'profile' => 'https://github.com/nacastroc/',
            'repository' => 'https://github.com/nacastroc/task-manager-api',
        ];
    }

    /**
     * Test the default endpoint as json.
     *
     * @return void
     */
    public function testWelcomeAsJson()
    {
        $response = $this->getJson('/', ['Accept' => 'application/json']);

        $response->assertStatus(200) // 200 Success
            ->assertExactJson($this->data);
    }

    /**
     * Test the default endpoint as web view.
     *
     * @return void
     */
    public function testWelcomeAsHtml()
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertViewHasAll($this->data); // 200 Success
    }
}

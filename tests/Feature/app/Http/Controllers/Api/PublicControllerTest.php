<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicControllerTest extends TestCase
{
    /**
     * Test the vesion endpoint.
     *
     * @return void
     */
    public function testVersion()
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);

        $response = $this->get('/api/version');

        $response->assertStatus(200) // 200 Success
            ->assertJsonStructure(['version'])
            ->assertExactJson([
                'version' => $composer['version']
            ]);
    }
}

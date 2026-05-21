<?php

namespace Tests\Feature;

use Tests\TestCase;

class SwaggerDocsTest extends TestCase
{
    /**
     * Test that the Swagger UI page loads successfully.
     */
    public function test_swagger_ui_loads(): void
    {
        $response = $this->get('/api/v1/docs');

        $response->assertStatus(200)
            ->assertSeeHtml('id="swagger-ui"')
            ->assertSeeHtml('openapi.json');
    }

    /**
     * Test that the OpenAPI JSON specification is accessible and is valid JSON.
     */
    public function test_openapi_json_is_accessible(): void
    {
        $response = $this->get('/openapi.json');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonPath('openapi', '3.0.0')
            ->assertJsonPath('info.title', 'CivicPulse RESTful API');
    }
}

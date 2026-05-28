<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminWebRoutesTest extends TestCase
{
    /**
     * Test that the Admin Login page loads successfully.
     */
    public function test_admin_login_page_loads(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200)
            ->assertSeeHtml('Login Admin - CivicPulse');
    }

    /**
     * Test that the Admin Dashboard page loads successfully.
     */
    public function test_admin_dashboard_page_loads(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200)
            ->assertSeeHtml('Dashboard Admin - CivicPulse');
    }
}

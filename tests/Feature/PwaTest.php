<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    public function test_manifest_describes_an_installable_app(): void
    {
        $this->get(route('pwa.manifest'))
            ->assertOk()
            ->assertHeader('content-type', 'application/manifest+json')
            ->assertJsonPath('name', config('app.name'))
            ->assertJsonPath('short_name', 'KP SDS')
            ->assertJsonPath('start_url', '/')
            ->assertJsonPath('scope', '/')
            ->assertJsonPath('display', 'standalone')
            ->assertJsonPath('theme_color', '#0e221f')
            ->assertJsonPath('icons.0.sizes', '192x192')
            ->assertJsonPath('icons.1.sizes', '512x512')
            ->assertJsonPath('icons.2.purpose', 'maskable');

        $this->assertFileExists(public_path('icons/icon-192.png'));
        $this->assertFileExists(public_path('icons/icon-512.png'));
        $this->assertFileExists(public_path('icons/apple-touch-icon.png'));
    }

    public function test_service_worker_caches_static_files_only(): void
    {
        $this->get(route('pwa.service-worker'))
            ->assertOk()
            ->assertHeader('content-type', 'application/javascript; charset=UTF-8')
            ->assertHeader('service-worker-allowed', '/')
            ->assertSee("addEventListener('fetch'", false)
            ->assertSee("request.method !== 'GET'", false)
            ->assertSee('/build/', false)
            ->assertSee('/icons/', false)
            ->assertSee('/images/', false);
    }

    public function test_guest_and_app_layouts_link_the_manifest(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('rel="manifest"', false)
            ->assertSee(route('pwa.manifest'), false)
            ->assertSee('apple-touch-icon', false)
            ->assertSee('apple-mobile-web-app-capable', false);

        $this->actingAs(User::factory()->create())
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('rel="manifest"', false)
            ->assertSee(route('pwa.manifest'), false);
    }
}

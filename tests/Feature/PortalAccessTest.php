<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PortalAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/login')->assertOk();
    }

    public function test_client_can_log_in_and_see_home(): void
    {
        $user = $this->client();
        $this->post('/login', ['email' => $user->email, 'password' => 'password123abc'])->assertRedirect('/');
        $this->get('/')->assertOk()->assertInertia(fn (Assert $p) => $p->component('Portal/Home')->has('units'));
        $this->assertDatabaseHas('audit_logs', ['user_id' => $user->id, 'action' => 'login']);
    }

    public function test_inactive_user_cannot_log_in_and_is_logged_out(): void
    {
        $user = $this->client();
        $user->update(['is_active' => false]);
        $this->post('/login', ['email' => $user->email, 'password' => 'password123abc'])->assertSessionHasErrors('email');
        $this->actingAs($user)->get('/')->assertRedirect('/login');
    }

    public function test_client_cannot_see_another_institutions_project(): void
    {
        $mine = $this->client();
        $other = Project::create(['institution_id' => $this->institution()->id, 'name' => 'Ajeno']);
        $this->actingAs($mine)->get("/proyectos/{$other->id}")->assertNotFound();
    }

    public function test_research_requires_contracted_unit(): void
    {
        $without = $this->client($this->institution(['BCS']));
        $this->actingAs($without)->get('/research')->assertRedirect('/');

        $with = $this->client($this->institution(['RESEARCH']));
        $this->actingAs($with)->get('/research')->assertOk();
    }

    public function test_client_cannot_open_cms_and_admin_can(): void
    {
        $this->actingAs($this->client())->get('/admin')->assertForbidden();
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/')->assertRedirect('/admin');
    }

    public function test_every_portal_page_renders(): void
    {
        $user = $this->client($this->institution(['BCS', 'RESEARCH']), 'client_admin');
        foreach (['/', '/proyectos', '/entregables', '/research', '/academia', '/facturacion', '/bolsa-de-horas', '/credencial', '/perfil'] as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }
}

<?php

namespace Tests\Feature;

use App\Models\HourMovement;
use App\Models\HourRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HoursTest extends TestCase
{
    use RefreshDatabase;

    private array $payload = [
        'mode' => 'consulta', 'specialty' => 'Mercado y datos', 'estimated_hours' => 2,
        'description' => 'Necesitamos una revisión de los umbrales del piloto.',
    ];

    public function test_request_cannot_exceed_balance(): void
    {
        $user = $this->client();
        HourMovement::create(['institution_id' => $user->institution_id, 'hours' => 1, 'description' => 'Bolsa']);
        $this->actingAs($user)->post('/bolsa-de-horas', $this->payload)->assertSessionHasErrors('estimated_hours');
    }

    public function test_done_request_deducts_hours_once(): void
    {
        $user = $this->client();
        HourMovement::create(['institution_id' => $user->institution_id, 'hours' => 10, 'description' => 'Bolsa']);
        $this->actingAs($user)->post('/bolsa-de-horas', $this->payload)->assertSessionHas('success');
        $req = HourRequest::firstOrFail();

        $admin = User::factory()->admin()->create();
        $done = ['status' => 'done', 'charged_hours' => 1.5, 'response' => 'Atendido'];
        $this->actingAs($admin)->put("/admin/solicitudes-horas/{$req->id}", $done)->assertSessionHas('success');
        $this->actingAs($admin)->put("/admin/solicitudes-horas/{$req->id}", $done);

        $this->assertSame(8.5, $user->institution->fresh()->hourBalance());
    }
}

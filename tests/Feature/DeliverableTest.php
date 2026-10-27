<?php

namespace Tests\Feature;

use App\Models\Deliverable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliverableTest extends TestCase
{
    use RefreshDatabase;

    private function pending($inst): Deliverable
    {
        return Deliverable::create([
            'institution_id' => $inst->id, 'title' => 'Informe v2', 'status' => 'pending_approval',
            'sha256' => hash('sha256', 'contenido'), 'external_url' => 'https://drive.google.com/x',
        ]);
    }

    public function test_approval_requires_correct_password(): void
    {
        $user = $this->client();
        $d = $this->pending($user->institution);

        $this->actingAs($user)->post("/entregables/{$d->id}/aprobar", ['password' => 'incorrecta'])
            ->assertSessionHasErrors('password');
        $this->assertSame('pending_approval', $d->fresh()->status);
    }

    public function test_approval_is_recorded_with_hash(): void
    {
        $user = $this->client();
        $d = $this->pending($user->institution);

        $this->actingAs($user)->post("/entregables/{$d->id}/aprobar", ['password' => 'password123abc'])
            ->assertSessionHas('success');

        $d->refresh();
        $this->assertSame('approved', $d->status);
        $this->assertSame($user->id, $d->approved_by);
        $this->assertNotNull($d->approval_ip);
        $this->assertDatabaseHas('audit_logs', ['action' => 'deliverable.approve', 'subject_id' => $d->id]);
    }

    public function test_cannot_approve_other_institution_or_drafts(): void
    {
        $user = $this->client();
        $foreign = $this->pending($this->institution());
        $this->actingAs($user)->post("/entregables/{$foreign->id}/aprobar", ['password' => 'password123abc'])->assertNotFound();

        $draft = Deliverable::create(['institution_id' => $user->institution_id, 'title' => 'B', 'status' => 'draft']);
        $this->actingAs($user)->get("/entregables/{$draft->id}/descargar")->assertNotFound();
    }

    public function test_verify_by_hash_only_matches_own_documents(): void
    {
        $user = $this->client();
        $this->pending($user->institution);
        $hash = hash('sha256', 'contenido');

        $this->actingAs($user)->post('/entregables/verificar', ['sha256' => $hash])
            ->assertSessionHas('verification', fn ($v) => $v['ok'] === true);

        $other = $this->client();
        $this->actingAs($other)->post('/entregables/verificar', ['sha256' => $hash])
            ->assertSessionHas('verification', fn ($v) => $v['ok'] === false);

        $this->actingAs($user)->post('/entregables/verificar', ['sha256' => 'no-es-hash'])->assertSessionHasErrors('sha256');
    }
}

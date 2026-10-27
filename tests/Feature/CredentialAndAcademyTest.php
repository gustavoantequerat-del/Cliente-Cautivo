<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CredentialAndAcademyTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_verification_reflects_revocation(): void
    {
        $user = $this->client();
        $this->assertNotNull($user->credential_code);

        $this->get("/verificar/{$user->credential_code}")
            ->assertInertia(fn (Assert $p) => $p->component('Public/VerifyCredential')->where('credential.valid', true));

        $user->update(['is_active' => false]);
        $this->get("/verificar/{$user->credential_code}")
            ->assertInertia(fn (Assert $p) => $p->where('credential.valid', false));
    }

    public function test_marking_video_as_seen_awards_points_once(): void
    {
        $user = $this->client();
        $video = Video::create(['title' => 'Píldora', 'kind' => 'pill', 'points' => 10,
            'source_url' => 'https://drive.google.com/file/d/1AbCdEfGhIjKlMnOp/view']);

        $this->assertSame('gdrive', $video->provider);
        $this->actingAs($user)->get("/academia/{$video->id}")
            ->assertInertia(fn (Assert $p) => $p->where('video.embedUrl', 'https://drive.google.com/file/d/1AbCdEfGhIjKlMnOp/preview'));

        $this->actingAs($user)->post("/academia/{$video->id}/visto");
        $this->actingAs($user)->post("/academia/{$video->id}/visto");
        $this->assertSame(10, $user->institution->fresh()->alliance_points);
    }

    public function test_exclusive_video_hidden_from_other_institutions(): void
    {
        $video = Video::create(['title' => 'Privado', 'institution_id' => $this->institution()->id]);
        $this->actingAs($this->client())->get("/academia/{$video->id}")->assertNotFound();
    }

    public function test_cms_rejects_unknown_video_links_and_saves_valid_ones(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->post('/admin/videos', ['kind' => 'recorded', 'title' => 'X', 'source_url' => 'https://example.com/v.mp4'])
            ->assertSessionHasErrors('source_url');

        $this->actingAs($admin)->post('/admin/videos', ['kind' => 'recorded', 'title' => 'Y', 'source_url' => 'https://youtu.be/dQw4w9WgXcQ', 'is_published' => 1])
            ->assertRedirect('/admin/videos');
        $this->assertDatabaseHas('videos', ['title' => 'Y', 'provider' => 'youtube', 'external_id' => 'dQw4w9WgXcQ']);
    }

    public function test_cms_creates_institution_with_units(): void
    {
        $admin = User::factory()->admin()->create();
        $unit = \App\Models\BusinessUnit::create(['code' => 'BCS', 'name' => 'Compliance']);
        $this->actingAs($admin)->post('/admin/instituciones', [
            'name' => 'Cooperativa X', 'slug' => 'coop-x', 'is_active' => 1, 'alliance_points' => 0,
            'units' => [$unit->id => ['on' => 1, 'note' => '10 licencias']],
        ])->assertRedirect('/admin/instituciones');

        $inst = \App\Models\Institution::where('slug', 'coop-x')->firstOrFail();
        $this->assertTrue($inst->load('units')->hasUnit('BCS'));
    }
}

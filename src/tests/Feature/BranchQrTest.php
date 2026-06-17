<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchQrTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_download_branch_qr_png(): void
    {
        $branch = Branch::create(['name' => 'Sede Centro', 'city' => 'Cartagena', 'slug' => 'sede-centro']);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.branches.qr', $branch));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
        $this->assertSame("\x89PNG\r\n\x1A\n", substr($response->getContent(), 0, 8));
    }

    public function test_authenticated_user_can_preview_branch_qr_svg(): void
    {
        $branch = Branch::create(['name' => 'Sede Centro', 'city' => 'Cartagena', 'slug' => 'sede-centro']);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.branches.qr-preview', $branch));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/svg+xml');
        $response->assertSee('<svg', false);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchSlugTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_branch_generates_a_slug(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.branches.store'), [
            'name' => 'Sede Los Ejecutivos',
            'city' => 'Cartagena',
        ])->assertRedirect(route('admin.branches.index'));

        $branch = Branch::where('name', 'Sede Los Ejecutivos')->first();

        $this->assertNotNull($branch);
        $this->assertSame('sede-los-ejecutivos', $branch->slug);
    }

    public function test_duplicate_branch_names_get_unique_slugs(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.branches.store'), [
            'name' => 'Sede Centro',
            'city' => 'Cartagena',
        ]);
        $this->actingAs($user)->post(route('admin.branches.store'), [
            'name' => 'Sede Centro',
            'city' => 'Monteria',
        ]);

        $slugs = Branch::where('name', 'Sede Centro')->orderBy('id')->pluck('slug');

        $this->assertSame(['sede-centro', 'sede-centro-1'], $slugs->all());
    }
}

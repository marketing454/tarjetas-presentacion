<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BranchPhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_branch_with_a_photo_stores_file_and_position(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('sede.jpg', 800, 600);

        $this->actingAs($user)->post(route('admin.branches.store'), [
            'name' => 'Sede Centro',
            'city' => 'Cartagena',
            'photo' => $file,
            'photo_position_x' => '30',
            'photo_position_y' => '70',
        ])->assertRedirect(route('admin.branches.index'));

        $branch = Branch::where('name', 'Sede Centro')->first();

        $this->assertNotNull($branch->photo);
        $this->assertSame(30, $branch->photo_position_x);
        $this->assertSame(70, $branch->photo_position_y);
        Storage::disk('public')->assertExists($branch->photo);
    }

    public function test_branch_without_photo_defaults_position_to_center(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.branches.store'), [
            'name' => 'Sede Sin Foto',
            'city' => 'Cartagena',
        ]);

        $branch = Branch::where('name', 'Sede Sin Foto')->first();

        $this->assertSame(50, $branch->photo_position_x);
        $this->assertSame(50, $branch->photo_position_y);
    }

    public function test_removing_photo_deletes_file_and_clears_column(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $branch = Branch::create([
            'name' => 'Sede Centro',
            'city' => 'Cartagena',
            'slug' => 'sede-centro',
            'photo' => 'branches/old.jpg',
        ]);
        Storage::disk('public')->put('branches/old.jpg', 'fake');

        $this->actingAs($user)->put(route('admin.branches.update', $branch), [
            'name' => 'Sede Centro',
            'city' => 'Cartagena',
            'remove_photo' => '1',
        ])->assertRedirect(route('admin.branches.index'));

        $branch->refresh();
        $this->assertNull($branch->photo);
        Storage::disk('public')->assertMissing('branches/old.jpg');
    }

    public function test_public_branch_card_shows_photo_with_object_position_when_present(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('branches/sede.jpg', 'fake');
        $branch = Branch::create([
            'name' => 'Sede Centro',
            'city' => 'Cartagena',
            'slug' => 'sede-centro',
            'photo' => 'branches/sede.jpg',
            'photo_position_x' => 20,
            'photo_position_y' => 80,
        ]);

        $this->get(route('branch.show', $branch->slug))
            ->assertOk()
            ->assertSee('object-position: 20% 80%', false)
            ->assertDontSee('fa-building', false);
    }

    public function test_public_branch_card_shows_building_icon_without_photo(): void
    {
        $branch = Branch::create(['name' => 'Sede Centro', 'city' => 'Cartagena', 'slug' => 'sede-centro']);

        $this->get(route('branch.show', $branch->slug))
            ->assertOk()
            ->assertSee('fa-building', false);
    }
}

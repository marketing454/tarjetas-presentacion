<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchPhotoCarouselTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_branch_with_photo_urls_stores_them_in_order(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.branches.store'), [
            'name' => 'Sede Centro',
            'city' => 'Cartagena',
            'photos' => [
                'https://cdn.example.com/sede-1.jpg',
                'https://cdn.example.com/sede-2.jpg',
            ],
        ])->assertRedirect(route('admin.branches.index'));

        $branch = Branch::where('name', 'Sede Centro')->first();
        $urls = $branch->photos()->orderBy('position')->pluck('url');

        $this->assertSame([
            'https://cdn.example.com/sede-1.jpg',
            'https://cdn.example.com/sede-2.jpg',
        ], $urls->all());
    }

    public function test_blank_photo_url_rows_are_discarded(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.branches.store'), [
            'name' => 'Sede Centro',
            'city' => 'Cartagena',
            'photos' => ['https://cdn.example.com/sede-1.jpg', '', null],
        ]);

        $branch = Branch::where('name', 'Sede Centro')->first();
        $this->assertSame(1, $branch->photos()->count());
    }

    public function test_updating_with_a_shorter_url_list_replaces_all_photos(): void
    {
        $user = User::factory()->create();
        $branch = Branch::create(['name' => 'Sede Centro', 'city' => 'Cartagena', 'slug' => 'sede-centro']);
        BranchPhoto::create(['branch_id' => $branch->id, 'url' => 'https://cdn.example.com/old-1.jpg', 'position' => 0]);
        BranchPhoto::create(['branch_id' => $branch->id, 'url' => 'https://cdn.example.com/old-2.jpg', 'position' => 1]);

        $this->actingAs($user)->put(route('admin.branches.update', $branch), [
            'name' => 'Sede Centro',
            'city' => 'Cartagena',
            'photos' => ['https://cdn.example.com/new-1.jpg'],
        ])->assertRedirect(route('admin.branches.index'));

        $branch->refresh();
        $this->assertSame(['https://cdn.example.com/new-1.jpg'], $branch->photos()->pluck('url')->all());
    }

    public function test_public_branch_card_renders_carousel_when_photos_exist(): void
    {
        $branch = Branch::create(['name' => 'Sede Centro', 'city' => 'Cartagena', 'slug' => 'sede-centro']);
        BranchPhoto::create(['branch_id' => $branch->id, 'url' => 'https://cdn.example.com/sede-1.jpg', 'position' => 0]);

        $this->get(route('branch.show', $branch->slug))
            ->assertOk()
            ->assertSee('https://cdn.example.com/sede-1.jpg', false)
            ->assertSee('sede-carousel-track', false);
    }

    public function test_public_branch_card_omits_carousel_section_without_photos(): void
    {
        $branch = Branch::create(['name' => 'Sede Centro', 'city' => 'Cartagena', 'slug' => 'sede-centro']);

        $this->get(route('branch.show', $branch->slug))
            ->assertOk()
            ->assertDontSee('sede-carousel-track', false);
    }
}

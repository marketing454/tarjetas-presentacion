<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_branch_card_lists_its_employees(): void
    {
        $branch = Branch::create(['name' => 'Sede Centro', 'city' => 'Cartagena', 'slug' => 'sede-centro']);
        Employee::create([
            'branch_id' => $branch->id,
            'name' => 'Ana Gomez',
            'position' => 'Asesora Comercial',
            'card_type' => Employee::CARD_TYPE_NORMAL,
            'whatsapp' => '3000000000',
            'slug' => 'ana-gomez',
        ]);

        $this->get(route('branch.show', $branch->slug))
            ->assertOk()
            ->assertSee('Sede Centro')
            ->assertSee('Ana Gomez')
            ->assertSee('Asesora Comercial')
            ->assertSee('wa.me/3000000000', false);
    }

    public function test_branch_card_shows_empty_state_with_no_employees(): void
    {
        $branch = Branch::create(['name' => 'Sede Vacia', 'city' => 'Cartagena', 'slug' => 'sede-vacia']);

        $this->get(route('branch.show', $branch->slug))
            ->assertOk()
            ->assertSee('Aún no hay asesores asignados a esta sede.');
    }

    public function test_unknown_branch_slug_returns_404(): void
    {
        $this->get('/sede/no-existe')->assertNotFound();
    }

    public function test_visiting_branch_card_records_a_scan(): void
    {
        $branch = Branch::create(['name' => 'Sede Centro', 'city' => 'Cartagena', 'slug' => 'sede-centro']);

        $this->get(route('branch.show', $branch->slug))->assertOk();

        $this->assertDatabaseHas('card_scans', [
            'branch_id' => $branch->id,
            'employee_id' => null,
        ]);
    }
}

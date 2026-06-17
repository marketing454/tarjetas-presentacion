<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\CardScan;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_metrics_dashboard_shows_top_branches_alongside_top_employees(): void
    {
        $user = User::factory()->create();
        $branch = Branch::create(['name' => 'Sede Centro', 'city' => 'Cartagena', 'slug' => 'sede-centro']);
        $employee = Employee::create([
            'branch_id' => $branch->id,
            'name' => 'Ana Gomez',
            'position' => 'Asesora Comercial',
            'card_type' => Employee::CARD_TYPE_NORMAL,
            'slug' => 'ana-gomez',
        ]);

        CardScan::create(['employee_id' => $employee->id]);
        CardScan::create(['branch_id' => $branch->id]);
        CardScan::create(['branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(route('admin.metrics'));

        $response->assertOk();
        $response->assertSee('Top sedes');
        $response->assertSee('Sede Centro');
        $response->assertSee('Ana Gomez');
    }
}

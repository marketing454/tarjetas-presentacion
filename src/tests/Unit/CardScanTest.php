<?php

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\CardScan;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CardScanTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_for_employee_creates_scan_with_employee_id_only(): void
    {
        $branch = Branch::create(['name' => 'Centro', 'city' => 'Cartagena', 'slug' => 'centro']);
        $employee = Employee::create([
            'branch_id' => $branch->id,
            'name' => 'Juan Perez',
            'position' => 'Asesor',
            'card_type' => Employee::CARD_TYPE_NORMAL,
            'slug' => 'juan-perez',
        ]);

        CardScan::recordForEmployee($employee->id, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', null);

        $scan = CardScan::first();
        $this->assertNotNull($scan);
        $this->assertSame($employee->id, $scan->employee_id);
        $this->assertNull($scan->branch_id);
    }

    public function test_record_for_branch_creates_scan_with_branch_id_only(): void
    {
        $branch = Branch::create(['name' => 'Centro', 'city' => 'Cartagena', 'slug' => 'centro']);

        CardScan::recordForBranch($branch->id, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', null);

        $scan = CardScan::first();
        $this->assertNotNull($scan);
        $this->assertNull($scan->employee_id);
        $this->assertSame($branch->id, $scan->branch_id);
    }
}

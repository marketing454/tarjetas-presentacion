<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\CardTypeBanner;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_card_uses_employee_card_type(): void
    {
        $employee = $this->makeEmployee([
            'card_type' => Employee::CARD_TYPE_CORPORATE,
        ]);

        $this->get(route('card.show', $employee->slug))
            ->assertOk()
            ->assertSee($employee->name)
            ->assertSee('Asesor corporativo')
            ->assertSee('Logo-compulago-corporativo.png');
    }

    public function test_public_card_uses_commercial_logo_for_normal_and_credit_types(): void
    {
        $normalEmployee = $this->makeEmployee([
            'name' => 'Normal Advisor',
            'slug' => 'normal-advisor',
            'card_type' => Employee::CARD_TYPE_NORMAL,
        ]);
        $creditEmployee = $this->makeEmployee([
            'name' => 'Credit Advisor',
            'slug' => 'credit-advisor',
            'card_type' => Employee::CARD_TYPE_CREDIT,
        ]);

        $this->get(route('card.show', $normalEmployee->slug))
            ->assertOk()
            ->assertSee('Asesor Comercial')
            ->assertDontSee('Asesor normal')
            ->assertDontSee('emp-position')
            ->assertSee('Logo-Compulago.png')
            ->assertDontSee('Logo-compulago-corporativo.png');

        $this->get(route('card.show', $creditEmployee->slug))
            ->assertOk()
            ->assertSee('Logo-Compulago.png')
            ->assertDontSee('Logo-compulago-corporativo.png');
    }

    public function test_public_card_uses_exact_branch_maps_url_when_available(): void
    {
        $mapsUrl = 'https://maps.app.goo.gl/compulago-test';
        $employee = $this->makeEmployee(branchOverrides: [
            'maps_url' => $mapsUrl,
        ]);

        $this->get(route('card.show', $employee->slug))
            ->assertOk()
            ->assertSee('href="' . $mapsUrl . '"', false);
    }

    public function test_public_card_uses_default_banner_for_employee_card_type(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('card-type-banners/normal.jpg', 'banner');
        CardTypeBanner::create([
            'card_type' => Employee::CARD_TYPE_NORMAL,
            'banner_path' => 'card-type-banners/normal.jpg',
        ]);

        $employee = $this->makeEmployee();

        $this->get(route('card.show', $employee->slug))
            ->assertOk()
            ->assertSee('card-type-banners/normal.jpg');
    }

    public function test_authenticated_user_can_open_default_banners_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.card-banners.index'))
            ->assertOk()
            ->assertSee('Banners por tipo de asesor')
            ->assertSee('Asesor Comercial')
            ->assertSee('Asesor de credito')
            ->assertSee('Asesor corporativo');
    }

    public function test_authenticated_user_can_upload_default_banner(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('banner-normal.jpg', 900, 420);

        $this->actingAs($user)
            ->post(route('admin.card-banners.update'), [
                'banners' => [
                    Employee::CARD_TYPE_NORMAL => $file,
                ],
            ])
            ->assertRedirect(route('admin.card-banners.index'));

        $banner = CardTypeBanner::where('card_type', Employee::CARD_TYPE_NORMAL)->first();

        $this->assertNotNull($banner);
        Storage::disk('public')->assertExists($banner->banner_path);
    }

    public function test_authenticated_user_can_download_employee_qr_png(): void
    {
        $employee = $this->makeEmployee();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('admin.employees.qr', $employee));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
        $this->assertSame("\x89PNG\r\n\x1A\n", substr($response->getContent(), 0, 8));
    }

    public function test_authenticated_user_can_preview_employee_qr_svg(): void
    {
        $employee = $this->makeEmployee();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('admin.employees.qr-preview', $employee));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/svg+xml');
        $response->assertSee('<svg', false);
    }

    private function makeEmployee(array $overrides = [], array $branchOverrides = []): Employee
    {
        $branch = Branch::create(array_merge([
            'name' => 'Principal',
            'city' => 'Cucuta',
            'address' => 'Avenida 1 #2-3',
            'phone' => '3000000000',
        ], $branchOverrides));

        return Employee::create(array_merge([
            'branch_id' => $branch->id,
            'name' => 'Juan Perez',
            'position' => 'Asesor Comercial',
            'card_type' => Employee::CARD_TYPE_NORMAL,
            'whatsapp' => '3000000000',
            'slug' => 'juan-perez',
        ], $overrides));
    }
}

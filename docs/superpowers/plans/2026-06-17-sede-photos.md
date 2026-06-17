# Foto principal y carrusel de fotos de sede Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let admins upload a main photo for a branch (with drag-to-reposition framing) and attach a list of externally-hosted photo URLs that render as a carousel on the branch's public card.

**Architecture:** Extend `Branch` with photo/position columns and a `hasMany` `BranchPhoto` relation. The admin photo-position picker reuses the exact `background-image`/`background-position` preview pattern already used for employee photos, driven by a small vanilla-JS drag handler that writes percentages into two hidden inputs. The public card renders the photo with CSS `object-position` (percentage-equivalent to `background-position`) and a vanilla-JS prev/next/dots carousel for the externally-hosted photos — no new dependencies.

**Tech Stack:** Laravel 13, PHP 8.3, MySQL 8 (dev/prod), SQLite in-memory (tests, per `phpunit.xml`), Bootstrap 5 (admin), vanilla Blade/CSS/JS (public card, no client-side framework or carousel library).

## Global Constraints

- Spec: `docs/superpowers/specs/2026-06-17-sede-photos-design.md` — read it before starting.
- No `doctrine/dbal` is installed. Do not use `->change()` on any migration column.
- Tests run against SQLite in-memory (`phpunit.xml`). Every migration must be valid on both MySQL and SQLite.
- Photo upload validation mirrors the existing employee photo pattern exactly: `nullable|image|mimes:jpeg,png,jpg,webp|max:5120`.
- Carousel photo URLs are never downloaded or stored as files — only the URL string is persisted.
- Follow existing code style: no docblocks/comments beyond what's already in each file, Spanish copy for user-facing text, English code identifiers.
- Run tests with `docker exec team_app php artisan test` (this work happens directly in the main checkout, not a worktree — confirm with `git branch --show-current` that you're not accidentally in a stale worktree before running this).
- After committing each task, run the new migration against the real dev database with `docker exec team_app php artisan migrate --force` — the previous feature shipped with this step skipped and it caused a production-path bug (`route()` failing on a null slug). Do not repeat that mistake.

---

### Task 1: Branch main photo with drag-to-reposition

**Files:**
- Create: `src/database/migrations/2026_06_17_000003_add_photo_fields_to_branches_table.php`
- Modify: `src/app/Models/Branch.php`
- Modify: `src/app/Http/Controllers/Admin/BranchController.php`
- Modify: `src/resources/views/admin/branches/create.blade.php`
- Modify: `src/resources/views/admin/branches/edit.blade.php`
- Modify: `src/resources/views/card/branch.blade.php`
- Test: `src/tests/Feature/BranchPhotoTest.php`

**Interfaces:**
- Produces: `Branch::$fillable` includes `photo`, `photo_position_x`, `photo_position_y`; `Branch::getPhotoUrlAttribute(): string` accessor. Task 2 does not depend on this, but both tasks touch the same Blade files — implement Task 1 first so Task 2's edits land after these are in place.

- [ ] **Step 1: Write the failing tests**

Create `src/tests/Feature/BranchPhotoTest.php`:

```php
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
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `docker exec team_app php artisan test --filter=BranchPhotoTest`
Expected: FAIL — `photo`/`photo_position_x`/`photo_position_y` columns don't exist on `branches` yet.

- [ ] **Step 3: Create the migration**

Create `src/database/migrations/2026_06_17_000003_add_photo_fields_to_branches_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('slug');
            $table->unsignedTinyInteger('photo_position_x')->default(50)->after('photo');
            $table->unsignedTinyInteger('photo_position_y')->default(50)->after('photo_position_x');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['photo', 'photo_position_x', 'photo_position_y']);
        });
    }
};
```

- [ ] **Step 4: Update the Branch model**

Modify `src/app/Models/Branch.php`. Change:

```php
    protected $fillable = ['name', 'city', 'address', 'maps_url', 'phone', 'slug'];
```

to:

```php
    protected $fillable = [
        'name', 'city', 'address', 'maps_url', 'phone', 'slug',
        'photo', 'photo_position_x', 'photo_position_y',
    ];
```

Then add this accessor right after `generateSlug()` (before `employees()`):

```php
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo && \Storage::disk('public')->exists($this->photo)) {
            return asset('storage/' . $this->photo);
        }
        return '';
    }
```

- [ ] **Step 5: Update BranchController**

Modify `src/app/Http/Controllers/Admin/BranchController.php`. Add the import:

```php
use Illuminate\Support\Facades\Storage;
```

Change the `store` method's validation and creation block from:

```php
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'city'    => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'maps_url' => 'nullable|url|max:1000',
            'phone'   => 'nullable|string|max:50',
        ]);

        $data['slug'] = Branch::generateSlug($data['name']);
        Branch::create($data);

        return redirect()->route('admin.branches.index')
            ->with('success', 'Sucursal creada correctamente.');
    }
```

to:

```php
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'city'    => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'maps_url' => 'nullable|url|max:1000',
            'phone'   => 'nullable|string|max:50',
            'photo'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'photo_position_x' => 'nullable|integer|min:0|max:100',
            'photo_position_y' => 'nullable|integer|min:0|max:100',
        ]);

        $data['slug'] = Branch::generateSlug($data['name']);
        $data['photo_position_x'] = $data['photo_position_x'] ?? 50;
        $data['photo_position_y'] = $data['photo_position_y'] ?? 50;

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('branches', 'public');
        }

        Branch::create($data);

        return redirect()->route('admin.branches.index')
            ->with('success', 'Sucursal creada correctamente.');
    }
```

Change the `update` method from:

```php
    public function update(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'city'    => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'maps_url' => 'nullable|url|max:1000',
            'phone'   => 'nullable|string|max:50',
        ]);

        $branch->update($data);

        return redirect()->route('admin.branches.index')
            ->with('success', 'Sucursal actualizada correctamente.');
    }
```

to:

```php
    public function update(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'city'    => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'maps_url' => 'nullable|url|max:1000',
            'phone'   => 'nullable|string|max:50',
            'photo'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'photo_position_x' => 'nullable|integer|min:0|max:100',
            'photo_position_y' => 'nullable|integer|min:0|max:100',
        ]);

        if ($request->hasFile('photo')) {
            if ($branch->photo) {
                Storage::disk('public')->delete($branch->photo);
            }
            $data['photo'] = $request->file('photo')->store('branches', 'public');
        }

        if ($request->boolean('remove_photo') && $branch->photo) {
            Storage::disk('public')->delete($branch->photo);
            $data['photo'] = null;
        }

        $branch->update($data);

        return redirect()->route('admin.branches.index')
            ->with('success', 'Sucursal actualizada correctamente.');
    }
```

`$branch->update($data)` only overwrites the keys present in `$data` — when the request doesn't send `photo_position_x`/`photo_position_y` (e.g. the user only edited the name), those keys are simply absent from `$data` and the previously stored position is left untouched.

- [ ] **Step 6: Add the photo + drag-position UI to the create form**

Modify `src/resources/views/admin/branches/create.blade.php`. Change the opening `<form>` tag from:

```blade
                <form action="{{ route('admin.branches.store') }}" method="POST">
                    @csrf
```

to:

```blade
                <form action="{{ route('admin.branches.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="text-center mb-4">
                        <div id="sedePhotoPreview" class="avatar avatar-lg mx-auto mb-2"
                             style="width:120px;height:120px;font-size:1.5rem;background:#dcfce7;color:#16a34a;cursor:grab;"
                             onclick="document.getElementById('sedePhoto').click()">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="text-muted" style="font-size:.8rem;">
                            <a href="#" onclick="document.getElementById('sedePhoto').click();return false;">
                                Subir foto de la sede
                            </a>
                        </div>
                        <div class="text-muted" style="font-size:.72rem;">
                            Despues de subirla, arrastra la foto dentro del circulo para elegir el encuadre.
                        </div>
                        <input type="file" id="sedePhoto" name="photo" accept="image/*" class="d-none"
                               onchange="previewSedePhoto(this)">
                        <input type="hidden" name="photo_position_x" id="photo_position_x" value="50">
                        <input type="hidden" name="photo_position_y" id="photo_position_y" value="50">
                    </div>
```

Then change the end of the file from:

```blade
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> Guardar sucursal
                        </button>
                        <a href="{{ route('admin.branches.index') }}" class="btn btn-outline-secondary px-4">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
```

to:

```blade
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> Guardar sucursal
                        </button>
                        <a href="{{ route('admin.branches.index') }}" class="btn btn-outline-secondary px-4">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function previewSedePhoto(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById('sedePhotoPreview');
        preview.innerHTML = '';
        preview.style.backgroundImage = `url('${e.target.result}')`;
        preview.style.backgroundSize = 'cover';
        preview.style.backgroundPosition = '50% 50%';
        document.getElementById('photo_position_x').value = '50';
        document.getElementById('photo_position_y').value = '50';
    };
    reader.readAsDataURL(input.files[0]);
}

function setupSedePhotoDrag() {
    const container = document.getElementById('sedePhotoPreview');
    const xInput = document.getElementById('photo_position_x');
    const yInput = document.getElementById('photo_position_y');
    let dragging = false;
    let startX = 0, startY = 0;
    let posX = parseInt(xInput.value, 10);
    let posY = parseInt(yInput.value, 10);

    function clamp(v) { return Math.max(0, Math.min(100, v)); }

    function start(e) {
        dragging = true;
        const point = e.touches ? e.touches[0] : e;
        startX = point.clientX;
        startY = point.clientY;
    }

    function move(e) {
        if (!dragging) return;
        const point = e.touches ? e.touches[0] : e;
        const dx = point.clientX - startX;
        const dy = point.clientY - startY;
        const rect = container.getBoundingClientRect();
        posX = clamp(posX - (dx / rect.width) * 100);
        posY = clamp(posY - (dy / rect.height) * 100);
        container.style.backgroundPosition = `${posX}% ${posY}%`;
        startX = point.clientX;
        startY = point.clientY;
        e.preventDefault();
    }

    function end() {
        if (!dragging) return;
        dragging = false;
        xInput.value = Math.round(posX);
        yInput.value = Math.round(posY);
    }

    container.addEventListener('mousedown', start);
    container.addEventListener('touchstart', start, { passive: true });
    window.addEventListener('mousemove', move);
    window.addEventListener('touchmove', move, { passive: false });
    window.addEventListener('mouseup', end);
    window.addEventListener('touchend', end);
}
document.addEventListener('DOMContentLoaded', setupSedePhotoDrag);
</script>
@endpush
@endsection
```

- [ ] **Step 7: Add the same UI to the edit form**

Modify `src/resources/views/admin/branches/edit.blade.php`. Change the opening `<form>` tag from:

```blade
                <form action="{{ route('admin.branches.update', $branch) }}" method="POST">
                    @csrf @method('PUT')
```

to:

```blade
                <form action="{{ route('admin.branches.update', $branch) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    <div class="text-center mb-4">
                        <div id="sedePhotoPreview" class="avatar avatar-lg mx-auto mb-2"
                             style="width:120px;height:120px;font-size:1.5rem;cursor:grab;
                                    {{ $branch->photo_url ? "background-image:url('{$branch->photo_url}');background-size:cover;background-position:{$branch->photo_position_x}% {$branch->photo_position_y}%;" : 'background:#dcfce7;color:#16a34a;' }}"
                             onclick="document.getElementById('sedePhoto').click()">
                            @if(!$branch->photo_url)
                                <i class="fas fa-building"></i>
                            @endif
                        </div>
                        <div class="text-muted" style="font-size:.8rem;">
                            <a href="#" onclick="document.getElementById('sedePhoto').click();return false;">
                                {{ $branch->photo_url ? 'Cambiar foto' : 'Subir foto de la sede' }}
                            </a>
                            @if($branch->photo)
                                · <a href="#" onclick="removeSedePhoto();return false;" class="text-danger">Quitar foto</a>
                            @endif
                        </div>
                        <div class="text-muted" style="font-size:.72rem;">
                            Arrastra la foto dentro del circulo para elegir el encuadre.
                        </div>
                        <input type="file" id="sedePhoto" name="photo" accept="image/*" class="d-none"
                               onchange="previewSedePhoto(this)">
                        <input type="hidden" name="photo_position_x" id="photo_position_x" value="{{ $branch->photo_position_x }}">
                        <input type="hidden" name="photo_position_y" id="photo_position_y" value="{{ $branch->photo_position_y }}">
                        <input type="hidden" name="remove_photo" id="remove_photo" value="0">
                    </div>
```

Then, at the very end of the file, change:

```blade
@endsection
```

to:

```blade
@push('scripts')
<script>
function previewSedePhoto(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById('sedePhotoPreview');
        preview.innerHTML = '';
        preview.style.backgroundImage = `url('${e.target.result}')`;
        preview.style.backgroundSize = 'cover';
        preview.style.backgroundPosition = '50% 50%';
        document.getElementById('photo_position_x').value = '50';
        document.getElementById('photo_position_y').value = '50';
        document.getElementById('remove_photo').value = '0';
    };
    reader.readAsDataURL(input.files[0]);
}

function removeSedePhoto() {
    const preview = document.getElementById('sedePhotoPreview');
    preview.style.backgroundImage = '';
    preview.style.background = '#dcfce7';
    preview.style.color = '#16a34a';
    preview.innerHTML = '<i class="fas fa-building"></i>';
    document.getElementById('sedePhoto').value = '';
    document.getElementById('remove_photo').value = '1';
}

function setupSedePhotoDrag() {
    const container = document.getElementById('sedePhotoPreview');
    const xInput = document.getElementById('photo_position_x');
    const yInput = document.getElementById('photo_position_y');
    let dragging = false;
    let startX = 0, startY = 0;
    let posX = parseInt(xInput.value, 10);
    let posY = parseInt(yInput.value, 10);

    function clamp(v) { return Math.max(0, Math.min(100, v)); }

    function start(e) {
        dragging = true;
        const point = e.touches ? e.touches[0] : e;
        startX = point.clientX;
        startY = point.clientY;
    }

    function move(e) {
        if (!dragging) return;
        const point = e.touches ? e.touches[0] : e;
        const dx = point.clientX - startX;
        const dy = point.clientY - startY;
        const rect = container.getBoundingClientRect();
        posX = clamp(posX - (dx / rect.width) * 100);
        posY = clamp(posY - (dy / rect.height) * 100);
        container.style.backgroundPosition = `${posX}% ${posY}%`;
        startX = point.clientX;
        startY = point.clientY;
        e.preventDefault();
    }

    function end() {
        if (!dragging) return;
        dragging = false;
        xInput.value = Math.round(posX);
        yInput.value = Math.round(posY);
    }

    container.addEventListener('mousedown', start);
    container.addEventListener('touchstart', start, { passive: true });
    window.addEventListener('mousemove', move);
    window.addEventListener('touchmove', move, { passive: false });
    window.addEventListener('mouseup', end);
    window.addEventListener('touchend', end);
}
document.addEventListener('DOMContentLoaded', setupSedePhotoDrag);
</script>
@endpush
@endsection
```

- [ ] **Step 8: Show the photo on the public branch card**

Modify `src/resources/views/card/branch.blade.php`. Change:

```blade
        <div class="card-header">
            <div class="avatar-wrap">
                <div class="avatar-ring">
                    <div class="avatar-inner">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
            </div>
        </div>
```

to:

```blade
        <div class="card-header">
            <div class="avatar-wrap">
                <div class="avatar-ring">
                    <div class="avatar-inner">
                        @if($branch->photo_url)
                            <img src="{{ $branch->photo_url }}" alt="{{ $branch->name }}"
                                 style="object-position: {{ $branch->photo_position_x }}% {{ $branch->photo_position_y }}%;">
                        @else
                            <i class="fas fa-building"></i>
                        @endif
                    </div>
                </div>
            </div>
        </div>
```

- [ ] **Step 9: Run tests to verify they pass**

Run: `docker exec team_app php artisan test --filter=BranchPhotoTest`
Expected: PASS (5 tests)

Run the full suite too:

Run: `docker exec team_app php artisan test`
Expected: PASS (all tests)

- [ ] **Step 10: Commit**

```bash
git add src/database/migrations/2026_06_17_000003_add_photo_fields_to_branches_table.php src/app/Models/Branch.php src/app/Http/Controllers/Admin/BranchController.php src/resources/views/admin/branches/create.blade.php src/resources/views/admin/branches/edit.blade.php src/resources/views/card/branch.blade.php src/tests/Feature/BranchPhotoTest.php
git commit -m "Add branch main photo with drag-to-reposition framing"
```

- [ ] **Step 11: Run the migration against the real development database**

This step matters because the previous feature shipped with its migrations un-run against the real dev database, causing a production-path bug. Do not skip it.

Run: `docker exec team_app php artisan migrate --force`
Expected: shows `2026_06_17_000003_add_photo_fields_to_branches_table ... DONE`

---

### Task 2: Photo carousel (external URLs)

**Files:**
- Create: `src/database/migrations/2026_06_17_000004_create_branch_photos_table.php`
- Create: `src/app/Models/BranchPhoto.php`
- Modify: `src/app/Models/Branch.php`
- Modify: `src/app/Http/Controllers/Admin/BranchController.php`
- Modify: `src/resources/views/admin/branches/create.blade.php`
- Modify: `src/resources/views/admin/branches/edit.blade.php`
- Modify: `src/resources/views/card/branch.blade.php`
- Test: `src/tests/Feature/BranchPhotoCarouselTest.php`

**Interfaces:**
- Consumes: nothing from Task 1 (independent data/feature), but edits the same Blade files Task 1 already changed — implement after Task 1 is committed so there's no merge overlap.
- Produces: `Branch::photos(): HasMany` (ordered by `position`), `BranchPhoto::$fillable = ['branch_id', 'url', 'position']`.

- [ ] **Step 1: Write the failing tests**

Create `src/tests/Feature/BranchPhotoCarouselTest.php`:

```php
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
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `docker exec team_app php artisan test --filter=BranchPhotoCarouselTest`
Expected: FAIL — `branch_photos` table / `BranchPhoto` model don't exist yet.

- [ ] **Step 3: Create the migration**

Create `src/database/migrations/2026_06_17_000004_create_branch_photos_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('url', 1000);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index('branch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_photos');
    }
};
```

- [ ] **Step 4: Create the BranchPhoto model**

Create `src/app/Models/BranchPhoto.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchPhoto extends Model
{
    protected $fillable = ['branch_id', 'url', 'position'];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
```

- [ ] **Step 5: Add the `photos()` relation to Branch**

Modify `src/app/Models/Branch.php`. Add this method right after `employees()`:

```php
    public function photos()
    {
        return $this->hasMany(BranchPhoto::class)->orderBy('position');
    }
```

- [ ] **Step 6: Sync photo URLs in BranchController**

Modify `src/app/Http/Controllers/Admin/BranchController.php`. Add the import:

```php
use App\Models\BranchPhoto;
```

In the `store` method, add `'photos' => 'nullable|array', 'photos.*' => 'nullable|url|max:1000',` to the validation array (right after the `photo_position_y` line), then change the end of the method from:

```php
        Branch::create($data);

        return redirect()->route('admin.branches.index')
            ->with('success', 'Sucursal creada correctamente.');
    }
```

to:

```php
        $photos = array_values(array_filter($data['photos'] ?? [], fn ($url) => filled($url)));
        unset($data['photos']);

        $branch = Branch::create($data);

        foreach ($photos as $i => $url) {
            BranchPhoto::create(['branch_id' => $branch->id, 'url' => $url, 'position' => $i]);
        }

        return redirect()->route('admin.branches.index')
            ->with('success', 'Sucursal creada correctamente.');
    }
```

In the `update` method, add the same `'photos' => 'nullable|array', 'photos.*' => 'nullable|url|max:1000',` validation rules, then change the end of the method from:

```php
        $branch->update($data);

        return redirect()->route('admin.branches.index')
            ->with('success', 'Sucursal actualizada correctamente.');
    }
```

to:

```php
        $photos = array_values(array_filter($data['photos'] ?? [], fn ($url) => filled($url)));
        unset($data['photos']);

        $branch->update($data);

        $branch->photos()->delete();
        foreach ($photos as $i => $url) {
            BranchPhoto::create(['branch_id' => $branch->id, 'url' => $url, 'position' => $i]);
        }

        return redirect()->route('admin.branches.index')
            ->with('success', 'Sucursal actualizada correctamente.');
    }
```

- [ ] **Step 7: Add the URL-list UI to the create form**

Modify `src/resources/views/admin/branches/create.blade.php`. Right after the photo-position block added in Task 1 (the `</div>` that closes the `text-center mb-4` block containing `sedePhotoPreview`), add:

```blade

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Fotos de la sede (URLs externas)</label>
                        <div id="photoUrlRows">
                            <div class="d-flex gap-2 mb-2">
                                <input type="url" name="photos[]" class="form-control" placeholder="https://...">
                                <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">×</button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addPhotoUrlRow()">
                            <i class="fas fa-plus me-1"></i> Agregar foto
                        </button>
                        <div class="form-text">
                            Pega el link directo de cada foto (alojada en otro servicio). El orden de la lista es el orden del carrusel.
                        </div>
                    </div>
```

Then, in the `@push('scripts')` block created in Task 1, add this function alongside `previewSedePhoto`/`setupSedePhotoDrag`:

```js
function addPhotoUrlRow() {
    const container = document.getElementById('photoUrlRows');
    const row = document.createElement('div');
    row.className = 'd-flex gap-2 mb-2';
    row.innerHTML = '<input type="url" name="photos[]" class="form-control" placeholder="https://..."><button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">×</button>';
    container.appendChild(row);
}
```

- [ ] **Step 8: Add the same UI to the edit form**

Modify `src/resources/views/admin/branches/edit.blade.php`. Right after the photo-position block added in Task 1, add:

```blade

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Fotos de la sede (URLs externas)</label>
                        <div id="photoUrlRows">
                            @forelse($branch->photos as $photo)
                                <div class="d-flex gap-2 mb-2">
                                    <input type="url" name="photos[]" class="form-control" value="{{ $photo->url }}">
                                    <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">×</button>
                                </div>
                            @empty
                                <div class="d-flex gap-2 mb-2">
                                    <input type="url" name="photos[]" class="form-control" placeholder="https://...">
                                    <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">×</button>
                                </div>
                            @endforelse
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addPhotoUrlRow()">
                            <i class="fas fa-plus me-1"></i> Agregar foto
                        </button>
                        <div class="form-text">
                            Pega el link directo de cada foto (alojada en otro servicio). El orden de la lista es el orden del carrusel.
                        </div>
                    </div>
```

Then add the same `addPhotoUrlRow()` function (shown in Step 7) to the `@push('scripts')` block created in Task 1 of this file.

- [ ] **Step 9: Add the carousel to the public branch card**

Modify `src/resources/views/card/branch.blade.php`. Add these styles to the existing `<style>` block (the one with `.advisor-list` etc. — add these rules right before the closing `</style>`):

```css
        .sede-carousel { position: relative; margin-bottom: 1.5rem; border-radius: 14px; overflow: hidden; }
        .sede-carousel-track { position: relative; height: 200px; background: #f1f5f9; }
        .sede-carousel-slide { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .sede-carousel-arrow { position: absolute; top: 50%; transform: translateY(-50%); width: 32px; height: 32px; border-radius: 50%; background: rgba(15,23,42,.55); color: #fff; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; }
        .sede-carousel-arrow.prev { left: .5rem; }
        .sede-carousel-arrow.next { right: .5rem; }
        .sede-carousel-dots { display: flex; justify-content: center; gap: .4rem; padding: .6rem 0; background: #f8fafc; }
        .sede-carousel-dot { width: 7px; height: 7px; border-radius: 50%; background: #cbd5e1; border: none; cursor: pointer; padding: 0; }
        .sede-carousel-dot.active { background: var(--accent); }
```

Then, in the body, change the second `<div class="divider"></div>` (the one right before `<div class="contact-grid">`) from:

```blade
            <div class="divider"></div>

            <div class="contact-grid">
```

to:

```blade
            @if($branch->photos->isNotEmpty())
                <div class="divider"></div>

                <div class="sede-carousel">
                    <div class="sede-carousel-track">
                        @foreach($branch->photos as $i => $photo)
                            <img src="{{ $photo->url }}" class="sede-carousel-slide" style="display: {{ $i === 0 ? 'block' : 'none' }};" alt="{{ $branch->name }}">
                        @endforeach
                    </div>
                    @if($branch->photos->count() > 1)
                        <button type="button" class="sede-carousel-arrow prev">&lsaquo;</button>
                        <button type="button" class="sede-carousel-arrow next">&rsaquo;</button>
                        <div class="sede-carousel-dots">
                            @foreach($branch->photos as $i => $photo)
                                <button type="button" class="sede-carousel-dot {{ $i === 0 ? 'active' : '' }}"></button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <div class="divider"></div>

            <div class="contact-grid">
```

Then, right before the closing `</body>` tag, add the carousel script:

```blade
<script>
document.addEventListener('DOMContentLoaded', function () {
    const track = document.querySelector('.sede-carousel-track');
    if (!track) return;
    const slides = track.querySelectorAll('.sede-carousel-slide');
    const dots = document.querySelectorAll('.sede-carousel-dot');
    let current = 0;

    function show(index) {
        slides.forEach((s, i) => s.style.display = i === index ? 'block' : 'none');
        dots.forEach((d, i) => d.classList.toggle('active', i === index));
        current = index;
    }

    document.querySelector('.sede-carousel-arrow.prev')?.addEventListener('click', () => {
        show((current - 1 + slides.length) % slides.length);
    });
    document.querySelector('.sede-carousel-arrow.next')?.addEventListener('click', () => {
        show((current + 1) % slides.length);
    });
    dots.forEach((d, i) => d.addEventListener('click', () => show(i)));
});
</script>
</body>
```

- [ ] **Step 10: Run tests to verify they pass**

Run: `docker exec team_app php artisan test --filter=BranchPhotoCarouselTest`
Expected: PASS (5 tests)

Run the full suite too:

Run: `docker exec team_app php artisan test`
Expected: PASS (all tests)

- [ ] **Step 11: Commit**

```bash
git add src/database/migrations/2026_06_17_000004_create_branch_photos_table.php src/app/Models/BranchPhoto.php src/app/Models/Branch.php src/app/Http/Controllers/Admin/BranchController.php src/resources/views/admin/branches/create.blade.php src/resources/views/admin/branches/edit.blade.php src/resources/views/card/branch.blade.php src/tests/Feature/BranchPhotoCarouselTest.php
git commit -m "Add external-URL photo carousel to branch public card"
```

- [ ] **Step 12: Run the migration against the real development database**

Run: `docker exec team_app php artisan migrate --force`
Expected: shows `2026_06_17_000004_create_branch_photos_table ... DONE`

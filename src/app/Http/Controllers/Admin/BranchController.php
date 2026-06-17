<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount('employees')->orderBy('city')->get();
        return view('admin.branches.index', compact('branches'));
    }

    public function create()
    {
        return view('admin.branches.create');
    }

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
            'photos' => 'nullable|array',
            'photos.*' => 'nullable|url|max:1000',
        ]);

        $data['slug'] = Branch::generateSlug($data['name']);
        $data['photo_position_x'] = $data['photo_position_x'] ?? 50;
        $data['photo_position_y'] = $data['photo_position_y'] ?? 50;

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('branches', 'public');
        }

        $photos = array_values(array_filter($data['photos'] ?? [], fn ($url) => filled($url)));
        unset($data['photos']);

        $branch = Branch::create($data);

        foreach ($photos as $i => $url) {
            BranchPhoto::create(['branch_id' => $branch->id, 'url' => $url, 'position' => $i]);
        }

        return redirect()->route('admin.branches.index')
            ->with('success', 'Sucursal creada correctamente.');
    }

    public function show(Branch $branch)
    {
        $branch->load('employees');
        return redirect()->route('admin.branches.edit', $branch);
    }

    public function edit(Branch $branch)
    {
        $branch->load(['employees.branch']);
        return view('admin.branches.edit', compact('branch'));
    }

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
            'photos' => 'nullable|array',
            'photos.*' => 'nullable|url|max:1000',
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

    public function destroy(Branch $branch)
    {
        $branch->delete();
        return redirect()->route('admin.branches.index')
            ->with('success', 'Sucursal eliminada correctamente.');
    }

    public function downloadQr(Branch $branch)
    {
        $url = route('branch.show', $branch->slug);
        $png = QrCode::format('png')->size(500)->margin(2)->generate($url);

        return response($png, 200, [
            'Content-Type'        => 'image/png',
            'Content-Disposition' => 'attachment; filename="qr-sede-' . $branch->slug . '.png"',
        ]);
    }

    public function qrPreview(Branch $branch)
    {
        $url = route('branch.show', $branch->slug);
        $svg = QrCode::size(200)->generate($url);

        return response($svg, 200, ['Content-Type' => 'image/svg+xml']);
    }
}

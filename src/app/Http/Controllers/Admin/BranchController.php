<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
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
        ]);

        $data['slug'] = Branch::generateSlug($data['name']);
        Branch::create($data);

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
        ]);

        $branch->update($data);

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

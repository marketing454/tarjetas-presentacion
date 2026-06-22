<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with('branch');

        if ($request->filled('branch')) {
            $request->branch === 'none'
                ? $query->whereNull('branch_id')
                : $query->where('branch_id', $request->branch);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('position', 'like', '%' . $request->search . '%');
            });
        }

        $employees = $query->orderBy('name')->get();
        $branches  = Branch::orderBy('city')->get();
        $noBranchCount = Employee::whereNull('branch_id')->count();

        return view('admin.employees.index', compact('employees', 'branches', 'noBranchCount'));
    }

    public function create()
    {
        $branches = Branch::orderBy('city')->get();
        return view('admin.employees.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name'      => 'required|string|max:255',
            'position'  => 'required|string|max:255',
            'card_type' => ['required', Rule::in(array_keys(Employee::cardTypes()))],
            'photo'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'card_background' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',
            'whatsapp'  => 'nullable|string|max:30',
            'instagram' => 'nullable|string|max:100',
            'facebook'  => 'nullable|string|max:100',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('employees', 'public');
        }
        if ($request->hasFile('card_background')) {
            $data['card_background'] = $request->file('card_background')->store('card-backgrounds', 'public');
        }

        $data['slug'] = Employee::generateSlug($data['name']);
        Employee::create($data);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Empleado creado correctamente.');
    }

    public function show(Employee $employee)
    {
        return redirect()->route('admin.employees.edit', $employee);
    }

    public function edit(Employee $employee)
    {
        $branches = Branch::orderBy('city')->get();
        return view('admin.employees.edit', compact('employee', 'branches'));
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name'      => 'required|string|max:255',
            'position'  => 'required|string|max:255',
            'card_type' => ['required', Rule::in(array_keys(Employee::cardTypes()))],
            'photo'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'card_background' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',
            'whatsapp'  => 'nullable|string|max:30',
            'instagram' => 'nullable|string|max:100',
            'facebook'  => 'nullable|string|max:100',
        ]);

        if ($request->hasFile('photo')) {
            if ($employee->photo) {
                Storage::disk('public')->delete($employee->photo);
            }
            $data['photo'] = $request->file('photo')->store('employees', 'public');
        }

        if ($request->boolean('remove_photo') && $employee->photo) {
            Storage::disk('public')->delete($employee->photo);
            $data['photo'] = null;
        }
        if ($request->hasFile('card_background')) {
            if ($employee->card_background) {
                Storage::disk('public')->delete($employee->card_background);
            }
            $data['card_background'] = $request->file('card_background')->store('card-backgrounds', 'public');
        }

        if ($request->boolean('remove_card_background') && $employee->card_background) {
            Storage::disk('public')->delete($employee->card_background);
            $data['card_background'] = null;
        }

        $employee->update($data);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Empleado actualizado correctamente.');
    }

    public function destroy(Employee $employee)
    {
        if ($employee->photo) {
            Storage::disk('public')->delete($employee->photo);
        }
        if ($employee->card_background) {
            Storage::disk('public')->delete($employee->card_background);
        }
        $employee->delete();
        return redirect()->route('admin.employees.index')
            ->with('success', 'Empleado eliminado correctamente.');
    }

    public function detachBranch(Employee $employee)
    {
        $employee->update(['branch_id' => null]);

        return back()->with('success', "{$employee->name} fue quitado de la sucursal (el empleado no se eliminó).");
    }

    public function downloadQr(Employee $employee)
    {
        $url = route('card.show', $employee->slug);
        $png = QrCode::format('png')->size(500)->margin(2)->generate($url);

        return response($png, 200, [
            'Content-Type'        => 'image/png',
            'Content-Disposition' => 'attachment; filename="qr-' . $employee->slug . '.png"',
        ]);
    }

    public function qrPreview(Employee $employee)
    {
        $url = route('card.show', $employee->slug);
        $svg = QrCode::size(200)->generate($url);

        return response($svg, 200, ['Content-Type' => 'image/svg+xml']);
    }
}

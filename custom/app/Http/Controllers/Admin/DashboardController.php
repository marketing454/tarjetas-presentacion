<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'branches'  => Branch::count(),
            'employees' => Employee::count(),
            'cities'    => Branch::distinct('city')->count('city'),
        ];

        $recentEmployees = Employee::with('branch')->latest()->take(6)->get();
        $branches = Branch::withCount('employees')->orderBy('employees_count', 'desc')->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentEmployees', 'branches'));
    }
}

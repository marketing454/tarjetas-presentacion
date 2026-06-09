<?php

namespace App\Http\Controllers;

use App\Models\Employee;

class CardController extends Controller
{
    public function show(string $slug)
    {
        $employee = Employee::with('branch')->where('slug', $slug)->firstOrFail();
        return view('card.show', compact('employee'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\CardScan;
use App\Models\Employee;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function show(string $slug, Request $request)
    {
        $employee = Employee::with('branch')->where('slug', $slug)->firstOrFail();

        $employeeId = $employee->id;
        $ip         = $request->ip();
        $ua         = $request->userAgent();
        $referrer   = $request->header('referer');

        defer(function () use ($employeeId, $ip, $ua, $referrer) {
            CardScan::record($employeeId, $ip, $ua, $referrer);
        });

        return view('card.show', compact('employee'));
    }
}

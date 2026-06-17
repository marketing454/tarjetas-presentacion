<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CardScan;
use App\Models\Employee;
use Illuminate\Http\Request;

class BranchCardController extends Controller
{
    public function show(string $slug, Request $request)
    {
        $branch = Branch::with('employees')->where('slug', $slug)->firstOrFail();
        $branch->setRelation('employees', $branch->employees->sortBy('name')->values());

        $theme = Employee::themeFor(Employee::CARD_TYPE_NORMAL);

        $branchId = $branch->id;
        $ip       = $request->ip();
        $ua       = $request->userAgent();
        $referrer = $request->header('referer');

        defer(function () use ($branchId, $ip, $ua, $referrer) {
            CardScan::recordForBranch($branchId, $ip, $ua, $referrer);
        });

        return view('card.branch', compact('branch', 'theme'));
    }
}

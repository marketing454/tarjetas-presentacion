<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CardScan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MetricsController extends Controller
{
    public function index(Request $request)
    {
        $days = (int) $request->get('days', 30);
        $days = in_array($days, [7, 30, 90, 365], true) ? $days : 30;
        $from = now()->subDays($days)->startOfDay();

        // KPI totales (all time)
        $totalScans = CardScan::count();
        $scansToday = CardScan::whereDate('created_at', today())->count();
        $scansWeek  = CardScan::where('created_at', '>=', now()->startOfWeek())->count();
        $scansMonth = CardScan::where('created_at', '>=', now()->startOfMonth())->count();

        // Top empleados en el período
        $topEmployees = CardScan::query()
            ->select('employee_id', DB::raw('COUNT(*) as total'))
            ->with('employee:id,name,position,photo,slug')
            ->where('created_at', '>=', $from)
            ->groupBy('employee_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Por dispositivo
        $byDevice = CardScan::query()
            ->select('device_type', DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', $from)
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->orderByDesc('total')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->device_type => $r->total]);

        // Por OS
        $byOs = CardScan::query()
            ->select('os', DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', $from)
            ->whereNotNull('os')
            ->groupBy('os')
            ->orderByDesc('total')
            ->get();

        // Por browser
        $byBrowser = CardScan::query()
            ->select('browser', DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', $from)
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderByDesc('total')
            ->get();

        // Por ciudad (top 10)
        $byCity = CardScan::query()
            ->select('city', 'country', DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', $from)
            ->whereNotNull('city')
            ->where('city', '!=', 'Local')
            ->groupBy('city', 'country')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Por país (top 10)
        $byCountry = CardScan::query()
            ->select('country', DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', $from)
            ->whereNotNull('country')
            ->where('country', '!=', 'Local')
            ->groupBy('country')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Timeline: escaneos por día en el período
        $rawTimeline = CardScan::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', $from)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $timeline = collect();
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $timeline->push([
                'date'  => now()->subDays($i)->format('d/m'),
                'total' => $rawTimeline->get($date)?->total ?? 0,
            ]);
        }

        // Escaneos recientes
        $recentScans = CardScan::query()
            ->with('employee:id,name,position,slug')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('admin.metrics.index', compact(
            'days', 'totalScans', 'scansToday', 'scansWeek', 'scansMonth',
            'topEmployees', 'byDevice', 'byOs', 'byBrowser',
            'byCity', 'byCountry', 'timeline', 'recentScans'
        ));
    }
}

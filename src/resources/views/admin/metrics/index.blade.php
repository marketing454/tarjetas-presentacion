@extends('layouts.admin')

@section('title', 'Métricas')
@section('page-title', 'Métricas de Tarjetas')

@push('styles')
<style>
    .metric-kpi {
        border: none;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
    }
    .metric-kpi .icon-box {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .chart-card {
        background: #fff;
        border-radius: 12px;
        border: none;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
    }
    .chart-card .card-header {
        background: none;
        border-bottom: 1px solid #f1f5f9;
        padding: 1.1rem 1.5rem;
        font-weight: 600;
        color: #0f172a;
        font-size: .9rem;
    }
    .period-btn { font-size: .78rem; padding: .3rem .75rem; }
    .period-btn.active { background: #8dc63f; border-color: #8dc63f; color: #111; }
    .bar-label { font-size: .78rem; color: #475569; min-width: 90px; }
    .bar-track { flex: 1; background: #f1f5f9; border-radius: 6px; height: 10px; overflow: hidden; }
    .bar-fill  { height: 100%; border-radius: 6px; background: #8dc63f; transition: width .5s; }
    .bar-value { font-size: .78rem; font-weight: 600; color: #0f172a; min-width: 30px; text-align: right; }
    .rank-badge {
        width: 22px; height: 22px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: .65rem; font-weight: 700; flex-shrink: 0;
    }
    .scan-row td { font-size: .82rem; }
    .device-chip {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 2px 8px; border-radius: 20px;
        font-size: .7rem; font-weight: 500;
    }
    .device-chip.mobile   { background: #ede9fe; color: #6d28d9; }
    .device-chip.tablet   { background: #fef3c7; color: #92400e; }
    .device-chip.desktop  { background: #dbeafe; color: #1e40af; }
</style>
@endpush

@section('topbar-actions')
    <div class="btn-group" role="group">
        @foreach([7 => '7d', 30 => '30d', 90 => '90d', 365 => '1año'] as $d => $label)
            <a href="{{ request()->fullUrlWithQuery(['days' => $d]) }}"
               class="btn btn-outline-secondary period-btn {{ $days == $d ? 'active' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
@endsection

@section('content')

{{-- KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card metric-kpi h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box" style="background:#f0fdf4;">
                    <i class="fas fa-qrcode" style="color:#16a34a;"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold text-dark lh-1">{{ number_format($totalScans) }}</div>
                    <div class="text-muted small">Total escaneos</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card metric-kpi h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box" style="background:#eff6ff;">
                    <i class="fas fa-calendar-day" style="color:#2563eb;"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold text-dark lh-1">{{ number_format($scansToday) }}</div>
                    <div class="text-muted small">Hoy</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card metric-kpi h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box" style="background:#fef9c3;">
                    <i class="fas fa-calendar-week" style="color:#ca8a04;"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold text-dark lh-1">{{ number_format($scansWeek) }}</div>
                    <div class="text-muted small">Esta semana</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card metric-kpi h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box" style="background:#fdf2f8;">
                    <i class="fas fa-calendar" style="color:#9333ea;"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold text-dark lh-1">{{ number_format($scansMonth) }}</div>
                    <div class="text-muted small">Este mes</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Timeline --}}
<div class="chart-card mb-4">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="fas fa-chart-line text-primary"></i>
        Escaneos por día — últimos {{ $days }} días
    </div>
    <div class="card-body" style="height:220px;">
        <canvas id="timelineChart"></canvas>
    </div>
</div>

<div class="row g-4 mb-4 align-items-start">

    {{-- Top Empleados --}}
    <div class="col-lg-5">
        <div class="chart-card">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="fas fa-trophy text-warning"></i>
                Top empleados ({{ $days }}d)
            </div>
            <div class="card-body p-0">
                @forelse($topEmployees as $i => $row)
                    @php $emp = $row->employee; @endphp
                    @if($emp)
                    <div class="d-flex align-items-center gap-3 px-4 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="rank-badge"
                             style="background:{{ $i === 0 ? '#fef3c7' : ($i === 1 ? '#f1f5f9' : '#fef3c7') }};
                                    color:{{ $i === 0 ? '#92400e' : '#475569' }};">
                            {{ $i + 1 }}
                        </div>
                        <div class="flex-grow-1" style="min-width:0;">
                            <div class="fw-semibold text-dark" style="font-size:.82rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ $emp->name }}
                            </div>
                            <div class="text-muted" style="font-size:.72rem;">{{ $emp->position }}</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold" style="font-size:.95rem;color:#8dc63f;">{{ $row->total }}</div>
                            <div class="text-muted" style="font-size:.68rem;">escaneos</div>
                        </div>
                    </div>
                    @endif
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-chart-bar fa-2x mb-2 opacity-25"></i>
                        <p class="small mb-0">Sin datos en este período</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Dispositivos + OS apilados --}}
    <div class="col-lg-7">
        <div class="row g-4 align-items-start">

        {{-- Dispositivos --}}
        <div class="col-sm-6">
            <div class="chart-card">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="fas fa-mobile-screen text-primary"></i>
                    Dispositivos
                </div>
                <div class="card-body">
                    <div style="height:150px;" class="mb-3">
                        <canvas id="deviceChart"></canvas>
                    </div>
                    @php
                        $deviceTotal  = $byDevice->sum();
                        $deviceIcons  = ['mobile' => 'fa-mobile-screen', 'tablet' => 'fa-tablet-screen-button', 'desktop' => 'fa-desktop'];
                        $deviceLabels = ['mobile' => 'Móvil', 'tablet' => 'Tablet', 'desktop' => 'PC'];
                    @endphp
                    @foreach($byDevice as $type => $count)
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fas {{ $deviceIcons[$type] ?? 'fa-question' }} text-muted" style="width:16px;text-align:center;font-size:.8rem;"></i>
                        <span class="bar-label">{{ $deviceLabels[$type] ?? $type }}</span>
                        <div class="bar-track">
                            <div class="bar-fill" style="width:{{ $deviceTotal > 0 ? round($count / $deviceTotal * 100) : 0 }}%"></div>
                        </div>
                        <span class="bar-value">{{ $count }}</span>
                    </div>
                    @endforeach
                    @if($byDevice->isEmpty())
                        <p class="text-muted small text-center mb-0">Sin datos</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- OS --}}
        <div class="col-sm-6">
        <div class="chart-card">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="fas fa-laptop text-primary"></i>
                Sistema Operativo
            </div>
            <div class="card-body">
                @php $osTotal = $byOs->sum('total'); @endphp
                @forelse($byOs as $row)
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="bar-label">{{ $row->os }}</span>
                    <div class="bar-track">
                        <div class="bar-fill" style="width:{{ $osTotal > 0 ? round($row->total / $osTotal * 100) : 0 }}%"></div>
                    </div>
                    <span class="bar-value">{{ $row->total }}</span>
                </div>
                @empty
                    <p class="text-muted small text-center mb-0">Sin datos</p>
                @endforelse
            </div>
        </div>
        </div>{{-- /col-sm-6 OS --}}

        </div>{{-- /row nested --}}
    </div>{{-- /col-lg-7 --}}
</div>{{-- /row align-items-start --}}

<div class="row g-4 mb-4 align-items-start">
    {{-- Top Sedes --}}
    <div class="col-lg-5">
        <div class="chart-card">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="fas fa-building text-success"></i>
                Top sedes ({{ $days }}d)
            </div>
            <div class="card-body p-0">
                @forelse($topBranches as $i => $row)
                    @php $branchRow = $row->branch; @endphp
                    @if($branchRow)
                    <div class="d-flex align-items-center gap-3 px-4 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="rank-badge"
                             style="background:{{ $i === 0 ? '#fef3c7' : ($i === 1 ? '#f1f5f9' : '#fef3c7') }};
                                    color:{{ $i === 0 ? '#92400e' : '#475569' }};">
                            {{ $i + 1 }}
                        </div>
                        <div class="flex-grow-1" style="min-width:0;">
                            <div class="fw-semibold text-dark" style="font-size:.82rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ $branchRow->name }}
                            </div>
                            <div class="text-muted" style="font-size:.72rem;">{{ $branchRow->city }}</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold" style="font-size:.95rem;color:#8dc63f;">{{ $row->total }}</div>
                            <div class="text-muted" style="font-size:.68rem;">escaneos</div>
                        </div>
                    </div>
                    @endif
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-building fa-2x mb-2 opacity-25"></i>
                        <p class="small mb-0">Sin datos en este período</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4 align-items-start">

    {{-- Ciudades --}}
    <div class="col-lg-6">
        <div class="chart-card">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="fas fa-location-dot text-danger"></i>
                Top ciudades ({{ $days }}d)
            </div>
            <div class="card-body">
                @php $cityTotal = $byCity->sum('total'); @endphp
                @forelse($byCity as $row)
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="bar-label text-truncate" style="max-width:110px;" title="{{ $row->city }}">
                        {{ $row->city }}
                    </span>
                    <div class="bar-track">
                        <div class="bar-fill" style="width:{{ $cityTotal > 0 ? round($row->total / $cityTotal * 100) : 0 }}%"></div>
                    </div>
                    <span class="bar-value">{{ $row->total }}</span>
                </div>
                @empty
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-map fa-2x mb-2 opacity-25"></i>
                        <p class="small mb-0">Sin datos de ubicación aún</p>
                        <p class="small mb-0" style="font-size:.7rem;">Los escaneos desde IPs locales no registran ciudad</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Navegador y País --}}
    <div class="col-lg-6">
        <div class="row g-4">
            <div class="col-12">
                <div class="chart-card">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="fas fa-globe text-primary"></i>
                        Navegador
                    </div>
                    <div class="card-body">
                        @php $browserTotal = $byBrowser->sum('total'); @endphp
                        <div class="row g-2">
                        @forelse($byBrowser as $row)
                            <div class="col-6">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="bar-label" style="min-width:65px;">{{ $row->browser }}</span>
                                    <div class="bar-track">
                                        <div class="bar-fill" style="width:{{ $browserTotal > 0 ? round($row->total / $browserTotal * 100) : 0 }}%"></div>
                                    </div>
                                    <span class="bar-value">{{ $row->total }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center text-muted small">Sin datos</div>
                        @endforelse
                        </div>
                    </div>
                </div>
            </div>
            @if($byCountry->isNotEmpty())
            <div class="col-12">
                <div class="chart-card">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="fas fa-flag text-success"></i>
                        Países
                    </div>
                    <div class="card-body">
                        @php $countryTotal = $byCountry->sum('total'); @endphp
                        @foreach($byCountry as $row)
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="bar-label">{{ $row->country }}</span>
                            <div class="bar-track">
                                <div class="bar-fill" style="width:{{ $countryTotal > 0 ? round($row->total / $countryTotal * 100) : 0 }}%"></div>
                            </div>
                            <span class="bar-value">{{ $row->total }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Escaneos recientes --}}
<div class="chart-card">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="fas fa-clock-rotate-left text-primary"></i>
        Escaneos recientes
    </div>
    <div class="card-body p-0">
        @if($recentScans->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-qrcode fa-2x mb-2 opacity-25"></i>
                <p class="small mb-0">Aún no hay escaneos registrados.</p>
                <p class="small mb-0">Comparte los links o QRs de los empleados para empezar a ver datos.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Empleado / Sede</th>
                            <th>Dispositivo</th>
                            <th>OS / Browser</th>
                            <th>Ciudad</th>
                            <th class="pe-4">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($recentScans as $scan)
                        <tr class="scan-row">
                            <td class="ps-4">
                                @if($scan->employee)
                                    <a href="{{ route('card.show', $scan->employee->slug) }}" target="_blank"
                                       class="text-decoration-none fw-semibold text-dark" style="font-size:.82rem;">
                                        {{ $scan->employee->name }}
                                    </a>
                                    <div class="text-muted" style="font-size:.7rem;">{{ $scan->employee->position }}</div>
                                @elseif($scan->branch)
                                    <a href="{{ route('branch.show', $scan->branch->slug) }}" target="_blank"
                                       class="text-decoration-none fw-semibold text-dark" style="font-size:.82rem;">
                                        <i class="fas fa-building me-1 text-muted"></i>{{ $scan->branch->name }}
                                    </a>
                                    <div class="text-muted" style="font-size:.7rem;">Sede · {{ $scan->branch->city }}</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($scan->device_type)
                                    <span class="device-chip {{ $scan->device_type }}">
                                        <i class="fas {{ $scan->device_type === 'mobile' ? 'fa-mobile-screen' : ($scan->device_type === 'tablet' ? 'fa-tablet-screen-button' : 'fa-desktop') }}"></i>
                                        {{ ucfirst($scan->device_type) }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-muted">
                                {{ $scan->os ?? '—' }}@if($scan->browser) / {{ $scan->browser }}@endif
                            </td>
                            <td class="text-muted">
                                {{ $scan->city ?? '—' }}
                                @if($scan->country && $scan->country !== 'Local' && $scan->country !== $scan->city)
                                    <span style="font-size:.7rem;">, {{ $scan->country }}</span>
                                @endif
                            </td>
                            <td class="pe-4 text-muted">
                                <div>{{ $scan->created_at->format('d/m/Y') }}</div>
                                <div style="font-size:.7rem;">{{ $scan->created_at->format('H:i') }}</div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const accent  = '#8dc63f';
const accentT = 'rgba(141,198,63,.15)';
const palette = ['#8dc63f','#2563eb','#f59e0b','#ef4444','#8b5cf6','#06b6d4'];

// Timeline
const timelineCtx = document.getElementById('timelineChart').getContext('2d');
new Chart(timelineCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($timeline->pluck('date')) !!},
        datasets: [{
            label: 'Escaneos',
            data: {!! json_encode($timeline->pluck('total')) !!},
            borderColor: accent,
            backgroundColor: accentT,
            borderWidth: 2,
            pointRadius: {{ $days <= 30 ? 3 : 0 }},
            pointBackgroundColor: accent,
            tension: 0.4,
            fill: true,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1, font: { size: 11 } },
                grid: { color: '#f1f5f9' }
            },
            x: {
                ticks: {
                    font: { size: 10 },
                    maxTicksLimit: {{ $days <= 30 ? 15 : 12 }},
                    maxRotation: 0,
                },
                grid: { display: false }
            }
        }
    }
});

// Devices doughnut
@if($byDevice->isNotEmpty())
const deviceCtx = document.getElementById('deviceChart').getContext('2d');
new Chart(deviceCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($byDevice->keys()->map(fn($k) => match($k) { 'mobile' => 'Móvil', 'tablet' => 'Tablet', 'desktop' => 'PC', default => $k })->values()) !!},
        datasets: [{
            data: {!! json_encode($byDevice->values()) !!},
            backgroundColor: palette,
            borderWidth: 2,
            borderColor: '#fff',
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { font: { size: 11 }, boxWidth: 12, padding: 10 }
            }
        },
        cutout: '60%',
    }
});
@endif
</script>
@endpush

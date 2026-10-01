@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
<style>
    * { box-sizing: border-box; }
    body { background-color: #c8e6a0; min-height: 100vh; padding: clamp(10px, 3vw, 20px); font-family: 'Segoe UI', Arial, sans-serif; }

    /* ── Top bar ── */
    .topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 8px; }
    .topbar h1 { font-size: clamp(1.2rem, 4vw, 1.5rem); font-weight: 700; color: #1a3a0a; }
    .logout-btn { background: #2e9e3e; color: #fff; border: none; border-radius: 8px; padding: 7px 16px; font-size: 0.85rem; font-weight: 600; cursor: pointer; text-decoration: none; transition: background 0.2s; white-space: nowrap; }
    .logout-btn:hover { background: #1a6e28; }

    /* ── Search bar ── */
    .search-bar { background: #fff; border-radius: 14px; padding: 12px 14px; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
    .search-bar label { font-size: 0.78rem; font-weight: 700; color: #1a3a0a; white-space: nowrap; }
    .search-bar input[type="text"], .search-bar input[type="date"] { border: 1.5px solid #b6e4a0; border-radius: 20px; padding: 6px 12px; font-size: 0.82rem; color: #1a3a0a; outline: none; background: #f4fdf0; transition: border 0.2s; min-width: 0; flex: 1; }
    .search-bar input[type="text"] { min-width: 120px; }
    .search-bar input[type="date"] { min-width: 130px; }
    .search-bar input:focus { border-color: #2e9e3e; }
    .search-btn { background: #2e9e3e; color: #fff; border: none; border-radius: 20px; padding: 7px 18px; font-size: 0.82rem; font-weight: 700; cursor: pointer; transition: background 0.2s; white-space: nowrap; }
    .search-btn:hover { background: #1a6e28; }
    .reset-btn { background: transparent; color: #c0392b; border: 1.5px solid #c0392b; border-radius: 20px; padding: 6px 14px; font-size: 0.82rem; font-weight: 700; cursor: pointer; text-decoration: none; transition: background 0.2s; white-space: nowrap; }
    .reset-btn:hover { background: #fdecea; }
    .search-divider { color: #aaa; font-size: 0.8rem; }

    /* ── Mode buttons ── */
    .mode-btns { display: flex; gap: 8px; margin-bottom: 12px; flex-wrap: wrap; }
    .mode-btn { padding: 6px 16px; border-radius: 6px; border: 2px solid #555; background: #fff; font-size: 0.8rem; font-weight: 600; cursor: pointer; text-decoration: none; color: #333; transition: background 0.15s; white-space: nowrap; }
    .mode-btn.active { background: #2e9e3e; color: #fff; border-color: #2e9e3e; }
    .mode-btn:hover:not(.active) { background: #e0e0e0; }

    /* ── Device panel ── */
    .device-panel { background: #b8d4f0; border-radius: 14px; padding: 14px; margin-bottom: 14px; }
    .device-status-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 14px; }
    .device-card { background: #fff; border-radius: 12px; padding: 10px 12px; display: flex; align-items: center; gap: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.07); border-left: 4px solid #ccc; min-width: 0; }
    .device-card.on  { border-left-color: #2e9e3e; }
    .device-card.off { border-left-color: #bbb; opacity: 0.75; }
    .device-icon { width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .device-icon svg { width: 18px; height: 18px; }
    .pump-icon { background: #d4f5d0; color: #2e9e3e; }
    .sprinkler-icon { background: #d0f0ff; color: #0ea5e9; }
    .fan-icon { background: #fff3e0; color: #f59e0b; }
    .device-info { flex: 1; min-width: 0; }
    .device-name { font-size: clamp(0.72rem, 2vw, 0.82rem); font-weight: 700; color: #1a3a0a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .device-desc { font-size: clamp(0.62rem, 1.6vw, 0.72rem); color: #666; margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .device-badge { font-size: 0.72rem; font-weight: 800; padding: 3px 8px; border-radius: 20px; letter-spacing: 1px; flex-shrink: 0; }
    .badge-on  { background: #2e9e3e; color: #fff; }
    .badge-off { background: #e0e0e0; color: #888; }

    /* ── Activity log ── */
    .activity-log { background: #fff; border-radius: 12px; padding: 12px 14px; box-shadow: 0 2px 6px rgba(0,0,0,0.07); }
    .activity-log-header { display: flex; align-items: baseline; gap: 8px; margin-bottom: 10px; border-bottom: 1px solid #e8f5e9; padding-bottom: 8px; flex-wrap: wrap; }
    .activity-log-title { font-size: 0.85rem; font-weight: 700; color: #1a3a0a; }
    .activity-log-sub { font-size: 0.72rem; color: #888; }
    .activity-list { display: flex; flex-direction: column; max-height: 200px; overflow-y: auto; }
    .activity-item { display: flex; align-items: flex-start; gap: 8px; padding: 8px 4px; border-bottom: 1px solid #f0f0f0; transition: background 0.15s; }
    .activity-item:last-child { border-bottom: none; }
    .activity-item:hover { background: #f9fdf6; }
    .activity-dot { width: 10px; height: 10px; border-radius: 50%; margin-top: 4px; flex-shrink: 0; }
    .activity-item.pump .activity-dot { background: #2e9e3e; }
    .activity-item.sprinkler .activity-dot { background: #0ea5e9; }
    .activity-item.fan .activity-dot { background: #f59e0b; }
    .activity-body { flex: 1; min-width: 0; }
    .activity-msg { font-size: 0.78rem; color: #1a3a0a; line-height: 1.4; }
    .activity-msg strong { font-weight: 700; }
    .activity-detail { font-size: 0.7rem; color: #777; margin-top: 2px; }
    .activity-time { font-size: 0.68rem; color: #999; white-space: nowrap; padding-top: 2px; flex-shrink: 0; }
    .activity-empty { text-align: center; color: #aaa; font-size: 0.82rem; font-style: italic; padding: 16px 0; }

    /* ── Main grid: charts + stat cards ── */
    .main-grid { display: grid; grid-template-columns: 1fr 260px; gap: 14px; }
    .chart-col { display: flex; flex-direction: column; gap: 14px; min-width: 0; }
    .chart-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .chart-card { background: #fff; border-radius: 12px; padding: 12px 14px 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); min-width: 0; }
    .chart-card.humid-card { background: #d0f0ff; }
    .chart-card.soil-card  { background: #fff8e0; }
    .chart-title { font-size: clamp(0.7rem, 1.8vw, 0.82rem); font-weight: 700; color: #1a3a0a; background: #fff; display: inline-block; border: 1px solid #ccc; border-radius: 6px; padding: 3px 8px; margin-bottom: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; }
    .chart-container { position: relative; height: 110px; }
    .chart-xlabel { text-align: center; font-size: 0.7rem; color: #888; margin-top: 4px; }

    /* ── Stat cards ── */
    .stat-col { display: flex; flex-direction: column; gap: 14px; }
    .stat-card { border-radius: 14px; padding: 16px 18px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); }
    .stat-card.soil-stat  { background: #fff8e0; }
    .stat-card.humid-stat { background: #d0f0ff; }
    .stat-card.temp-stat  { background: #e8f5e9; }
    .stat-title { font-size: 0.8rem; font-weight: 700; color: #1a3a0a; margin-bottom: 8px; }
    .stat-body  { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .stat-value { font-size: clamp(1.5rem, 4vw, 2rem); font-weight: 800; color: #1a3a0a; }
    .stat-change { font-size: 0.78rem; font-weight: 700; padding: 3px 7px; border-radius: 6px; background: #fff; border: 1px solid #ccc; }
    .stat-change.negative { color: #c0392b; }
    .stat-change.positive { color: #27ae60; }
    .stat-change.neutral  { color: #888; }

    /* ── Summary table ── */
    .summary-section { margin-top: 16px; background: #fff; border-radius: 14px; padding: 16px 18px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); }
    .summary-section h2 { font-size: clamp(0.9rem, 2.5vw, 1rem); font-weight: 700; color: #1a3a0a; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .summary-section h2 .badge { background: #2e9e3e; color: #fff; font-size: 0.7rem; padding: 2px 8px; border-radius: 10px; font-weight: 600; }
    .summary-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    table.summary-table { width: 100%; border-collapse: collapse; font-size: clamp(0.7rem, 1.8vw, 0.8rem); min-width: 560px; }
    table.summary-table thead tr { background: #2e9e3e; color: #fff; }
    table.summary-table thead tr.sub-head { background: #1a6e28; }
    table.summary-table thead th { padding: 7px 10px; text-align: center; font-weight: 600; white-space: nowrap; }
    table.summary-table thead th:first-child { text-align: left; }
    table.summary-table tbody tr:nth-child(even) { background: #f4fdf0; }
    table.summary-table tbody tr:hover { background: #d4f5d0; }
    table.summary-table tbody td { padding: 6px 10px; text-align: center; color: #1a3a0a; border-bottom: 1px solid #e8f5e9; white-space: nowrap; }
    table.summary-table tbody td:first-child { text-align: left; font-weight: 700; color: #155724; }

    /* separator between column groups */
    table.summary-table th.group-sep,
    table.summary-table td.group-sep { border-left: 2px solid rgba(255,255,255,0.5); }
    table.summary-table tbody td.group-sep { border-left: 2px solid #b6e4a0; }
    .val-avg { color: #1a5276; font-weight: 600; }
    .val-max { color: #c0392b; font-weight: 600; }
    .val-min { color: #27ae60; font-weight: 600; }
    .no-data { text-align: center; color: #aaa; font-style: italic; padding: 20px; }
    .result-count { font-size: 0.75rem; color: #555; margin-bottom: 8px; }

    /* ══════════════════════════════════════
       RESPONSIVE BREAKPOINTS
    ══════════════════════════════════════ */

    /* Tablet: ≤ 900px — stack stat cards below charts */
    @media (max-width: 900px) {
        .main-grid { grid-template-columns: 1fr; }
        .stat-col { flex-direction: row; flex-wrap: wrap; }
        .stat-card { flex: 1; min-width: 140px; }
    }

    /* Mobile: ≤ 600px */
    @media (max-width: 600px) {
        body { padding: 10px; }

        /* Search bar stacks vertically */
        .search-bar { flex-direction: column; align-items: stretch; }
        .search-bar input[type="text"],
        .search-bar input[type="date"] { width: 100%; min-width: unset; }
        .search-divider { display: none; }
        .search-btn, .reset-btn { width: 100%; text-align: center; }

        /* Device cards: 1 column on very small, 2 on medium mobile */
        .device-status-row { grid-template-columns: 1fr; }

        /* Charts: stack the 2-col row */
        .chart-row-2 { grid-template-columns: 1fr; }

        /* Stat cards: stack vertically */
        .stat-col { flex-direction: column; }
        .stat-card { min-width: unset; }

        /* Activity time wraps instead of overflowing */
        .activity-time { white-space: normal; text-align: right; }

        /* Mode buttons full width feel */
        .mode-btns { gap: 6px; }
        .mode-btn { flex: 1; text-align: center; padding: 8px 10px; }
    }

    /* Small mobile: 2-col device cards */
    @media (min-width: 400px) and (max-width: 600px) {
        .device-status-row { grid-template-columns: 1fr 1fr; }
    }
</style>
@endpush

@section('content')

{{-- Top bar --}}
<div class="topbar">
    <h1>Welcome</h1>
    <form method="POST" action="{{ route('logout') }}" style="display:inline">
        @csrf
        <button type="submit" class="logout-btn">Log Out</button>
    </form>
</div>

{{-- Search bar --}}
<form method="GET" action="{{ route('dashboard') }}" class="search-bar">
    <label for="search">Search</label>
    <input type="text" id="search" name="search" value="{{ $search }}" placeholder="e.g. 2024-01 or date keyword">
    <span class="search-divider">|</span>
    <label for="date_from">From</label>
    <input type="date" id="date_from" name="date_from" value="{{ $dateFrom }}">
    <label for="date_to">To</label>
    <input type="date" id="date_to" name="date_to" value="{{ $dateTo }}">
    <input type="hidden" name="mode" value="{{ $mode }}">
    <button type="submit" class="search-btn">Search</button>
    @if($search || $dateFrom || $dateTo)
        <a href="{{ route('dashboard', ['mode' => $mode]) }}" class="reset-btn">Reset</a>
    @endif
</form>

{{-- Mode toggle --}}
<div class="mode-btns">
    <a href="{{ route('dashboard', array_merge(request()->except('mode'), ['mode' => 'monthly'])) }}"
       class="mode-btn {{ $mode === 'monthly' ? 'active' : '' }}">Monthly Summary</a>
    <a href="{{ route('dashboard', array_merge(request()->except('mode'), ['mode' => 'weekly'])) }}"
       class="mode-btn {{ $mode === 'weekly' ? 'active' : '' }}">Weekly Summary</a>
    <a href="{{ route('dashboard', array_merge(request()->except('mode'), ['mode' => 'daily'])) }}"
       class="mode-btn {{ $mode === 'daily' ? 'active' : '' }}">Daily</a>
</div>

{{-- Device Panel --}}
<div class="device-panel">
    <div class="device-status-row">

        <div class="device-card {{ $pumpOn ? 'on' : 'off' }}" id="pump-card">
            <div class="device-icon pump-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2C8 2 5 5 5 9c0 5 7 13 7 13s7-8 7-13c0-4-3-7-7-7z"/>
                    <circle cx="12" cy="9" r="2.5"/>
                </svg>
            </div>
            <div class="device-info">
                <div class="device-name">Irrigation Pump</div>
                <div class="device-desc">Soil moisture &lt; 40%</div>
            </div>
            <div class="device-badge {{ $pumpOn ? 'badge-on' : 'badge-off' }}" id="pump-badge">{{ $pumpOn ? 'ON' : 'OFF' }}</div>
        </div>

        <div class="device-card {{ $sprinklerOn ? 'on' : 'off' }}" id="mist-card">
            <div class="device-icon sprinkler-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v4M4.93 4.93l2.83 2.83M2 12h4M4.93 19.07l2.83-2.83M12 22v-4M19.07 19.07l-2.83-2.83M22 12h-4M19.07 4.93l-2.83 2.83"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </div>
            <div class="device-info">
                <div class="device-name">Misting System</div>
                <div class="device-desc">Temp &gt; 30°C or Humid &lt; 60%</div>
            </div>
            <div class="device-badge {{ $sprinklerOn ? 'badge-on' : 'badge-off' }}" id="mist-badge">{{ $sprinklerOn ? 'ON' : 'OFF' }}</div>
        </div>

        <div class="device-card {{ $fanOn ? 'on' : 'off' }}" id="fan-card">
            <div class="device-icon fan-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2a4 4 0 0 1 4 4c0 2-2 3-2 6h-4c0-3-2-4-2-6a4 4 0 0 1 4-4z"/>
                    <path d="M22 12a4 4 0 0 1-4 4c-2 0-3-2-6-2v-4c3 0 4-2 6-2a4 4 0 0 1 4 4z"/>
                    <path d="M12 22a4 4 0 0 1-4-4c0-2 2-3 2-6h4c0 3 2 4 2 6a4 4 0 0 1-4 4z"/>
                    <path d="M2 12a4 4 0 0 1 4-4c2 0 3 2 6 2v4c-3 0-4 2-6 2a4 4 0 0 1-4-4z"/>
                    <circle cx="12" cy="12" r="2"/>
                </svg>
            </div>
            <div class="device-info">
                <div class="device-name">Exhaust Fan</div>
                <div class="device-desc">Ventilation control</div>
            </div>
            <div class="device-badge {{ $fanOn ? 'badge-on' : 'badge-off' }}">{{ $fanOn ? 'ON' : 'OFF' }}</div>
        </div>

    </div>

    <div class="activity-log">
        <div class="activity-log-header">
            <span class="activity-log-title">Device Activity Log</span>
            <span class="activity-log-sub">Based on last 50 sensor readings</span>
        </div>
        @if($deviceEvents->count())
            <div class="activity-list">
                @foreach($deviceEvents as $event)
                <div class="activity-item {{ $event['color'] }}">
                    <div class="activity-dot"></div>
                    <div class="activity-body">
                        <div class="activity-msg"><strong>{{ $event['device'] }}</strong> — {{ $event['message'] }}</div>
                        <div class="activity-detail">{{ $event['detail'] }}</div>
                    </div>
                    <div class="activity-time">{{ $event['time'] }}</div>
                </div>
                @endforeach
            </div>
        @else
            <div class="activity-empty">No device activations recorded yet.</div>
        @endif
    </div>
</div>

@if($search || $dateFrom || $dateTo)
    <p class="result-count">
        Showing {{ count($labels) }} data points
        @if($dateFrom || $dateTo) from {{ $dateFrom ?: '—' }} to {{ $dateTo ?: '—' }} @endif
        @if($search) matching "{{ $search }}" @endif
    </p>
@endif

{{-- Main grid: charts + stat cards --}}
<div class="main-grid">
    <div class="chart-col">

        @php
            $xLabel = $mode === 'daily' ? 'Time (Today)' : ($mode === 'weekly' ? 'Day (Last 7 Days)' : 'Week per Month');
            $modeLabel = $mode === 'daily' ? "Today's Readings" : ($mode === 'weekly' ? 'Daily Avg — Last 7 Days' : 'Weekly Avg per Month');
        @endphp

        <div class="chart-card">
            <div class="chart-title">Temperature — {{ $modeLabel }}</div>
            <div class="chart-container"><canvas id="tempChart"></canvas></div>
            <p class="chart-xlabel">{{ $xLabel }}</p>
        </div>

        <div class="chart-row-2">
            <div class="chart-card humid-card">
                <div class="chart-title">Humidity — {{ $modeLabel }}</div>
                <div class="chart-container"><canvas id="humidChart"></canvas></div>
                <p class="chart-xlabel">{{ $xLabel }}</p>
            </div>
            <div class="chart-card soil-card">
                <div class="chart-title">Soil Moisture — {{ $modeLabel }}</div>
                <div class="chart-container"><canvas id="soilChart"></canvas></div>
                <p class="chart-xlabel">{{ $xLabel }}</p>
            </div>
        </div>

    </div>

    <div class="stat-col">
        <div class="stat-card soil-stat">
            <div class="stat-title">Soil Moisture (Latest)</div>
            <div class="stat-body">
                <span class="stat-value" id="live-soil">{{ number_format($latest->soil_moisture ?? 0, 1) }} %</span>
                @if(!is_null($soilChange))
                    <span class="stat-change {{ $soilChange < 0 ? 'negative' : ($soilChange > 0 ? 'positive' : 'neutral') }}" id="live-soil-change">
                        {{ $soilChange > 0 ? '+' : '' }}{{ $soilChange }} %
                    </span>
                @else
                    <span class="stat-change neutral" id="live-soil-change">—</span>
                @endif
            </div>
        </div>
        <div class="stat-card humid-stat">
            <div class="stat-title">Humidity (Latest)</div>
            <div class="stat-body">
                <span class="stat-value" id="live-humid">{{ number_format($latest->humidity ?? 0, 1) }} %</span>
                @if(!is_null($humidChange))
                    <span class="stat-change {{ $humidChange < 0 ? 'negative' : ($humidChange > 0 ? 'positive' : 'neutral') }}" id="live-humid-change">
                        {{ $humidChange > 0 ? '+' : '' }}{{ $humidChange }} %
                    </span>
                @else
                    <span class="stat-change neutral" id="live-humid-change">—</span>
                @endif
            </div>
        </div>
        <div class="stat-card temp-stat">
            <div class="stat-title">Temperature (Latest)</div>
            <div class="stat-body">
                <span class="stat-value" id="live-temp">{{ number_format($latest->temperature ?? 0, 1) }} °C</span>
                @if(!is_null($tempChange))
                    <span class="stat-change {{ $tempChange < 0 ? 'negative' : ($tempChange > 0 ? 'positive' : 'neutral') }}" id="live-temp-change">
                        {{ $tempChange > 0 ? '+' : '' }}{{ $tempChange }} %
                    </span>
                @else
                    <span class="stat-change neutral" id="live-temp-change">—</span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Summary Table --}}
<div class="summary-section">
    <h2>
        @if($mode === 'daily')   Daily Summary   <span class="badge">{{ now()->format('F d, Y') }}</span>
        @elseif($mode === 'weekly') Weekly Summary <span class="badge">Last 7 Days</span>
        @else                    Monthly Summary  <span class="badge">Weeks per Month</span>
        @endif
    </h2>

    @if($summaryRows->count())
        <div class="summary-table-wrap">
            <table class="summary-table">
                <thead>
                    <tr>
                        <th>{{ $mode === 'daily' ? 'Hour' : ($mode === 'weekly' ? 'Day' : 'Week') }}</th>
                        <th colspan="3" class="group-sep">Temperature (°C)</th>
                        <th colspan="3" class="group-sep">Humidity (%)</th>
                        <th colspan="3" class="group-sep">Soil Moisture (%)</th>
                        <th class="group-sep">Readings</th>
                    </tr>
                    <tr class="sub-head">
                        <th></th>
                        <th class="group-sep">Avg</th><th>Max</th><th>Min</th>
                        <th class="group-sep">Avg</th><th>Max</th><th>Min</th>
                        <th class="group-sep">Avg</th><th>Max</th><th>Min</th>
                        <th class="group-sep"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($summaryRows as $row)
                    <tr>
                        <td>{{ $row->label }}</td>
                        <td class="val-avg group-sep">{{ $row->avg_temp }}</td>
                        <td class="val-max">{{ $row->max_temp }}</td>
                        <td class="val-min">{{ $row->min_temp }}</td>
                        <td class="val-avg group-sep">{{ $row->avg_humidity }}</td>
                        <td class="val-max">{{ $row->max_humidity }}</td>
                        <td class="val-min">{{ $row->min_humidity }}</td>
                        <td class="val-avg group-sep">{{ $row->avg_soil }}</td>
                        <td class="val-max">{{ $row->max_soil }}</td>
                        <td class="val-min">{{ $row->min_soil }}</td>
                        <td class="group-sep">{{ number_format($row->reading_count) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="no-data">
            @if($mode === 'daily') No readings recorded today yet.
            @elseif($mode === 'weekly') No readings in the last 7 days.
            @else No monthly data available yet.
            @endif
        </p>
    @endif
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const labels    = @json($labels);
    const tempData  = @json($tempData);
    const humidData = @json($humidData);
    const soilData  = @json($soilData);
    const mode      = @json($mode);

    const chartType = mode === 'daily' ? 'line' : 'bar';

    const commonOpts = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { ticks: { font: { size: 8 }, maxRotation: 45 }, grid: { display: false } },
            y: { ticks: { font: { size: 9 } }, grid: { color: '#e0e0e0' } }
        }
    };

    function makeDataset(data, rgbColor) {
        return {
            data,
            borderColor: 'rgb(' + rgbColor + ')',
            backgroundColor: chartType === 'line'
                ? 'rgba(' + rgbColor + ', 0.15)'
                : 'rgba(' + rgbColor + ', 0.75)',
            borderWidth: chartType === 'line' ? 2 : 1,
            borderRadius: chartType === 'bar' ? 4 : 0,
            pointRadius: chartType === 'line' ? 3 : 0,
            fill: chartType === 'line',
            tension: 0.3,
        };
    }

    new Chart(document.getElementById('tempChart'),  { type: chartType, data: { labels, datasets: [makeDataset(tempData,  '46,158,62')]  }, options: commonOpts });
    new Chart(document.getElementById('humidChart'), { type: chartType, data: { labels, datasets: [makeDataset(humidData, '14,165,233')] }, options: commonOpts });
    new Chart(document.getElementById('soilChart'),  { type: chartType, data: { labels, datasets: [makeDataset(soilData,  '245,158,11')] }, options: commonOpts });

    // ── Auto-refresh every 2 seconds ─────────────────────────────────────
    const LIVE_URL = "{{ route('dashboard.live') }}";

    function updateChange(el, val) {
        if (!el || val === null || val === undefined) return;
        el.textContent = (val > 0 ? '+' : '') + val + ' %';
        el.className = 'stat-change ' + (val < 0 ? 'negative' : val > 0 ? 'positive' : 'neutral');
    }

    function updateDevice(cardId, badgeId, isOn) {
        const card  = document.getElementById(cardId);
        const badge = document.getElementById(badgeId);
        if (!card || !badge) return;
        card.className  = card.className.replace(/\bon\b|\boff\b/, isOn ? 'on' : 'off');
        badge.textContent = isOn ? 'ON' : 'OFF';
        badge.className = 'device-badge ' + (isOn ? 'badge-on' : 'badge-off');
    }

    setInterval(function () {
        fetch(LIVE_URL)
            .then(r => r.json())
            .then(d => {
                if (!d.success) return;

                // Stat cards
                document.getElementById('live-temp').textContent  = d.temperature + ' °C';
                document.getElementById('live-humid').textContent = d.humidity    + ' %';
                document.getElementById('live-soil').textContent  = d.soil        + ' %';

                updateChange(document.getElementById('live-temp-change'),  d.temp_change);
                updateChange(document.getElementById('live-humid-change'), d.humid_change);
                updateChange(document.getElementById('live-soil-change'),  d.soil_change);

                // Device badges
                updateDevice('pump-card',  'pump-badge', d.pump_on);
                updateDevice('mist-card',  'mist-badge', d.mist_on);
            })
            .catch(() => {}); // silent fail
    }, 2000);
</script>
@endpush

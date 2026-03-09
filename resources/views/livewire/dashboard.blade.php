<div>
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Visión general del estado operacional de vuelos</p>
    </div>

    <!-- ── Métricas ────────────────────────────────────── -->
    <div class="row g-4 mb-5">
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="metric-card-icon" style="background:#eef2ff;">
                    <i class="fa-solid fa-plane" style="color:#6366f1;"></i>
                </div>
                <div class="metric-card-label">Total Vuelos</div>
                <div class="metric-card-value">{{ $totalFlights }}</div>
                <div class="metric-card-accent" style="background: linear-gradient(90deg,#6366f1,#8b5cf6);"></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="metric-card-icon" style="background:#ecfdf5;">
                    <i class="fa-solid fa-calendar-day" style="color:#10b981;"></i>
                </div>
                <div class="metric-card-label">Vuelos Hoy</div>
                <div class="metric-card-value">{{ $flightsToday }}</div>
                <div class="metric-card-accent" style="background: linear-gradient(90deg,#10b981,#34d399);"></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="metric-card-icon" style="background:#eff6ff;">
                    <i class="fa-solid fa-calendar-week" style="color:#3b82f6;"></i>
                </div>
                <div class="metric-card-label">Últimos 7 Días</div>
                <div class="metric-card-value">{{ $flightsLast7 }}</div>
                <div class="metric-card-accent" style="background: linear-gradient(90deg,#3b82f6,#60a5fa);"></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="metric-card-icon" style="background:#fef3c7;">
                    <i class="fa-solid fa-clock" style="color:#f59e0b;"></i>
                </div>
                <div class="metric-card-label">Pendientes</div>
                <div class="metric-card-value">{{ $pendingCount }}</div>
                <div class="metric-card-accent" style="background: linear-gradient(90deg,#f59e0b,#fbbf24);"></div>
            </div>
        </div>
    </div>

    <!-- ── Chart + Recent ──────────────────────────────── -->
    <div class="row g-4">
        <!-- Chart -->
        <div class="col-xl-5">
            <div class="app-card" style="height:100%;">
                <div class="app-card-header">
                    <span class="app-card-title">Actividad — Últimos 7 días</span>
                </div>
                <div class="app-card-body">
                    <canvas id="flightsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent flights -->
        <div class="col-xl-7">
            <div class="app-card">
                <div class="app-card-header">
                    <span class="app-card-title">Vuelos Recientes</span>
                    <a href="{{ route('flights.index') }}" class="btn btn-sm btn-secondary">Ver todos</a>
                </div>
                <div style="overflow-x:auto;">
                    <table class="app-table" id="dashTable">
                        <thead>
                            <tr>
                                <th>Vuelo</th>
                                <th>Fecha</th>
                                <th>Aerolínea</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($flights as $flight)
                            <tr>
                                <td class="fw-semibold">{{ $flight->flight_number }}</td>
                                <td style="color:var(--text-secondary);">{{ $flight->flight_date->format('d/m/Y') }}</td>
                                <td>{{ $flight->airline->name ?? '—' }}</td>
                                <td>
                                    @if($flight->status === 'pending')
                                        <span class="status-pill status-pending">Pendiente</span>
                                    @elseif($flight->status === 'approved')
                                        <span class="status-pill status-approved">Aprobado</span>
                                    @else
                                        <span class="status-pill status-billed">Facturado</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" style="text-align:center; padding:32px; color:var(--text-muted);">
                                    <i class="fa-solid fa-plane-slash" style="font-size:24px; display:block; margin-bottom:8px;"></i>
                                    No hay vuelos registrados
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('flightsChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Vuelos',
                data: @json($chartData),
                backgroundColor: 'rgba(99,102,241,.15)',
                borderColor: '#6366f1',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleFont: { family: 'Inter', weight: '600', size: 12 },
                    bodyFont:  { family: 'Inter', size: 12 },
                    padding: 10,
                    cornerRadius: 8,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0, font: { family: 'Inter', size: 11 }, color: '#94a3b8' },
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    ticks: { font: { family: 'Inter', size: 11 }, color: '#94a3b8' },
                    grid: { display: false }
                }
            }
        }
    });
});
</script>
@endpush

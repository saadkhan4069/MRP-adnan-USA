@extends('backend.layout.main')

@section('content')
<x-success-message key="message" />
<x-error-message key="not_permitted" />

<style>
    .stat-card {
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    .stat-card .icon {
        font-size: 40px;
        opacity: 0.8;
    }
    .stat-card.total { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
    .stat-card.pending { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
    .stat-card.transit { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; }
    .stat-card.delivered { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; }
    .stat-card.returned { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; }
    .stat-card.cancelled { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333; }
    .stat-card.value { background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); color: #333; }
    
    .chart-container {
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
</style>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-0"><i class="fa fa-dashboard"></i> Shipment Dashboard</h4>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card total">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Total Shipments</h6>
                        <h2 class="mb-0">{{ number_format($totalShipments) }}</h2>
                    </div>
                    <div class="icon">
                        <i class="fa fa-truck"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card pending">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Pending</h6>
                        <h2 class="mb-0">{{ number_format($pendingShipments) }}</h2>
                    </div>
                    <div class="icon">
                        <i class="fa fa-clock-o"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card transit">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">In Transit</h6>
                        <h2 class="mb-0">{{ number_format($inTransitShipments) }}</h2>
                    </div>
                    <div class="icon">
                        <i class="fa fa-road"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card delivered">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Delivered</h6>
                        <h2 class="mb-0">{{ number_format($deliveredShipments) }}</h2>
                    </div>
                    <div class="icon">
                        <i class="fa fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="stat-card returned">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Returned</h6>
                        <h2 class="mb-0">{{ number_format($returnedShipments) }}</h2>
                    </div>
                    <div class="icon">
                        <i class="fa fa-undo"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card cancelled">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Cancelled</h6>
                        <h2 class="mb-0">{{ number_format($cancelledShipments) }}</h2>
                    </div>
                    <div class="icon">
                        <i class="fa fa-times-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card value">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Total Value</h6>
                        <h2 class="mb-0">${{ number_format($totalValue, 2) }}</h2>
                    </div>
                    <div class="icon">
                        <i class="fa fa-dollar"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row">
        <div class="col-md-6">
            <div class="chart-container">
                <h5 class="mb-3">Shipments by Status</h5>
                <div style="position: relative; height: 300px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="chart-container">
                <h5 class="mb-3">Monthly Shipments (Last 6 Months)</h5>
                <div style="position: relative; height: 300px;">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Tables Row --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-list"></i> Recent Shipments</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Reference</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentShipments as $shipment)
                                <tr>
                                    <td>#{{ $shipment->id }}</td>
                                    <td>{{ $shipment->reference_no ?? '—' }}</td>
                                    <td>{{ $shipment->customer->name ?? '—' }}</td>
                                    <td>
                                        @php
                                            $statusMap = [
                                                1 => ['label' => 'Pending', 'class' => 'badge-warning'],
                                                2 => ['label' => 'In Transit', 'class' => 'badge-info'],
                                                3 => ['label' => 'Delivered', 'class' => 'badge-success'],
                                                4 => ['label' => 'Returned', 'class' => 'badge-danger'],
                                                5 => ['label' => 'Cancelled', 'class' => 'badge-secondary'],
                                            ];
                                            $status = $statusMap[$shipment->status] ?? ['label' => '—', 'class' => 'badge-secondary'];
                                        @endphp
                                        <span class="badge {{ $status['class'] }}">{{ $status['label'] }}</span>
                                    </td>
                                    <td>{{ $shipment->created_at->format('M d, Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No shipments found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-users"></i> Top Customers</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Shipments</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topCustomers as $customer)
                                <tr>
                                    <td>{{ $customer->customer->name ?? 'Unknown' }}</td>
                                    <td><span class="badge badge-primary">{{ $customer->shipment_count }}</span></td>
                                    <td>
                                        @if($customer->customer_id)
                                        <a href="{{ route('shipment.index') }}?customer_id={{ $customer->customer_id }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">No customers found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // Status Chart (Doughnut)
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($statusData)) !!},
            datasets: [{
                data: {!! json_encode(array_values($statusData)) !!},
                backgroundColor: [
                    '#f093fb',
                    '#4facfe',
                    '#43e97b',
                    '#fa709a',
                    '#a8edea'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 1.5,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Monthly Chart (Line)
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($monthlyData, 'month')) !!},
            datasets: [{
                label: 'Shipments',
                data: {!! json_encode(array_column($monthlyData, 'count')) !!},
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 1.5,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>

@endsection


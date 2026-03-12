@extends('layouts.admin')

@section('content')

<h4 class="fw-bold mb-4">Overview</h4>

<div class="row g-4">

    <div class="col-md-3">
        <div class="card stat-card p-4">
            <small class="text-muted">Total Pegawai</small>
            <h3 class="fw-bold">{{ $totalPegawai }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card p-4">
            <small class="text-muted">Pegawai Aktif</small>
            <h3 class="fw-bold text-success">{{ $pegawaiAktif }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card p-4">
            <small class="text-muted">Pegawai Non Aktif</small>
            <h3 class="fw-bold text-danger">{{ $pegawaiNonAktif }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card p-4">
            <small class="text-muted">Total Satker</small>
            <h3 class="fw-bold text-warning">{{ $totalSatker }}</h3>
        </div>
    </div>

</div>

<div class="row mt-4 g-4">

    <div class="col-md-6">
        <div class="card stat-card p-4">
            <h6 class="fw-semibold mb-3">Status Pegawai</h6>
            <div style="position:relative; height:220px;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card stat-card p-4">
            <h6 class="fw-semibold mb-3">Pegawai per Satker</h6>
            <div style="position:relative; height:220px;">
                <canvas id="satkerChart"></canvas>
            </div>
        </div>
    </div>

</div>

<div class="row mt-4">

    <div class="col-md-12">
        <div class="card stat-card p-4">
            <h6 class="fw-semibold mb-3">Pendidikan Pegawai</h6>
            <div style="position:relative; height:220px;">
                <canvas id="pendidikanChart"></canvas>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: ['Aktif', 'Non Aktif'],
        datasets: [{
            data: [{{ $pegawaiAktif }}, {{ $pegawaiNonAktif }}],
            backgroundColor: ['#16a34a', '#dc2626']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

new Chart(document.getElementById('satkerChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($pegawaiPerSatker->pluck('nama_satker')) !!},
        datasets: [{
            label: 'Jumlah Pegawai',
            data: {!! json_encode($pegawaiPerSatker->pluck('pegawai_count')) !!},
            backgroundColor: '#2563eb'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});

new Chart(document.getElementById('pendidikanChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($pendidikanStats->keys()) !!},
        datasets: [{
            label: 'Total',
            data: {!! json_encode($pendidikanStats->values()) !!},
            backgroundColor: '#7c3aed'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});
</script>

@endsection

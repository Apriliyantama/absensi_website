@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard Admin</h1>
@stop

@section('content')

    <div class="row">

        {{-- INFO BOX --}}
        <div class="col-lg-3 col-md-6 col-12">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $studentCount }}</h3>
                    <p>Total Murid</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-12">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $teacherCount }}</h3>
                    <p>Total Guru</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-12">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $classCount }}</h3>
                    <p>Total Kelas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-school"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-12">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $attendanceToday }}</h3>
                    <p>Absensi Hari Ini</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
        </div>

    </div>

    {{-- CHART --}}
    {{-- <div class="row">

        <div class="col-lg-6 col-12">
            <div class="card shadow-sm">

                <div class="card-header">
                    <h3 class="card-title">
                        Statistik Absensi Hari Ini
                    </h3>
                </div>

                <div class="card-body">
                    <div style="height:450px;">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                </div>

            </div>
        </div>

        <div class="col-lg-6 col-12">
            <div class="card shadow-sm">

                <div class="card-header">
                    <h3 class="card-title">
                        Jumlah Murid per Kelas
                    </h3>
                </div>

                <div class="card-body">
                    <div style="height:450px;">
                        <canvas id="classChart"></canvas>
                    </div>
                </div>

            </div>
        </div>

    </div> --}}

@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // new Chart(
        //     document.getElementById('attendanceChart'), {
        //         type: 'doughnut',
        //         data: {
        //             labels: [
        //                 'Hadir',
        //                 'Izin',
        //                 'Sakit',
        //                 'Alfa'
        //             ],

        //             datasets: [{
        //                 label: 'Jumlah',
        //                 data: [
        //                     {{ $hadir }},
        //                     {{ $izin }},
        //                     {{ $sakit }},
        //                     {{ $alfa }}
        //                 ],
        //                 backgroundColor: [
        //                     '#28a745',
        //                     '#17a2b8',
        //                     '#ffc107',
        //                     '#dc3545'
        //                 ]
        //             }]
        //         },
        //         options: {
        //             responsive: true,
        //             maintainAspectRatio: false,
        //             plugins: {
        //                 legend: {
        //                     position: 'bottom'
        //                 }
        //             }
        //         }
        //     }
        // );

        // new Chart(
        //     document.getElementById('classChart'), {
        //         type: 'pie',
        //         data: {
        //             labels: @json($classLabels),
        //             datasets: [{
        //                 label: 'Jumlah Murid',
        //                 data: @json($classTotals),
        //                 backgroundColor: [
        //                     '#007bff',
        //                     '#28a745',
        //                     '#ffc107',
        //                     '#dc3545',
        //                     '#6f42c1',
        //                     '#fd7e14'
        //                 ]
        //             }]
        //         },
        //         options: {
        //             responsive: true,
        //             maintainAspectRatio: false,
        //             plugins: {
        //                 legend: {
        //                     position: 'bottom'
        //                 }
        //             }
        //         }
        //     }
        // );
    </script>
@stop

@extends('adminlte::page')

@section('title', 'Detail Kehadiran')

@section('content_header')
    <h1>Detail Kehadiran</h1>
@stop

@section('content')

    <a href="{{ route('teacher.attendance.overview') }}" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left"></i>
        Kembali
    </a>

    <div class="card mb-3">

        <div class="card-body">
            <p>
                <strong>Mata Pelajaran:</strong>
                {{ $session->schedule->subject->name }}
            </p>
            <p>
                <strong>Kelas:</strong>
                {{ $session->schedule->class->grade }}
                {{ $session->schedule->class->name }}
            </p>
            <p>
                <strong>Mode GPS:</strong>
                {{ $session->gps_enabled ? 'ON' : 'OFF' }}
            </p>
            <p>
                <strong>Tanggal:</strong>
                {{ $session->date }}
            </p>
        </div>

    </div>

    <div class="card">
        <div class="card-body">
            <table id="detailTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Status</th>
                        <th>Jam</th>
                        <th>Metode</th>
                        <th>Jarak</th>
                        <th>Confidence</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($session->attendances as $attendance)
                        <tr>
                            <td>
                                {{ $loop->iteration }}
                            </td>
                            <td>
                                {{ $attendance->user->name ?? '-' }}
                            </td>
                            <td>
                                {{ ucfirst($attendance->status) }}
                            </td>
                            <td>
                                {{ $attendance->check_in_time }}
                            </td>
                            <td>
                                {{ $attendance->method }}
                            </td>
                            <td>
                                {{ round($attendance->distance) }} m
                            </td>
                            <td>
                                {{ $attendance->confidence_score }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@stop

@section('js')

    <script>
        $(function() {

            $('#detailTable').DataTable({
                responsive: true,
                autoWidth: false,
            });

        });
    </script>

@stop

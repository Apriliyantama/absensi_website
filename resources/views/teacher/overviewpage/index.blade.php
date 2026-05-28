@extends('adminlte::page')

@section('title', 'Overview Kehadiran')

@section('content_header')
    <h1>Overview Kehadiran</h1>
@stop

@section('content')

    <div class="card">

        <div class="card-body">

            <table id="overviewTable" class="table table-bordered table-striped">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Mata Pelajaran</th>
                        <th>Kelas</th>
                        <th>Mode GPS</th>
                        <th>Hadir</th>
                        <th>Alfa</th>
                        <th>Status</th>
                        <th>Detail</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($sessions as $session)
                        <tr>
                            <td>
                                {{ $loop->iteration }}
                            </td>
                            <td>
                                {{ $session->date }}
                            </td>
                            <td>
                                {{ $session->schedule->subject->name ?? '-' }}
                            </td>
                            <td>
                                {{ $session->schedule->class->grade ?? '-' }}
                                {{ $session->schedule->class->name ?? '-' }}
                            </td>
                            <td>
                                @if ($session->gps_enabled)
                                    <p>ON</p>
                                @else
                                    <p>OFF</p>
                                @endif
                            </td>
                            <td>
                                {{ $session->attendances->where('status', 'hadir')->count() }}
                            </td>
                            <td>
                                {{ $session->attendances->where('status', 'alfa')->count() }}
                            </td>
                            <td>
                                @if ($session->status == 'open')
                                    <span class="badge badge-success">
                                        Open
                                    </span>
                                @else
                                    <span class="badge badge-secondary">
                                        Closed
                                    </span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('teacher.attendance.overview.show', $session->id) }}"
                                    class="btn btn-primary btn-sm">
                                    Detail
                                </a>
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

            $('#overviewTable').DataTable({
                responsive: true,
                autoWidth: false,
            });

        });
    </script>

@stop

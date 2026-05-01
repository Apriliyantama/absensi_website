@extends('adminlte::page')

@section('title', 'Dashboard Guru')

@section('content')
    <h4>Jadwal Hari Ini</h4>

    <div class="row">
        @forelse($schedules as $schedule)
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">

                        <h5>{{ $schedule->subject->name }}</h5>

                        <p>
                            {{ $schedule->class->grade }}
                            {{ $schedule->class->name }}
                        </p>

                        <p>
                            {{ $schedule->start_time }} - {{ $schedule->end_time }}
                        </p>

                        <button class="btn btn-success btn-sm">
                            Mulai Absensi
                        </button>

                    </div>
                </div>
            </div>
        @empty
            <p>Tidak ada jadwal hari ini</p>
        @endforelse
    </div>
@stop
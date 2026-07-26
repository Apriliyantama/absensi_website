@extends('adminlte::page')

@section('title', 'Dashboard Guru')

@section('content')
    <br>
    <div class="row">

        <!-- LEFT -->
        <div class="col-md-4">
            {{-- <h4 class="mb-3">Jadwal Hari Ini</h4> --}}

            @foreach ($schedules as $schedule)
                <div class="card mb-3 shadow-sm border-0 rounded-lg">
                    <div class="card-body">
                        <h5 class="font-weight-bold">
                            {{ $schedule->subject->name }}
                        </h5>
                        <p class="mb-1 text-muted">
                            {{ $schedule->class->grade }}
                            {{ $schedule->class->name }}
                        </p>
                        <p class="font-weight-bold">
                            {{ $schedule->start_time }}
                            -
                            {{ $schedule->end_time }}
                        </p>

                        {{-- <div class="form-group">
                            <label class="font-weight-bold">
                                Mode Absensi:
                            </label>

                            <select class="form-control gps-mode">
                                <option value="1">
                                    GPS ON (Sekolah)
                                </option>

                                <option value="0">
                                    GPS OFF (Online)
                                </option>
                            </select>
                        </div> --}}

                        @if (!$schedule->is_started)
                            <button class="btn btn-secondary w-100" disabled>
                                Pelajaran Berikutnya
                            </button>
                        @elseif ($schedule->active_session)
                            <span class="badge badge-success mb-2">
                                Absensi Sedang Berjalan
                            </span>
                            <button class="btn btn-warning w-100 btn-end-attendance"
                                data-id="{{ $schedule->active_session->id }}">
                                Tutup Absensi
                            </button>
                        @elseif($schedule->closed_session)
                            <span class="badge badge-dark mb-2">
                                Sesi Selesai
                            </span>
                            <button class="btn btn-dark w-100" disabled>
                                Absensi Ditutup
                            </button>
                        @else
                            <button class="btn btn-success w-100 btn-start-attendance" data-id="{{ $schedule->id }}">
                                Mulai Absensi
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach

        </div>

        <!-- RIGHT -->
        <div class="col-md-8">

            @php
                $activeSchedule = $schedules->first(function ($s) {
                    return $s->active_session;
                });

                $nextSchedule = $schedules->first(function ($s) {
                    return !$s->active_session && !$s->closed_session;
                });
            @endphp

            {{-- ACTIVE SESSION --}}
            @if ($activeSchedule)
                <div class="card shadow-sm border-0 rounded-lg">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="font-weight-bold">
                                    Jadwal Berlangsung
                                </h5>
                                <h4>
                                    {{ $activeSchedule->subject->name }}
                                </h4>
                                <h5>
                                    {{ $activeSchedule->start_time }}
                                    -
                                    {{ $activeSchedule->end_time }}
                                </h5>
                                {{-- MODE SESSION --}}
                                @if ($activeSchedule->active_session)

                                    @if ($activeSchedule->active_session->gps_enabled)
                                        <span class="badge badge-success mt-2">
                                            GPS ON • Area Sekolah
                                        </span>
                                    @else
                                        <span class="badge badge-warning mt-2">
                                            GPS OFF • Online / Luar Sekolah
                                        </span>
                                    @endif

                                @endif
                            </div>
                            <div class="text-right">
                                <h4>
                                    {{ $activeSchedule->class->grade }}
                                    {{ $activeSchedule->class->name }}
                                </h4>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered text-center">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>H</th>
                                        <th>I</th>
                                        <th>S</th>
                                        <th>A</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($activeSchedule->students as $student)
                                        @php
                                            $status = $student->attendance->status ?? null;
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="text-left">{{ $student->user->name }}</td>
                                            @foreach (['hadir', 'izin', 'sakit', 'alfa'] as $item)
                                                <td>
                                                    <input type="radio" class="attendance-radio"
                                                        name="attendance_{{ $activeSchedule->id }}_{{ $student->id }}"
                                                        value="{{ $item }}" data-user="{{ $student->user_id }}"
                                                        data-schedule="{{ $activeSchedule->id }}"
                                                        {{ $status === $item ? 'checked' : '' }}
                                                        {{ !$activeSchedule->active_session ? 'disabled' : '' }}>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                {{-- NEXT SCHEDULE --}}
            @elseif ($nextSchedule)
                <div class="card shadow-sm border-0 rounded-lg">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="font-weight-bold text-muted">
                                    Jadwal Berikutnya
                                </h6>
                                <h3>
                                    {{ $nextSchedule->subject->name }}
                                </h3>
                                <p>
                                    {{ $nextSchedule->start_time }}
                                    -
                                    {{ $nextSchedule->end_time }}
                                </p>
                            </div>
                            <div class="text-right">
                                <h5>
                                    {{ $nextSchedule->class->grade }}
                                    {{ $nextSchedule->class->name }}
                                </h5>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered mt-3 text-center">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>H</th>
                                        <th>I</th>
                                        <th>S</th>
                                        <th>A</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($nextSchedule->students as $student)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="text-left">{{ $student->user->name }}</td>
                                            @foreach (['hadir', 'izin', 'sakit', 'alfa'] as $item)
                                                <td>
                                                    <input type="radio" disabled>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h3 class="text-muted">
                            Tidak ada sesi aktif saat ini
                        </h3>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // START ATTENDANCE
        $(document).on('click', '.btn-start-attendance', function() {
            let button = $(this);
            let scheduleId = button.data('id');
            Swal.fire({
                title: 'Mulai Absensi?',
                text: 'Pilih mode absensi',
                icon: 'question',
                input: 'select',
                inputOptions: {
                    1: 'GPS ON (Sekolah)',
                    0: 'GPS OFF (Online)'
                },
                inputValue: 1,
                showCancelButton: true,
                confirmButtonText: 'Mulai',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (!result.isConfirmed) return;
                button.prop('disabled', true);
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                $.ajax({
                    url: "{{ route('teacher.attendance.start') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        lesson_schedule_id: scheduleId,
                        gps_enabled: result.value
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        setTimeout(() => {
                            location.reload();
                        }, 1200);
                    },
                    error: function(err) {
                        button.prop('disabled', false);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: err.responseJSON?.message ??
                                'Terjadi kesalahan'
                        });
                    }
                });
            });
        });

        // UPDATE STATUS
        $(document).on('change', '.attendance-radio', function() {
            let radio = $(this);
            $.ajax({
                url: "{{ route('teacher.attendance.update-status') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    user_id: radio.data('user'),
                    lesson_schedule_id: radio.data('schedule'),
                    status: radio.val(),
                },
                success: function(res) {
                    toastr.success(res.message);
                },
                error: function(err) {
                    toastr.error(
                        err.responseJSON?.message ??
                        'Gagal update status'
                    );
                }
            });
        });

        // END ATTENDANCE
        $(document).on('click', '.btn-end-attendance', function() {
            let button = $(this);
            let id = button.data('id');
            Swal.fire({
                title: 'Tutup Absensi?',
                text: 'Sesi absensi akan ditutup',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Tutup',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545',
            }).then((result) => {
                if (!result.isConfirmed) return;
                button.prop('disabled', true);
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang menutup sesi absensi',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                $.ajax({
                    url: "{{ url('/teacher/attendance-session/end') }}/" + id,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        setTimeout(() => {
                            location.reload();
                        }, 1200);
                    },
                    error: function(err) {
                        button.prop('disabled', false);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: err.responseJSON?.message ??
                                'Terjadi kesalahan'
                        });
                    }
                });
            });
        });
    </script>
@stop

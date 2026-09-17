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

    <!-- MODAL UBAH PROFIL (NAMA & EMAIL) -->
    <div class="modal fade" id="profileModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title"><i class="fas fa-user mr-2"></i>Informasi Profil</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <!-- Route ke profile.update bawaan Breeze -->
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('patch')

                    <div class="modal-body">
                        <!-- Input Nama -->
                        <div class="form-group">
                            <label for="name">Nama Lengkap</label>
                            <!-- Value diisi otomatis dengan data user saat ini -->
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', Auth::user()->name) }}" required autofocus>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Input Email -->
                        <div class="form-group">
                            <label for="email">Alamat Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', Auth::user()->email) }}" required>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info">Simpan Profil</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--  -->

    <!-- MODAL UBAH PASSWORD -->
    <div class="modal fade" id="passwordModal" tabindex="-1" role="dialog" aria-labelledby="passwordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="passwordModalLabel"><i class="fas fa-lock mr-2"></i>Ubah Password</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form action="{{ route('password.update') }}" method="POST">
                    @csrf
                    @method('put')

                    <div class="modal-body">
                        <!-- Password Lama -->
                        <div class="form-group">
                            <label for="current_password">Password Saat Ini</label>
                            <div class="input-group">
                                <input type="password" name="current_password"
                                    class="form-control @if ($errors->updatePassword->has('current_password')) is-invalid @endif" required>
                                <div class="input-group-append">
                                    <span class="input-group-text toggle-password" style="cursor: pointer;">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                                @if ($errors->updatePassword->has('current_password'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->updatePassword->first('current_password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Password Baru -->
                        <div class="form-group">
                            <label for="password">Password Baru</label>
                            <div class="input-group">
                                <input type="password" name="password"
                                    class="form-control @if ($errors->updatePassword->has('password')) is-invalid @endif" required>
                                <div class="input-group-append">
                                    <span class="input-group-text toggle-password" style="cursor: pointer;">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                                @if ($errors->updatePassword->has('password'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->updatePassword->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Konfirmasi Password Baru -->
                        <div class="form-group">
                            <label for="password_confirmation">Konfirmasi Password Baru</label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text toggle-password" style="cursor: pointer;">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- -->

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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // PROFIL dan PASSWORD
        $(document).ready(function() {
            $('#btnUbahPassword').on('click', function(e) {
                e.preventDefault();
                $('#passwordModal').modal('show');
            });

            $('#btnUbahProfil').on('click', function(e) {
                e.preventDefault();
                $('#profileModal').modal('show');
            });

            @if ($errors->updatePassword->any())
                $('#passwordModal').modal('show');
            @endif

            @if ($errors->has('name') || $errors->has('email'))
                $('#profileModal').modal('show');
            @endif

            @if (session('status') === 'password-updated')
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Password Anda telah berhasil diperbarui.',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
            @endif

            @if (session('status') === 'profile-updated')
                Swal.fire({
                    icon: 'success',
                    title: 'Profil Diperbarui!',
                    text: 'Nama dan Email Anda berhasil disimpan.',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
            @endif

            $('.toggle-password').click(function() {
                let input = $(this).closest('.input-group').find('input');
                let icon = $(this).find('i');

                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
        });

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

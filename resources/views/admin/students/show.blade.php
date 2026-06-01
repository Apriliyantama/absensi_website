@extends('adminlte::page')

@section('title', 'Detail Murid')

@section('content')

    <br>

    <div class="card">
        <div class="mb-3">

            <button class="btn btn-warning editBtn" data-id="{{ $student->id }}">
                Edit Murid
            </button>
        </div>
        <div class="card-header">
            <h3 class="card-title">
                Detail Siswa
            </h3>
        </div>

        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th width="250">Nama</th>
                    <td>{{ $student->name }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $student->user->email ?? '-' }}</td>
                </tr>
                <tr>
                    <th>NIS</th>
                    <td>{{ $student->nis }}</td>
                </tr>
                <tr>
                    <th>Gender</th>
                    <td>{{ $student->gender }}</td>
                </tr>
                <tr>
                    <th>Tanggal Lahir</th>
                    <td>{{ $student->birth_date }}</td>
                </tr>
                <tr>
                    <th>Alamat</th>
                    <td>{{ $student->address }}</td>
                </tr>
                <tr>
                    <th>Kelas Saat Ini</th>
                    <td>
                        {{ $student->classRelation->grade ?? '-' }}
                        {{ $student->classRelation->name ?? '' }}
                    </td>
                </tr>
                <tr>
                    <th>Kelas Pilihan Saat Registrasi</th>
                    <td>
                        {{ $student->requestedClass->grade ?? '-' }}
                        {{ $student->requestedClass->name ?? '' }}
                    </td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        {{ $student->user->status ?? '-' }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).on(
            'click',
            '.editBtn',
            function() {

                let id = $(this).data('id');

                $.get(
                    '/admin/students/edit/' + id,
                    function(res) {

                        $('#student_id').val(res.id);

                        $('#name').val(res.name);
                        $('#nis').val(res.nis);
                        $('#gender').val(res.gender);
                        $('#birth_date').val(res.birth_date);
                        $('#address').val(res.address);

                        new bootstrap.Offcanvas(
                            '#offcanvasStudent'
                        ).show();
                    }
                );
            }
        );

        $('#saveStudent').click(function() {

            let id = $('#student_id').val();

            $.post(
                '/admin/students/update/' + id, {
                    _token: $('meta[name=csrf-token]').attr('content'),

                    name: $('#name').val(),
                    nis: $('#nis').val(),
                    gender: $('#gender').val(),
                    birth_date: $('#birth_date').val(),
                    address: $('#address').val()
                },

                function() {

                    $('#studentTable')
                        .DataTable()
                        .ajax.reload();

                    Swal.fire(
                        'Berhasil',
                        'Data murid diperbarui',
                        'success'
                    );
                }
            );
        });
    </script>
@stop

@extends('adminlte::page')

@section('title', 'Data Siswa')

@section('plugins.Datatables', true)

@section('content')
    <br>
    <h4>Tabel Data Siswa</h4>
    <div class="card">
        <div class="card-body">
            <button class="btn btn-success mb-3" id="btnPromote">
                Kenaikan Kelas
            </button>
            <div class="modal fade" id="promoteModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5>Bulk Naik Kelas</h5>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Dari Kelas</label>
                                <select id="from_class" class="form-control">
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}">
                                            {{ $class->grade }}
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Ke Kelas</label>
                                <select id="to_class" class="form-control">
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}">
                                            {{ $class->grade }}
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button id="savePromote" class="btn btn-success">
                                Proses
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <table id="studentTable" class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Kelas</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <div class="modal fade" id="modalStudent">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="studentForm">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Edit Siswa
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="student_id">
                        <div class="form-group">
                            <label>Nama</label>
                            <input type="text" id="name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>NIS</label>
                            <input type="text" id="nis" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Kelas</label>
                            <select id="class_id" class="form-control">
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}">
                                        {{ $class->grade }} {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Gender</label>
                            <select id="gender" class="form-control">
                                <option value="L">
                                    Laki-laki
                                </option>
                                <option value="P">
                                    Perempuan
                                </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tanggal Lahir</label>
                            <input type="date" id="birth_date" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Alamat</label>
                            <textarea id="address" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $('#studentTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.students.data') }}",
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'student_name',
                    name: 'students.name'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'class_name',
                    name: 'class_name'
                },
                {
                    data: 'status_badge',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        $('#studentTable').on(
            'click',
            '.editBtn',
            function() {

                let id = $(this).data('id');

                $.get(
                    '/admin/students/edit/' + id,
                    function(data) {

                        $('#student_id').val(data.id);
                        $('#name').val(data.name);
                        $('#nis').val(data.nis);
                        $('#gender').val(data.gender);
                        $('#birth_date').val(data.birth_date);
                        $('#address').val(data.address);
                        $('#class_id').val(data.class_id);
                        $('#modalStudent').modal('show');
                    }
                );
            }
        );

        $('#studentTable').on('click', '.delete', function() {

            let id = $(this).data('id');

            Swal.fire({
                title: 'Hapus siswa?',
                text: 'Data tidak bisa dikembalikan',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya'
            }).then((result) => {

                if (result.isConfirmed) {

                    $.ajax({
                        url: '/admin/students/delete/' + id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function() {

                            Swal.fire(
                                'Berhasil',
                                'Data siswa dihapus',
                                'success'
                            );

                            $('#studentTable')
                                .DataTable()
                                .ajax
                                .reload();
                        }
                    });
                }
            });
        });

        $('#studentForm').submit(function(e) {
            e.preventDefault();
            let id = $('#student_id').val();
            $.ajax({
                url: '/admin/students/update/' + id,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    name: $('#name').val(),
                    nis: $('#nis').val(),
                    gender: $('#gender').val(),
                    birth_date: $('#birth_date').val(),
                    address: $('#address').val(),
                    class_id: $('#class_id').val()
                },

                success: function(response) {

                    $('#modalStudent').modal('hide');
                    $('#studentTable')
                        .DataTable()
                        .ajax.reload();

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        });

        $('#btnPromote').click(function() {
            $('#promoteModal').modal('show');
        });

        $('#savePromote').click(function() {

            $.post(
                '/admin/students/promote', {
                    _token: '{{ csrf_token() }}',
                    from_class: $('#from_class').val(),
                    to_class: $('#to_class').val()
                },
                function() {

                    Swal.fire(
                        'Berhasil',
                        'Kenaikan kelas selesai',
                        'success'
                    );

                    $('#promoteModal').modal('hide');

                    $('#studentTable')
                        .DataTable()
                        .ajax
                        .reload();
                }
            );
        });
    </script>
@stop

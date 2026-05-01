@extends('adminlte::page')

@section('title', 'Teacher')

@section('plugins.Datatables', true)

@section('content')
    <br>
    <h4>Tabel Pengajar</h4>

    <div class="card">
        <div class="card-body">
            <button class="btn btn-primary mb-3" id="add">Tambah Guru</button>

            <table id="table" class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>NIP/NIK</th>
                        <th>Email</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modalForm">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="form">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Form Guru</h5>

                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" id="id">

                        <div class="form-group">
                            <label>Nama</label>
                            <input type="text" id="name" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>NIP/NIK</label>
                            <input type="text" id="nip" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" id="email" class="form-control">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {

            let table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.teachers.data') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'nip'
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

        });

        let save_method;

        $('#add').click(function() {
            save_method = 'add';
            $('#form')[0].reset();
            $('#modalTitle').text('Tambah Guru');
            $('#modalForm').modal('show');
        });

        // edit
        $('#table').on('click', '.edit', function() {
            let id = $(this).data('id');
            save_method = 'edit';

            $.get('/admin/teachers/edit/' + id, function(data) {
                $('#id').val(data.id);
                $('#name').val(data.name);
                $('#nip').val(data.nip);
                $('#email').val(data.email);

                $('#modalTitle').text('Edit Guru');
                $('#modalForm').modal('show');
            });
        });

        //submit (store + update)
        $('#form').submit(function(e) {
            e.preventDefault();

            let id = $('#id').val();
            let url = save_method == 'add' ?
                "{{ route('admin.teachers.store') }}" :
                "/admin/teachers/update/" + id;

            $.post(url, {
                _token: "{{ csrf_token() }}",
                name: $('#name').val(),
                nip: $('#nip').val(),
                email: $('#email').val()
            }, function(res) {

                $('#modalForm').modal('hide');
                $('#table').DataTable().ajax.reload();

                //kondisi add vs edit
                if (save_method == 'add') {

                    Swal.fire({
                        title: 'Akun Guru Dibuat',
                        html: `
                            Email: ${$('#email').val()}<br>
                            Password: <b>${res.password}</b>
                        `,
                        icon: 'success'
                    });

                } else {
                    swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Data Guru berhasil diupdate',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        });

        //delete
        $('#table').on('click', '.delete', function() {
            let id = $(this).data('id');

            Swal.fire({
                title: 'Yakin?',
                text: "Data akan dihapus!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/teachers/delete/' + id,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function() {
                            $('#table').DataTable().ajax.reload();

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Data guru berhasil dihapus',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    });
                }
            });
        });
    </script>
@stop

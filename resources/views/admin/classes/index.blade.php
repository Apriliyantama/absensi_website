@extends('adminlte::page')

@section('title', 'Kelas')

@section('plugins.Datatables', true)

@section('content')

    <button class="btn btn-primary mb-3" id="add">Tambah Kelas</button>

    <table id="table" class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Kelas</th>
                <th>Aksi</th>
            </tr>
        </thead>
    </table>


    {{-- Modal --}}
    <div class="modal fade" id="modal">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 id="modal-title">Tambah Kelas</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="id">

                    <div class="form-group">
                        <label>Grade</label>
                        <input type="text" id="grade" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" id="name" class="form-control">
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" id="save">Simpan</button>
                </div>

            </div>
        </div>
    </div>

@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {

            $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.classes.data') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'full_name'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

        });

        //Tambah Data Kelas
        $('#add').click(function() {
            $('#modal-title').text('Tambah Kelas');
            $('#id').val('');
            $('#grade').val('');
            $('#name').val('');
            $('#modal').modal('show');
        });

        //SIMPAN
        $('#save').click(function() {

            let id = $('#id').val();

            let url = id ? '/admin/classes/update/' + id :
                '/admin/classes/store';

            $.post(url, {
                _token: '{{ csrf_token() }}',
                grade: $('#grade').val(),
                name: $('#name').val()
            }, function() {

                $('#modal').modal('hide');
                $('#table').DataTable().ajax.reload();

                Swal.fire('Berhasil!', '', 'success');
            });

        });

        //EDIT
        $('#table').on('click', '.edit', function() {
            let id = $(this).data('id');

            $.get('/admin/classes/edit/' + id, function(res) {

                $('#modal-title').text('Edit Kelas');
                $('#id').val(res.id);
                $('#grade').val(res.grade);
                $('#name').val(res.name);

                $('#modal').modal('show');
            });
        });

        //DELETE
        $('#table').on('click', '.delete', function() {
            let id = $(this).data('id');

            Swal.fire({
                title: 'Hapus?',
                showCancelButton: true
            }).then((result) => {
                if (result.isConfirmed) {

                    $.ajax({
                        url: '/admin/classes/delete/' + id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function() {
                            $('#table').DataTable().ajax.reload();
                        }
                    });

                }
            });
        });
    </script>
@stop

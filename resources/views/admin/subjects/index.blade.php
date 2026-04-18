@extends('adminlte::page')

@section('title', 'Mata Pelajaran')

@section('plugins.Datatables', true)

@section('content')

    <button class="btn btn-primary mb-3" id="add">Tambah Mapel</button>

    <table id="table" class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Mapel</th>
                <th>Aksi</th>
            </tr>
        </thead>
    </table>

    <!-- Modal -->
    <div class="modal fade" id="modal">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 id="modal-title">Tambah Mapel</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="id">
                    <input type="text" id="name" class="form-control" placeholder="Nama Mapel">
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

            let table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.subjects.data') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            // ADD
            $('#add').click(function() {
                $('#modal-title').text('Tambah Mapel');
                $('#id').val('');
                $('#name').val('');
                $('#modal').modal('show');
            });

            // SAVE
            $('#save').click(function() {

                let id = $('#id').val();

                let url = id ? '/admin/subjects/update/' + id :
                    '/admin/subjects/store';

                $.post(url, {
                    _token: '{{ csrf_token() }}',
                    name: $('#name').val()
                }, function() {

                    $('#modal').modal('hide');
                    table.ajax.reload();

                    Swal.fire('Berhasil!', '', 'success');
                });

            });

            // EDIT
            $('#table').on('click', '.edit', function() {
                let id = $(this).data('id');

                $.get('/admin/subjects/edit/' + id, function(res) {
                    $('#modal-title').text('Edit Mapel');
                    $('#id').val(res.id);
                    $('#name').val(res.name);
                    $('#modal').modal('show');
                });
            });

            // DELETE
            $('#table').on('click', '.delete', function() {
                let id = $(this).data('id');

                Swal.fire({
                    title: 'Hapus?',
                    showCancelButton: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/admin/subjects/delete/' + id,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                _method: 'DELETE' // 🔥 penting
                            },
                            success: function() {
                                table.ajax.reload();
                                Swal.fire('Berhasil dihapus!', '', 'success');
                            }
                        });
                    }
                });
            });

        });
    </script>
@stop

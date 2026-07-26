@extends('adminlte::page')

@section('title', 'Jadwal')

@section('plugins.Datatables', true)

@section('content')
    <br>
    <h4>Tabel Jadwal</h4>

    <div class="card">
        <div class="card-body">
            <button class="btn btn-primary mb-3" id="add">Tambah Jadwal</button>

            <table id="table" class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kelas</th>
                        <th>Mapel</th>
                        <th>Guru</th>
                        <th>Hari</th>
                        <th>Jam</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- MODAL -->
    <div class="modal fade" id="modal">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 id="modal-title">Tambah Jadwal</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">

                    <input type="hidden" id="id">
                    <select id="class_id" class="form-control mb-2">
                        <option value="">Pilih Kelas</option>
                        @foreach ($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->grade }} {{ $c->name }}</option>
                        @endforeach
                    </select>

                    <select id="subject_id" class="form-control mb-2">
                        <option value="">Pilih Mapel</option>
                        @foreach ($subjects as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>

                    <select id="teacher_id" class="form-control mb-2">
                        <option value="">Pilih Guru</option>
                        @foreach ($teachers as $t)
                            <option value="{{ $t->id }}">
                                {{ $t->nip }} - {{ $t->name }}
                            </option>
                        @endforeach
                    </select>

                    <select id="day_of_week" class="form-control mb-2">
                        <option value="">Pilih Hari</option>
                        <option value="1">Senin</option>
                        <option value="2">Selasa</option>
                        <option value="3">Rabu</option>
                        <option value="4">Kamis</option>
                        <option value="5">Jumat</option>
                        <option value="6">Sabtu</option>
                    </select>

                    <input type="time" id="start_time" class="form-control mb-2">
                    <input type="time" id="end_time" class="form-control mb-2">
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
                ajax: '{{ route('admin.schedules.data') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'class',
                        name: 'class'
                    },
                    {
                        data: 'subject',
                        name: 'subject'
                    },
                    {
                        data: 'teacher',
                        name: 'teacher'
                    },
                    {
                        data: 'day',
                        name: 'day'
                    },
                    {
                        data: 'time',
                        name: 'time'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // ADD
            $('#add').click(function() {
                $('#modal-title').text('Tambah Jadwal');
                $('input, select').val('');
                $('#id').val('');
                $('#modal').modal('show');
            });

            // SAVE
            $('#save').click(function() {

                let id = $('#id').val();

                let url = id ? '/admin/schedules/update/' + id :
                    '/admin/schedules/store';

                $.post(url, {
                    _token: '{{ csrf_token() }}',
                    class_id: $('#class_id').val(),
                    subject_id: $('#subject_id').val(),
                    teacher_id: $('#teacher_id').val(),
                    day_of_week: $('#day_of_week').val(),
                    start_time: $('#start_time').val(),
                    end_time: $('#end_time').val(),
                }, function() {

                    $('#modal').modal('hide');
                    table.ajax.reload();

                    Swal.fire('Berhasil!', '', 'success');

                }).fail(function(xhr) {

                    let message = 'Terjadi kesalahan';

                    if (xhr.status === 422) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: message
                    });

                });

            });

            // EDIT
            $('#table').on('click', '.edit', function() {
                let id = $(this).data('id');

                $.get('/admin/schedules/edit/' + id, function(res) {

                    $('#modal-title').text('Edit Jadwal');
                    $('#id').val(res.id);

                    $('#class_id').val(res.class_id);
                    $('#subject_id').val(res.subject_id);
                    $('#teacher_id').val(res.teacher_id);
                    $('#day_of_week').val(res.day_of_week);
                    $('#start_time').val(res.start_time);
                    $('#end_time').val(res.end_time);

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
                            url: '/admin/schedules/delete/' + id,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                _method: 'DELETE'
                            },
                            success: function() {
                                table.ajax.reload();
                                Swal.fire('Deleted', '', 'success');
                            }
                        });

                    }

                });

            });

        });
    </script>
@stop

@extends('adminlte::page')

@section('title', 'Validasi Murid')

@section('plugins.Datatables', true)

@section('content')
    <br>
    <h4>Tabel Validasi Murid</h4>

    <div class="card">
        <div class="card-body">
            <table id="table" class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>NIS</th>
                        <th>Kelas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
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
                ajax: '{{ route('admin.students.approval.data') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'nis'
                    },
                    {
                        data: 'class_dropdown'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            // APPROVE
            $('#table').on('click', '.approve', function() {
                let btn = $(this);
                let id = btn.data('id');

                let select = btn.closest('tr').find('.class-select');
                let class_id = select.val();
                select.prop('disabled', true);
                // let class_id = btn.closest('tr').find('.class-select').prop('disabled', true);

                Swal.fire({
                    title: 'Approve murid?',
                    showCancelButton: true
                }).then((result) => {
                    if (result.isConfirmed) {

                        $.post('/admin/students/approve/' + id, {
                            _token: '{{ csrf_token() }}',
                            class_id: class_id
                        }, function() {

                            let row = btn.closest('tr');

                            row.find('td:last').html(
                                '<button class="btn btn-success btn-sm" disabled>Approved</button>'
                            );

                            row.find('.class-select').prop('disabled', true);

                            Swal.fire('Approved!', '', 'success');

                            $('#table').DataTable().ajax.reload();
                        });
                    }
                });
            });

            // REJECT
            $('#table').on('click', '.reject', function() {
                let btn = $(this);
                let id = btn.data('id');

                Swal.fire({
                    title: 'Tolak murid?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya'
                }).then((result) => {
                    if (result.isConfirmed) {

                        $.post('/admin/students/reject/' + id, {
                            _token: '{{ csrf_token() }}'
                        }, function() {

                            btn.closest('td').html(
                                '<button class="btn btn-danger btn-sm" disabled>Rejected</button>'
                            );

                            Swal.fire({
                                icon: 'success',
                                title: 'Rejected',
                                timer: 1200,
                                showConfirmButton: false
                            });

                        });
                    }
                });
            });

            setTimeout(() => {
                $('#table').DataTable().ajax.reload();
            }, 1500);
        });
    </script>
@stop

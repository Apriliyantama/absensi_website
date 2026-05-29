@extends('adminlte::page')

@section('title', 'Data Murid')

@section('plugins.Datatables', true)

@section('content')
    <br>
    <h4>Tabel Data Murid</h4>

    <div class="card">
        <div class="card-body">
            <table id="studentTable" class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Kelas</th>
                        <th>Status</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop

@section('js')
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
                    name: 'user.email'
                },
                {
                    data: 'class_name',
                    name: 'classRelation.name'
                },
                {
                    data: 'status_badge',
                    name: 'user.status',
                    orderable: false,
                    searchable: false
                },
            ]
        });
    </script>
@stop

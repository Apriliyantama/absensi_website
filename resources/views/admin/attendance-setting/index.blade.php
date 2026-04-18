@extends('adminlte::page')

@section('title', 'Setting GPS')

@section('content')
<h4>Konfigurasi Absensi</h4>

<div class="card">
    <div class="card-body">

        <table class="table table-bordered">
            <tr>
                <th>Latitude</th>
                <td>{{ $setting->latitude ?? '-' }}</td>
            </tr>
            <tr>
                <th>Longitude</th>
                <td>{{ $setting->longitude ?? '-' }}</td>
            </tr>
            <tr>
                <th>Radius</th>
                <td>{{ $setting->radius_meter ?? '-' }} meter</td>
            </tr>
            <tr>
                <th>Face Threshold</th>
                <td>{{ $setting->face_threshold ?? '-' }}</td>
            </tr>
        </table>

        <button class="btn btn-primary mt-3" id="editBtn">
            Edit Setting
        </button>

    </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="form">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Edit Setting</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">

                    <div class="mb-2">
                        <label>Latitude</label>
                        <input type="text" name="latitude" class="form-control"
                            value="{{ $setting->latitude ?? '' }}">
                    </div>

                    <div class="mb-2">
                        <label>Longitude</label>
                        <input type="text" name="longitude" class="form-control"
                            value="{{ $setting->longitude ?? '' }}">
                    </div>

                    <div class="mb-2">
                        <label>Radius</label>
                        <input type="number" name="radius_meter" class="form-control"
                            value="{{ $setting->radius_meter ?? 100 }}">
                    </div>

                    <div class="mb-2">
                        <label>Face Threshold</label>
                        <input type="number" step="0.01" name="face_threshold" class="form-control"
                            value="{{ $setting->face_threshold ?? 0.8 }}">
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-success">Simpan</button>
                </div>

            </form>

        </div>
    </div>
</div>

@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$('#editBtn').click(function(){
    $('#modal').modal('show');
});

$('#form').submit(function(e){
    e.preventDefault();

    $.post('{{ route("admin.attendance.setting.store") }}', $(this).serialize(), function(res){

        Swal.fire('Berhasil', res.message, 'success');

        setTimeout(() => {
            location.reload(); // reload biar update tampil
        }, 800);

    });
});
</script>
@stop
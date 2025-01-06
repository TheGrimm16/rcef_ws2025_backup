@extends('layouts.index')

@section('styles')

@endsection

@section('content')
<div class="col-md-12 col-sm-12 col-xs-12">

    <!-- Import File -->
    <div class="x_panel">
        <div class="x_title">
            <h2>
                Import new Farmer
            </h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content form-horizontal form-label-left">
            <div class="row">
                <div class="col-auto pt-1 pl-5">
                    <h5>Import new Farmer </h5>
                </div>
                <div class="col-auto">
                    <form id="fileUpload" method="POST" action="{{ route('new_farmer_uploader') }}" enctype="multipart/form-data">
                        <div class="input-group">
                            <input type="file" class="custom-file-input form-control" id="inputFile" name="inputFile" required>
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" required>
                            <input type="hidden" name="function" value="rla" required>
                            <span class="input-group-btn">
                                <button class="btn btn-outline-primary" type="submit">Upload</button>
                            </span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div><br>


</div>

@endsection()

@push('scripts')
<script src="{{ asset('public/js/loadingoverlay.js') }}"></script>
<script>

    $("#inputFile").change(function(e) {
        e.preventDefault();
        var fileName = e.target.files[0].name;
        $("#inputFileLabel").html(fileName);
    });

  

</script>
@endpush
@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
  <style>
    ul.parsley-errors-list {
        list-style: none;
        color: red;
        padding-left: 0;
        display: none !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 26px;
        position: absolute;
        top: 5px;
        right: 1px;
        width: 20px;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #a7acb5;
        color: black;
    }
    .x_content {
        padding: 0 5px 6px;
        float: left;
        clear: both;
        margin-top: 0; 
    }
    
    .btn-success[disabled]{
        background-color: #5cb85c;
        border-color: #4cae4c;
    }
  </style>
@endsection

@section('content')

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="col-md-12 col-sm-12 col-xs-12">

        <div class="x_panel">
            <div class="x_title">
                <h2>
                    Select a province
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <div class="row">
                    <div class="col-md-9">
                        <div class="form-group">
                            <select name="province" id="province" class="form-control">
                                <option value="0">Please select a province</option>
                                @foreach ($provinces as $row)
                                    <option value="{{$row->province}}|{{$row->prv}}">{{$row->province}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-success form-control" id="download_btn">DOWNLOAD EXCEL</button>
                    </div>
                </div>
            </div>
        </div><br>

        <div class="x_panel">
            <div class="x_title">
                <h2>
                    Generated list
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <table class="table table-bordered table-striped" id="list_tbl">
                    <thead>
                        <th>RSBSA #</th>
                        <th>DS 2019 Name</th>
                        <th>Variety (DS 2019)</th>
                        <th>Bags (DS 2019)</th>
                        <th>WS 2020 Name</th>
                        <th>Variety (WS 2020)</th>
                        <th>Bags (WS 2020)</th>
                    </thead>
                </table>
            </div>
        </div><br>

    </div>

@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>
    <script src=" {{ asset('public/js/highcharts.js') }} "></script>

    <script>
        $("#list_tbl").DataTable();

        /*$("#province").on("change", function(e){
            var province = $(this).val();

            $("#municipality").empty().append("<option value='0'>Loading municipalities please wait...</option>");
            $.ajax({
                type: 'POST',
                url: "{{ route('farmer_profile.home.list_municipality') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    province: province
                },
                success: function(data){
                    $("#municipality").empty().append("<option value='0'>Please select a municipality</option>");
                    $("#municipality").append(data);
                }
            });
        });*/

        $("#download_btn").on("click", function(e){
            $("#download_btn").empty().html("loading list...");
            $("#download_btn").attr("disabled", "");

            var province = $("#province").val();

            if(province != 0){
                $.ajax({
                    type: 'POST',
                    url: "{{ route('farmer_profile.load.list') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        province: province,
                    },
                    success: function (response, textStatus, request) {
                        var a = document.createElement("a");
                        a.href = response.file; 
                        a.download = response.name;
                        document.body.appendChild(a);
                        a.click();
                        a.remove();

                        $("#download_btn").removeAttr("disabled");
                        $("#download_btn").empty().html("DOWNLOAD EXCEL");
                    }
                });

            }else{
                alert('please select a region & municipality');
            }
        });
    </script>
@endpush

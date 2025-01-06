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
  </style>
@endsection

@section('content')

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="col-md-12 col-sm-12 col-xs-12">

		@if(Auth::user()->userId == 28 || Auth::user()->userId == 370 || Auth::user()->userId == 2 || Auth::user()->username == '19-0922' || Auth::user()->username == 'd.taruc')
        <!-- UPLOAD PANEL -->
        <div class="x_panel">
            <div class="x_title">
                <h2>
                    Select a file to upload
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <div class="row">
                    <form enctype="multipart/form-data" method="post" action="{{route('coop.rla.upload')}}">
                        {{ csrf_field() }}
                        <div class="col-md-9">
                            <input class="form-control" name="file" type="file" id="fileInput" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                        </div>
                        <div class="col-md-3">
                            <input class="btn btn-success form-control" type="submit" value="UPLOAD RLA FILE">
                        </div>
                    </form>
                </div>
            </div>
        </div><br>
        <!-- UPLOAD PANEL -->
		@endif


        <div class="x_panel">
            <div class="x_title">
                <h2>
                    RLA Details 
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <table class="table table-hover table-striped table-bordered" id="rla_tbl">
                    <thead>
                        <th style="width: 300px;">Cooperative</th>
                        <th>Seed Grower</th>
                        <th>Variety</th>
                        <th>Lab No.</th>
                        <th>Lot No.</th>
                        <th>Certification Date</th>
                        <th># of bags passed</th>
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

    <script>
        $('#rla_tbl').DataTable().clear();
        $("#rla_tbl").DataTable({
            "bDestroy": true,
            "autoWidth": false,
            "searchHighlight": true,
            "processing": true,
            "serverSide": true,
            "orderMulti": true,
            "order": [],
            "ajax": {
                "url": "{{ route('coop.rla.table') }}",
                "dataType": "json",
                "type": "POST",
                "data":{
                    "_token": "{{ csrf_token() }}"
                }
            },
            "columns":[
                {"data": "coop_name"},
                {"data": "sg_name"},
                {"data": "seedVariety"},
                {"data": "labNo"},
                {"data": "lotNo"},
                {"data": "certificationDate"},
                {"data": "noOfBags"}
            ]
        });
    </script>
@endpush

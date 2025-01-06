<?php $qr_side = "active"; $qr_home="active"?>

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

        <div class="row">
            <div class="alert alert-warning alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <strong><i class="fa fa-info-circle"></i> Notice!</strong> This is a scheduled report, please note that the values displayed based on live data are <b><u>updated everyday @ 12 MN</u></b> to eliminate or minimize loading time.
            </div>
        </div>

        <!-- FILTER PANEL -->
        <div class="x_panel">
            <div class="x_title">
                <h2>
                    1). Select a Province
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <div class="row">
                    <div class="col-md-8">
                        <select name="province" id="province" class="form-control">
                            <option value="0">Please select a province</option>
                            @foreach ($municipal_list as $row)
                                <option value="{{$row->province}}">{{$row->province}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <button class="btn btn-success form-control" id="load_report_btn"><i class="fa fa-database"></i> LOAD MUNICIPAL REPORT</button>
                    </div>
                </div>
            </div>
        </div><br>
        <!-- FILTER PANEL -->

        <!-- distribution details -->
        <div class="x_panel">
            <div class="x_title">
                <h2>
                    2). Municipal Report
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <table class="table table-hover table-striped table-bordered" id="mun_tbl">
                    <thead>
                        <th>Municipality</th>
                        <th>Total beneficiaries</th>
                        <th>Estimated area planted (ha)</th>
                        <th>Registered area (ha)</th>
                        <th>Bags distributed (20kg/bag)</th>
                        <th>Total male</th>
                        <th>Total female</th>
                        <th>Action</th>
                    </thead>
                </table>
            </div>
        </div><br>
        <!-- /distribution details -->
    </div>

@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

    <script>
        $("#mun_tbl").DataTable({
            "order": [],
            "pageLength": 25
        });

        $("#load_report_btn").on("click", function(e){
            var province = $("#province").val();

            $('#mun_tbl').DataTable().clear();
            $("#mun_tbl").DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "pageLength": 25,
                "ajax": {
                    "url": "{{ route('generate.municipal.report') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        province: province
                    }
                },
                "columns":[
                    {"data": "municipality"},
                    {"data": "total_beneficiaries", 'searchable': false},
                    {"data": "total_estimated_area", 'searchable': false},
                    {"data": "total_registered_area", 'searchable': false},
                    {"data": "total_bags_distributed", 'searchable': false},
                    {"data": "total_male_count", 'searchable': false},
                    {"data": "total_female_count", 'searchable': false},
                    {"data": "action", 'searchable': false }
                ]
            });
        });

        $("#excel_btn").on("click", function(e){
            $("#excel_btn").empty().html('<i class="fa fa-cog fa-spin"></i> Exporting data to Excel...');
            $("#excel_btn").attr('disabled', '');

            $.ajax({
                type: 'POST',
                url: "{{ route('rcef.report.excel') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    excel_type: 'municipal'
                },
                success: function (response, textStatus, request) {
                    var a = document.createElement("a");
                    a.href = response.file; 
                    a.download = response.name;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();

                    $("#excel_btn").removeAttr('disabled');
                    $("#excel_btn").empty().html('Export to Excel (Statistics)');
                }
            });
        });
    </script>
@endpush

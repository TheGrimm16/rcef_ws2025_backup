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

        <!-- distribution details -->
        <div class="x_panel">
        <div class="x_title">
            <h2>
                Area Range Report
            </h2>
            <!--<button class="btn btn-success btn-sm" style="float:right;" id="excel_btn">
                Export to Excel (Statistics)
            </button>-->
            <div class="clearfix"></div>
        </div>
        <div class="x_content form-horizontal form-label-left">
            <table class="table table-hover table-striped table-bordered" id="area_range_tbl">
                <thead>
                    <th>Province</th>
                    <th>Municipality</th>
                    <th>Total Beneficiaries</th>
                    <th><= 1ha</th>
                    <th><= 2ha</th>
                    <th><= 3ha</th>
                    <th>> 3ha</th>
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
        $('#area_range_tbl').DataTable().clear();
        $("#area_range_tbl").DataTable({
            "pageLength": 50,
            "bDestroy": true,
            "autoWidth": false,
            "searchHighlight": true,
            "processing": true,
            "serverSide": true,
            "orderMulti": true,
            "order": [],
            "ajax": {
                "url": "{{ route('report.area_range.municipal') }}",
                "dataType": "json",
                "type": "POST",
                "data":{
                    "_token": "{{ csrf_token() }}",
                }
            },
            "columns":[
                {"data": "province"},
                {"data": "municipality"},
                {"data": "total_beneficiarries", searchable: false},
                {"data": "one_hectare_col", searchable: false},
                {"data": "two_hectare_col", searchable: false},
                {"data": "three_hectare_col", searchable: false},
                {"data": "last_col", searchable: false}
            ]
        });
    </script>
@endpush

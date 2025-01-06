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
                Seed Variety Report (per dropoff point)
            </h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content form-horizontal form-label-left">
            <div class="form-group">
                <label class="control-label col-md-2 col-sm-2 col-xs-2">Select a Dropoff Point:</label>
                <div class="col-md-10 col-sm-9 col-xs-10">
                    <select name="dropoff_id" id="dropoff_id" class="form-control" data-parsley-min="1">
                        <option value="0">Please select a dropoff point</option>
                        @foreach ($dropoff_list as $item)
                            <option value="{{$item->prv_dropoff_id}}">{{$item->region}} < {{$item->province}} < {{$item->municipality}} < {{$item->dropOffPoint}}</option>
                        @endforeach
                    </select>
                </div>
            </div><br>

            <div class="form-group">
                <label class="control-label col-md-2 col-sm-2 col-xs-2">Generated Data:</label>
                <div class="col-md-10 col-sm-9 col-xs-10">
                    <table class="table table-striped table-bordered" id="dop_data_tbl">
                        <thead>
                            <th>Seed Variety</th>
                            <th>Voume (20kg/bag)</th>
                        </thead>
                    </table>
                </div>
            </div>
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
        $("#dropoff_id").select2();
        $("#dop_data_tbl").DataTable();

        $("#dropoff_id").on("change", function(e){
            var dop_id = $("#dropoff_id").val();

            $('#dop_data_tbl').DataTable().clear();
            $("#dop_data_tbl").DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('report.variety.dop_result') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        "dop_id": dop_id
                    }
                },
                "columns":[
                    {"data": "seed_variety"},
                    {"data": "total_volume"}                        
                ]
            });
        });
    </script>
@endpush

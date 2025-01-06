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
    input[type=number]::-webkit-inner-spin-button {
        opacity: 1
    }
  </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">

            @include('layouts.message')

            <form action="{{ route('farmer.qr.generate') }}" method="POST" data-parsley-validate="">
            {!! csrf_field() !!}
                <div class="x_panel">
                    <div class="x_title">
                        <h2>
                            Select a Region & Specify the Volume of QR Codes to be printed...
                        </h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <div class="row">
                            <div class="col-md-8">
                                <select name="region" id="region" class="form-control" data-parsley-min="1" required>
                                    <option value="0">Please select a Region</option>
                                    @foreach ($regions as $region)
                                        <option value="{{ $region->regCode }}">{{ $region->regDesc }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control" name="QRLimit" id="QRLimit" value="100" max="200" min="1" required>
                            </div>
                            <div class="col-md-2">
                                <input type="submit" class="btn btn-block btn-success" value="Generate QR Codes">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>
                        QR Code print Logs
                    </h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <table class="table table-bordered table-striped" id="logs_tbl">
                        <thead>
                            <th>Username</th>
                            <th>Region</th>
                            <th>Action</th>
                            <th>Date Recorded</th>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>QR Code Summary</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <div id="qr_chart" style="width:100%; height:500px;"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>
    <script src=" {{ asset('public/js/highcharts.js') }} "></script>

    <script>
        $("#logs_tbl").DataTable({
            "bDestroy": true,
            "autoWidth": false,
            "searchHighlight": true,
            "processing": true,
            "serverSide": true,
            "orderMulti": true,
            "order": [],
            "ajax": {
                "url": "{{ route('farmer.qr.logs') }}",
                "dataType": "json",
                "type": "POST",
                "data":{
                    "_token": "{{ csrf_token() }}",
                }
            },
            "columns":[
                {data: 'username'},
                {data: 'region'},
                {data: 'remarks'},
                {data: 'dateRecorded', searchable: false}
            ]
        });


        $.ajax({
            type: 'POST',
            url: "{{ route('farmer.qr.chart') }}",
            data: {
                _token: "{{ csrf_token() }}",
            },
            success: function(data){
                populateQRchart(data["regions"], data["volumes"]);
            }
        });

        function populateQRchart(regions, volumes){
            $('#qr_chart').highcharts({
                chart: {
                        type: 'bar'
                    },
                    title:{
                        text:''
                    },
                    xAxis: {
                        categories: regions
                    },
                    yAxis: {
                        title: {
                            text: ''
                        }
                    },
                    plotOptions: {
                        series: {
                            pointPadding: 0,
                            groupPadding: 0.1,
                        }
                    },
                    series: [{
                        name: 'Total QR Code (Total Volume)',
                        data: volumes,
                        color: "rgb(82, 204, 90)"
                    }]
            });
        }
        
    </script>
@endpush

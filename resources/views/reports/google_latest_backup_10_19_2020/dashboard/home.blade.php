@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">

  <style>
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            opacity: 1;
        }
  </style>
@endsection

@section('content')

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="col-md-12 col-sm-12 col-xs-12">

        <div class="x_panel">
            <div class="x_title">
                <h2>Select a PhilRice Branch Station</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <div class="row">
                    <div class="col-md-8">
                        <select name="progress_station" id="progress_station" class="form-control" style="width:100% !important">
                            <option value="0">Please select a branch station</option>
                            @foreach ($station_list as $row)
                                <option value="{{$row->stationId}}">{{$row->stationName}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-success btn-block" id="load_station_btn"><i class="fa fa-arrow-circle-o-right"></i> LOAD DATA</button>
                    </div>
                </div><br>

                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0" style="margin:0">
                                    <button style="color: #7387a8;text-decoration:none;font-weight: 600;font-size:20px;" class="btn btn-link">
                                        SCHEDULED
                                    </button>
                                </h5>
                            </div>
                        </div>
        
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Total Bags (20kg/bag)</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content form-horizontal form-label-left">
                                <div class="row tile_count" style="margin: 0">
                                    <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                        <div class="count"><i class="fa fa-truck"></i> {{$total_bags_scheduled}}</div>
                                    </div>
                                </div>
                            </div>
                        </div><br>
                    </div>
        
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0" style="margin:0">
                                    <button style="color: #7387a8;text-decoration:none;font-weight: 600;font-size:20px;" class="btn btn-link">
                                        ACTUAL
                                    </button>
                                </h5>
                            </div>
                        </div>
        
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Total Bags (20kg/bag)</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content form-horizontal form-label-left">
                                <div class="row tile_count" style="margin: 0">
                                    <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                        <div class="count"><i class="fa fa-truck"></i> {{$total_actual_bags}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0" style="margin:0">
                                    <button style="color: #7387a8;text-decoration:none;font-weight: 600;font-size:20px;" class="btn btn-link">
                                        REPORTED
                                    </button>
                                </h5>
                            </div>
                        </div>
        
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Total Bags (20kg/bag)</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content form-horizontal form-label-left">
                                <div class="row tile_count" style="margin: 0">
                                    <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                        <div class="count"><i class="fa fa-truck"></i> {{$reported_total_bags}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>CATEGORY COMPARISON</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <div id="category_chart" style="width:100%;height:300px;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Farmer Beneficiaries</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content form-horizontal form-label-left">
                                <div class="row tile_count" style="margin: 0">
                                    <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                        <div class="count"><i class="fa fa-users"></i> {{$reported_total_beneficiaries}}</div>
                                    </div>
                                </div>
                            </div>
                        </div><br>

                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Area Planted (ha)</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content form-horizontal form-label-left">
                                <div class="row tile_count" style="margin: 0">
                                    <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                        <div class="count"><i class="fa fa-map-marker"></i> {{$reported_area_planted}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
            </div>
        </div>
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
        $.ajax({
            type: 'POST',
            url: "{{ route('rcep.google_sheet.dashboard_chart') }}",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(data){
                //$("#station_data_title").empty().html("OK!");
                load_station_barChart(data["category_chart"], data["category_schedule"], data["category_actual"]);
                //alert(data);
            }
        });

        function load_station_barChart(categories, schedule, actual){
            $('#category_chart').highcharts({
                height: 100,
                plotOptions: {
                    series: {
                        pointPadding: 0,
                        groupPadding: 0.1,
                    }
                },
                chart: {
                        type: 'bar'
                    },
                    title:{
                        text:''
                    },
                    xAxis: {
                        categories: categories
                    },
                    yAxis: {
                        title: {
                            text: ''
                        }
                    },
                    series: [{
                            name: 'Schedule data',
                            data: schedule,
                            color: "rgb(162, 163, 162)"
                        }, {
                            name: 'Actual data',
                            data: actual,
                            color: "rgb(135, 201, 138)"
                        }]
            });
        }

        $("#load_station_btn").on("click", function(e){
            alert("Feature under development!");
        });
    </script>
@endpush

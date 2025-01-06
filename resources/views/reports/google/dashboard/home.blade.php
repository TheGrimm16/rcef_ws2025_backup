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

    <div class="col-md-12 col-sm-12 col-xs-12" style="min-height: 1200px;">

        <div class="x_panel">
            <div class="x_title">
                <h2>
                    <strong>SELECT MONTH & WEEK</strong>
                </h2>  
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="form-horizontal form-label-left">
                    <div class="row">
                        <div class="col-md-5">
                            <select name="month" id="month" class="form-control">
                                <option value="ALL">VIEW ALL DATA</option>
                                @foreach ($months as $month)
                                    <option value="{{$month["month_id"]}}">{{$month["month_name"]}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-5">
                            <select name="week_no" id="week_no" class="form-control">
                                <option value="ALL">VIEW ALL DATA</option>
                                <!--<option value="1">1st Week</option>
                                <option value="2">2nd Week</option>
                                <option value="3">3rd Week</option>
                                <option value="4">4th Week</option>-->
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button id="load_btn" class="btn btn-success form-control" style="border-radius: 20px;"><i class="fa fa-database"></i> LOAD DATA</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="x_panel">
            <div class="x_title">
                <h2><strong>SEED SCHEDULE</strong> vs <strong>ACTUAL DELIVERY</strong> vs <strong>REPORTED DATA</strong></h2>
                <!--<button id="export_schedule_btn" class="btn btn-warning pull-right" style="border-radius: 20px;"><i class="fa fa-table"></i> EXPORT SCHEDULE</button>
                <select name="category" id="category" class="form-control pull-right" style="width: 250px;margin-right: 10px;">
                    <option value="SEED_COOP">Seed Cooperative / Association</option>
                    <option value="PHILRICE_WAREHOUSE">PhilRice Designated Warehouse</option>
                    <option value="LGU_STOCKS">Stocks in LGU</option>
                    <option value="TRANSFERRED_SEEDS">Transferred Seeds</option>
                </select>-->
                <!--<button id="export_weekly_btn" class="btn btn-warning pull-right" style="border-radius: 20px;"><i class="fa fa-table"></i> EXPORT WEEKLY REPORT</button>-->
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
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
                                        <div class="count" id="total_bags_scheduled"><i class="fa fa-truck"></i> {{$total_bags_scheduled}}</div>
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
                                        <div class="count" id="total_actual_bags"><i class="fa fa-truck"></i> {{$total_actual_bags}}</div>
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
                                        <div class="count" id="reported_total_bags"><i class="fa fa-truck"></i> {{$reported_total_bags}}</div>
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
                                        <div class="count" id="reported_total_beneficiaries"><i class="fa fa-users"></i> {{$reported_total_beneficiaries}}</div>
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
                                        <div class="count" id="reported_area_planted"><i class="fa fa-map-marker"></i> {{$reported_area_planted}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
            </div>
        </div>

        <!-- WEEK CHARTS -->
        <div class="x_panel">
            <div class="x_title">
                <h2>
                    <strong>DATA PER WEEK FOR THE MONTH OF: `{{strtoupper(date('F'))}}`</strong>
                </h2>  
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="form-horizontal form-label-left">
                    <div id="week_chart" style="width:100%;height:500px;"></div>
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
        $("#month").on("change", function(e){
            if($("#month").val() == "ALL"){
                $("#week_no").empty().append('<option value="ALL">VIEW ALL DATA</option>');
            }else{
                $("#week_no").empty().append('<option value="1">1st Week</option>');
                $("#week_no").append('<option value="2">2nd Week</option>');
                $("#week_no").append('<option value="3">3rd Week</option>');
                $("#week_no").append('<option value="4">4th Week</option>');
            }
        });
        
        $.ajax({
            type: 'POST',
            url: "{{ route('rcep.google_sheet.dashboard_chart') }}",
            data: {
                _token: "{{ csrf_token() }}",
            },
            success: function(data){
                //$("#station_data_title").empty().html("OK!");
                load_station_barChart(data["category_chart"], data["category_schedule"], data["category_actual"], 
                    data["week_bags"], data["week_farmers"], data["week_area"], data["week_label"]);
                //alert(data);
            }
        });

        function load_station_barChart(categories, schedule, actual, week_bags, week_farmers, week_area, week_label){

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

            $('#week_chart').highcharts({
                plotOptions: {
                    series: {
                        pointPadding: 0,
                        groupPadding: 0.1,
                    }
                },
                chart: {
                        type: 'line'
                    },
                    title:{
                        text:''
                    },
                    xAxis: {
                        categories: week_label
                    },
                    yAxis: {
                        title: {
                            text: ''
                        }
                    },
                    series: [{
                            name: 'Total Farmers',
                            data: week_farmers,
                            color: "rgb(132, 155, 191)"
                        }, {
                            name: 'Estimated Area Planted',
                            data: week_area,
                            color: "rgb(214, 183, 111)"
                        }, {
                            name: 'Total Bags Distributed',
                            data: week_bags,
                            color: "rgb(46, 179, 57)"
                        }]
            });
        }

        $("#load_btn").on("click", function(e){

            $("#total_bags_scheduled").empty().append("loading...");
            $("#total_actual_bags").empty().append("loading...");
            $("#reported_total_bags").empty().append("loading...");
            $("#reported_total_beneficiaries").empty().append("loading...");
            $("#reported_area_planted").empty().append("loading...");

            $.ajax({
                type: 'POST',
                url: "{{ route('rcep.google_sheet.dashboard_filter') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    week_month: $("#month").val(),
                    week_number: $("#week_no").val()
                },
                success: function(data){
                    $("#total_bags_scheduled").empty().append("<i class='fa fa-truck'></i> "+data["total_bags_scheduled"]);
                    $("#total_actual_bags").empty().append("<i class='fa fa-truck'></i> "+data["total_actual_bags"]);
                    $("#reported_total_bags").empty().append("<i class='fa fa-truck'></i> "+data["reported_total_bags"]);
                    $("#reported_total_beneficiaries").empty().append("<i class='fa fa-users'></i> "+data["reported_total_beneficiaries"]);
                    $("#reported_area_planted").empty().append("<i class='fa fa-map-marker'></i> "+data["reported_area_planted"]);
                }
            }).done(function(e){
                if($("#month").val() == "ALL"){
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('rcep.google_sheet.dashboard_chart') }}",
                        data: {
                            _token: "{{ csrf_token() }}",
                        },
                        success: function(data){
                            load_station_barChart(data["category_chart"], data["category_schedule"], data["category_actual"], 
                                data["week_bags"], data["week_farmers"], data["week_area"]);
                        }
                    });
                }else{
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('rcep.google_sheet.category_filter') }}",
                        data: {
                            _token: "{{ csrf_token() }}",
                            week_month: $("#month").val(),
                            week_number: $("#week_no").val()
                        },
                        success: function(data){
                            load_station_barChart(data["category_chart"], data["category_schedule"], data["category_actual"], 
                                data["week_bags"], data["week_farmers"], data["week_area"]);
                        }
                    });
                }
            });
        });

        //export function
        $("#export_schedule_btn").on("click", function(e){
            var week_month = $("#month").val();
            var week_number = $("#week_no").val();
            var category = $("#category").val();

            var url = '{{ route("rcep.google_sheet.export_schedule", ["month" => ":month", "week" => ":week", "category" => ":category"]) }}';
            url = url.replace(':month', week_month);
            url = url.replace(':week', week_number);
            url = url.replace(':category', category);
            //window.location.href=url;

            window.open(url, '_blank');
        });
    </script>
@endpush

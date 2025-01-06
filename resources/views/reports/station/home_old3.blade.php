@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
  <style>
    .tab_link_active {
        color: #fff;
        background-color: #337ab7;
        border-color: #2e6da4;
        text-align: center;
    } 
    .tab_link_active .x_content div span{
        border-bottom: 2px solid white;
    }
    span {
        cursor: pointer;
    }
    .coop-link:hover{
        color: blue;
        text-decoration: underline;
    }

    .tile_count .tile_stats_count:before {
        content: "";
        position: absolute;
        left: 0;
        height: 65px;
        border-left: 0;
        margin-top: 10px;
    }
  </style>
@endsection

@section('content')

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="col-md-12 col-sm-12 col-xs-12">

        <div class="row">
            <div class="col-md-3">
                <div class="x_panel tab_link_active" id="overall_tab">
                    <div class="x_content" style="padding: 0;float: left;clear: both;margin-top: 0;">
                        <div style="padding:10px">
                            <span style="font-size: 15px;font-weight: 600;">OVERALL PERFORMANCE</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="x_panel" style="text-align: center;" id="station_tab">
                    <div class="x_content" style="padding: 0;float: left;clear: both;margin-top: 0;">
                        <div style="padding:10px">
                            <span style="font-size: 15px;font-weight: 600;">STATION REPORT</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="x_panel" style="text-align: center;" id="progress_tab">
                    <div class="x_content" style="padding: 0;float: left;clear: both;margin-top: 0;">
                        <div style="padding:10px">
                            <span style="font-size: 15px;font-weight: 600;">PROGRESS MONITORING</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="overall_div">

            <div class="col-md-12">
                <div class="alert alert-warning alert-dismissible fade in" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <strong><i class="fa fa-info-circle"></i> Notice!</strong> This is a scheduled report, please note that the values displayed based on live data are <b><u>updated everyday @ 12 MN</u></b> to eliminate or minimize loading time.
                </div>
            </div>

            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2 id="station_data_title">Overall Performance (All Branch Stations)</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <div id="station_chart" style="width:100%; height:1200px;"></div>
                        <!--<div class="no_delivery_wrapper" style="width:100%;height:400px;background-color:#d8d8d8;">
                            <img src="{{asset('public/images/load_station.gif')}}" alt="" style="display: block;margin: auto;height: 300px;padding-top: 25px;">
                        </div>-->
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="station_div" style="display:none">
            <div class="col-md-7">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>PhilRice station & Area coverage</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <div class="form-group">
                            <select name="station" id="station" class="form-control" style="width:100% !important">
                                <option value="0">Please select a branch station</option>
                                @foreach ($station_list as $row)
                                    <option value="{{$row->stationId}}">{{$row->stationName}}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <select name="station_area" id="station_area" class="form-control" style="width:100% !important">
                                <option value="0">Please select an area</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <button class="btn btn-success btn-block" id="station_load_btn"><i class="fa fa-arrow-circle-o-right"></i> LOAD REPORT</button>
                        </div>
                    </div>
                </div>

                <div class="x_panel" id="sg_section" style="display: none">
                    <div class="x_title">
                        <h2>Participating Seed Cooperatives</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <table class="table table-bordered table-striped" id="station_seedCoop_tbl">
                            <thead>
                                <tr>
                                    <th style="width: 30px !important">#</th>
                                    <th>Seed cooperative (click the coop  name to view more details)</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-5" id="val_section" style="display: none">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Covered Provinces</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <div class="row tile_count" style="margin: 0">
                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                <div class="count" id="count_provinces">--</div>
                            </div>
                        </div>
                    </div>
                </div><br>

                <div class="x_panel">
                    <div class="x_title">
                        <h2>Covered Municipalities</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <div class="row tile_count" style="margin: 0">
                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                <div class="count" id="count_municipalities">--</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="x_panel">
                    <div class="x_title">
                        <h2>Inspected & Accepted Deliveries</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <div class="row tile_count" style="margin: 0">
                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                <div class="count" id="count_bags_accepted">--</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Farmer Beneficiaries</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <div class="row tile_count" style="margin: 0">
                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                <div class="count" id="count_farmers">--</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6" id="seed_section" style="display: none">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Seed Variety List</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <table class="table table-bordered table-striped" id="seed_variety_tbl">
                            <thead>
                                <th style="width: 30px !important">#</th>
                                <th>Seed Variety</th>
                                <th>Total bags (20kg/bag)</th>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6" id="seed_chart" style="display: none">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Generated data from (`Seed variety list`)</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <div id="seed_chart_container" style="width:100%; height:500px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="progress_div" style="display:none">
            
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Select a PhilRice branch station</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
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
                                <button class="btn btn-success btn-block" id="progress_load_btn"><i class="fa fa-arrow-circle-o-right"></i> LOAD DATA</button>
                            </div>
                        </div><br>

                        <div class="alert alert-warning alert-dismissible fade in" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                            <strong><i class="fa fa-info-circle"></i> Notice!</strong> This report uses data generated from the system's "sheculed report" <strong><u>(executed everyday @ 12MN)</u></strong>
                        </div>

                        <div class="row">
                    
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0" style="margin:0">
                                            <button style="color: #7387a8;text-decoration:none;font-weight: 600;font-size:20px;" class="btn btn-link">
                                                RCEP-SMS
                                            </button>
                                        </h5>
                                    </div>
                                </div>

                                <div class="x_panel">
                                    <div class="x_title">
                                        <h2>Covered Provinces</h2>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="x_content form-horizontal form-label-left">
                                        <div class="row tile_count" style="margin: 0">
                                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                                <div class="count" id="count_sms_provinces"><i class="fa fa-map-marker"></i> --</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="x_panel">
                                    <div class="x_title">
                                        <h2>Covered Municipalities</h2>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="x_content form-horizontal form-label-left">
                                        <div class="row tile_count" style="margin: 0">
                                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                                <div class="count" id="count_sms_municipality"><i class="fa fa-map-marker"></i> --</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="x_panel">
                                    <div class="x_title">
                                        <h2>Inspeceted & Accepted Bags</h2>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="x_content form-horizontal form-label-left">
                                        <div class="row tile_count" style="margin: 0">
                                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                                <div class="count" id="count_sms_inspected"><i class="fa fa-truck"></i> --</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="x_panel">
                                    <div class="x_title">
                                        <h2>Total Beneficiaries</h2>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="x_content form-horizontal form-label-left">
                                        <div class="row tile_count" style="margin: 0">
                                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                                <div class="count" id="count_sms_farmers"><i class="fa fa-users"></i> --</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
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
                                        <h2>Covered Provinces</h2>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="x_content form-horizontal form-label-left">
                                        <div class="row tile_count" style="margin: 0">
                                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                                <div class="count" id="count_mob_provinces"><i class="fa fa-map-marker"></i> --</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="x_panel">
                                    <div class="x_title">
                                        <h2>Covered Municipalities</h2>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="x_content form-horizontal form-label-left">
                                        <div class="row tile_count" style="margin: 0">
                                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                                <div class="count" id="count_mob_municipality"><i class="fa fa-map-marker"></i> --</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="x_panel">
                                    <div class="x_title">
                                        <h2>Inspeceted & Accepted Bags</h2>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="x_content form-horizontal form-label-left">
                                        <div class="row tile_count" style="margin: 0">
                                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                                <div class="count" id="count_mob_inspected"><i class="fa fa-truck"></i> --</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="x_panel">
                                    <div class="x_title">
                                        <h2>Total Beneficiaries</h2>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="x_content form-horizontal form-label-left">
                                        <div class="row tile_count" style="margin: 0">
                                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                                <div class="count" id="count_mob_farmers"><i class="fa fa-users"></i> --</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEED COOPERATIVE MODAL -->
        <div id="seed_coop_details" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="coop_name_title">
                            {COOP_NAME}
                        </h4>
                        <span id="coop_accreditation_title">{COOP_ACCREDITATION}</span>
                    </div>
                    <div class="modal-body" style="max-height: 500px;overflow: auto;">
                        <table class="table table-bordered table-striped" id="sg_per_coop_tbl">
                            <thead>
                                <th>SG Name</th>
                                <th>Bags Passed</th>
                                <th>Status</th>
                            </thead>
                        </table>
                        <hr>
                        <div id="coop_chart_container" style="width:100%; height:600px;display: none"></div>             
                    </div>
                </div>
            </div>
        </div>
        <!-- SEED COOPERATIVE MODAL -->

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

        /**  <LOAD BAR FOR ALL STATIONS> **/
        //$("#station_data_title").empty().html("Loading...");
        $.ajax({
            type: 'POST',
            url: "{{ route('station_report.load_station_data') }}",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(data){
                //$("#station_data_title").empty().html("OK!");
                load_station_barChart(data["station_list"], data["confirmed_list"], data["inspected_list"], data["distributed_list"], data["farmer_list"], data["target_list"]);
            }
        });

        function load_station_barChart(stations, confirmed, inspected, distributed, farmers, targets){
            $('#station_chart').highcharts({
                chart: {
                        type: 'bar'
                    },
                    title:{
                        text:''
                    },
                    xAxis: {
                        categories: stations
                    },
                    yAxis: {
                        title: {
                            text: ''
                        }
                    },
                    legend: {
                        layout: 'vertical',
                        align: 'right',
                        verticalAlign: 'top',
                        floating: true,
                        backgroundColor: '#FFFFFF',
                        borderWidth: 1,
                        y: 90
                    },
                    series: [{
                            name: 'Target seeds to be delivered (20kg/bag)',
                            data: targets
                        }, {
                            name: 'Expected seeds for delivery (20kg/bag)',
                            data: confirmed
                        }, {
                            name: 'Accepted seeds for distribution (20kg/bag)',
                            data: inspected
                        }, {
                            name: 'Distributed seeds (20kg/bag)',
                            data: distributed
                        }, {
                            name: 'Total farmer beneficiaries',
                            data: farmers
                        }]
            });
        }
        /**  <LOAD BAR FOR ALL STATIONS/> **/
        
        $("#station_seedCoop_tbl").DataTable();
        Highcharts.setOptions({
            lang: {
                decimalPoint: '.',
                thousandsSep: ','
            },

            tooltip: {
                yDecimals: 2 // If you want to add 2 decimals
            }
        });

        

        $("#overall_tab").on("click", function(e){
            $("#overall_tab").addClass('tab_link_active');
            $("#station_tab").removeClass('tab_link_active');
            $("#progress_tab").removeClass('tab_link_active');

            $("#overall_div").css("display","block");
            $("#station_div").css("display", "none");
            $("#progress_div").css("display", "none");
        });


        $("#station_tab").on("click", function(e){
            $("#station_tab").addClass('tab_link_active');
            $("#overall_tab").removeClass('tab_link_active');
            $("#progress_tab").removeClass('tab_link_active');

            $("#station_div").css("display", "block");
            $("#overall_div").css("display","none");
            $("#progress_div").css("display", "none");
        });


        $("#progress_tab").on("click", function(e){
            $("#progress_tab").addClass('tab_link_active');
            $("#overall_tab").removeClass('tab_link_active');
            $("#station_tab").removeClass('tab_link_active');

            $("#progress_div").css("display", "block");
            $("#station_div").css("display", "none");
            $("#overall_div").css("display","none");

            //load all station data
            $("#count_sms_provinces").empty().html('loading...');
            $("#count_sms_municipality").empty().html('loading...');
            $("#count_sms_inspected").empty().html('loading...');
            $("#count_sms_area").empty().html('loading...');
            $("#count_sms_farmers").html('loading...');
            
            $.ajax({
                type: 'POST',
                url: "{{ route('station_report.load.progress_all') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                },
                success: function(data){
                    $("#count_sms_provinces").empty().html('<i class="fa fa-map-marker"></i> '+data["provinces"]+'</div>');
                    $("#count_sms_municipality").empty().html('<i class="fa fa-map-marker"></i> '+data["municipalities"]+'</div>');
                    $("#count_sms_inspected").empty().html('<i class="fa fa-truck"></i> '+data["inspected"]+'</div>');
                    $("#count_sms_farmers").empty().html('<i class="fa fa-users"></i> '+data["beneficiaries"]+'</div>');
                }
            });
        });

        //on select of region select tag in station report
        $("#station").select2();
        $("#station_area").select2();
        $("#station").on("change", function(e){
            var station = $(this).val();

            $.ajax({
                type: 'POST',
                url: "{{ route('station_report.load_area_cover') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    station: station
                },
                success: function(data){
                    $("#station_area").empty().append(data);
                }
            });
        });

        //on click of `load report` - station report
        $("#station_load_btn").on("click", function(e){
            var station = $("#station").val();
            var area_id = $("#station_area").val();

            if(station != "0" && area_id != "0"){
                $("#val_section").css("display", "none");
                $("#sg_section").css("display", "none");
                $("#seed_section").css("display", "none");
                $("#seed_chart").css("display", "none");

                $("#val_section").css("display", "block");
                $("#count_provinces").empty().html("loading...");
                $("#count_municipalities").empty().html("loading...");
                $("#count_bags_accepted").empty().html("loading...");
                $("#count_farmers").empty().html("loading...");

                $.ajax({
                    type: 'POST',
                    url: "{{ route('station_report.load_total_values') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        station: station,
                        area_id: area_id
                    },
                    success: function(data){
                        $("#count_provinces").empty().html("<i class='fa fa-map-marker'></i> "+data["total_provinces"]);
                        $("#count_municipalities").empty().html("<i class='fa fa-map-marker'></i> "+data["total_municipalities"]);
                        $("#count_bags_accepted").empty().html("<i class='fa fa-truck'></i> "+data["total_bags"]);
                        $("#count_farmers").empty().html("<i class='fa fa-users'></i> "+data["farmer_beneficiaries"]);
                    }
                }).done(function(){
                    load_cooperatives(area_id);
                    $("#sg_section").css("display", "inline-block");
                }).done(function(){
                    load_seedVariety_list(area_id);            
                }).done(function(){
                    load_seed_chart(area_id);
                    $("#seed_section").css("display", "inline-block");
                    $("#seed_chart").css("display", "inline-block");
                });
            
            }else{
                alert("Please select a branch station and an area that is being covered by the selected station.");
            }
           
        });

        //load seed cooperatives in region or province
        function load_cooperatives(area_id){
            $("#station_seedCoop_tbl").DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('station_report.coop_list') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        //region : region,
                        area_id: area_id
                    }
                },
                "columns":[
                    {data: 'row_count'},
                    {data: 'coop_link'}
                ]
            });
        }


        //seed coop modal details when clicked on table
        $('#seed_coop_details').on('show.bs.modal', function (e) {
            var accreditation_no = $(e.relatedTarget).data('accreditation_no');

            $.ajax({
                type: 'POST',
                url: "{{ route('station_report.selected_coop.details') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    accreditation_no: accreditation_no
                },
                success: function(data){
                    $("#coop_name_title").empty().html(data["coop_name"]);
                    $("#coop_accreditation_title").empty().html("Accreditation No: "+data["coop_accreditation"]);
                }

            }).done(function(e){
                $("#sg_per_coop_tbl").DataTable().clear();
                $("#sg_per_coop_tbl").DataTable({
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "ajax": {
                        "url": "{{ route('load.coop.members') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            "coop_accre": accreditation_no
                        }
                    },
                    "columns":[
                        {"data": "full_name"},
                        {"data": "bags_passed", searchable: false},
                        {"data": "blacklist_status", searchable: false},
                    ]
                });
            }).done(function(){
                load_data_for_coopChart(accreditation_no);
            });
        });

        //commitment vs delivered data fetching of coop
        function load_data_for_coopChart(accreditation_no){
            $.ajax({
                type: 'POST',
                url: "{{ route('load.coop.seeds') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    coop_accre: accreditation_no
                },
                success: function(data){
                    populateChart(data['variety_list'], data['commitment_list'], data['delivered_list'], data["total_commitment"], data["total_delivered"]);
                    $("#coop_chart_container").css("display", "inline-grid");
                }
            });
        }

        //placing commitment vs delivered data to chart
        function populateChart(varieties, commitment, delivered, total_commitment, total_delivered){
            $('#coop_chart_container').highcharts({
                chart: {
                    type: 'bar'
                },
                title:{
                    text: total_commitment + ' (Commitment) ' + ' vs ' + total_delivered + ' (Delivered) Seeds'
                },
                xAxis: {
                    categories: varieties
                },
                yAxis: {
                    title: {
                        text: ''
                    }
                },
                series: [{
                        name: 'Commitment',
                        data: commitment
                    }, {
                        name: 'Delivered',
                        data: delivered
                    }]
            });
        }

        //get seed variety list
        function load_seedVariety_list(area_id){
            $("#seed_variety_tbl").DataTable().clear();
            $("#seed_variety_tbl").DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('station_report.region.varieties') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        //"region": region,
                        area_id: area_id
                    }
                },
                "columns":[
                    {"data": "row_count"},
                    {"data": "seed_variety"},
                    {"data": "seed_volume"}
                ]
            });
        }

        //get seed variety list raw data for preparation in displaying to seed chart
        function load_seed_chart(area_id){
            $.ajax({
                type: 'POST',
                url: "{{ route('station_report.seed.chart') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    //region: region,
                    area_id: area_id
                },
                success: function(data){
                    populateSeedChart(data);
                }
            });
        }

        //plot data to seed chart
        function populateSeedChart(chart_data){
            var title_text = '';
            if($("#station_area").val() == ''){
                title_text = $("#region option:selected").text();
            }else{
                title_text = $("#station_area option:selected").text();
            }

            Highcharts.chart('seed_chart_container', {
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: title_text
                },
                tooltip: {
                    useHTML: true,
                    headerFormat: '',
                    pointFormat: '{point.name}: <b>{point.y:,.0f} bag(s)</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>'
                        },
                        showInLegend: true
                    }
                },
                series: [{
                    name: 'Total',
                    colorByPoint: true,
                    data: chart_data
                }]
            });
        }


        $("#progress_load_btn").on("click", function(){
            var station = $("#progress_station").val();

            if(station != "0"){
                $("#count_sms_provinces").empty().html('loading...');
                $("#count_sms_municipality").empty().html('loading...');
                $("#count_sms_inspected").empty().html('loading...');
                $("#count_sms_area").empty().html('loading...');
                $("#count_sms_farmers").html('loading...');
                
                $.ajax({
                    type: 'POST',
                    url: "{{ route('station_report.load.progress') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        station: station
                    },
                    success: function(data){
                        $("#count_sms_provinces").empty().html('<i class="fa fa-map-marker"></i> '+data["provinces"]+'</div>');
                        $("#count_sms_municipality").empty().html('<i class="fa fa-map-marker"></i> '+data["municipalities"]+'</div>');
                        $("#count_sms_inspected").empty().html('<i class="fa fa-truck"></i> '+data["inspected"]+'</div>');
                        $("#count_sms_farmers").empty().html('<i class="fa fa-users"></i> '+data["beneficiaries"]+'</div>');
                    }
                });
            }else{
                alert("Please select a branch station.");
            }
        });

    </script>
@endpush

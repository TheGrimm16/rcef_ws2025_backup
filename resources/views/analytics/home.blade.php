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

    .total_container{
        font-size: 30px;
        font-weight: bold;
    }


  </style>
@endsection

@section('content')

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="row">
        <div class="col-md-4">
            <div class="x_panel">
                <div class="x_title">
                    <h2>
                        Seed Varieties (top 5)
                    </h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <div id="top5_chart" style="width:100%;height:500px;"></div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Total Beneficiaries</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-9 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                            <div class="count" id="range_total_beneficiaries"><i class="fa fa-users"></i> --</div>
                        </div>
                        <div class="col-md-3 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">
                                <div class="row ml-3">
                                    <div class="col-md-12 col-sm-6 col-xs-6">
                                        <div class="sub-count" ><i class="fa fa-users" id="range_total_male">-</i></div>
                                    </div>

                                    <div class="col-md-12 col-sm-6 col-xs-6">
                                        <div class="sub-count" ><i class="fa fa-users" id="range_total_female">-</i></div>
                                    </div>
                                </div>
                        </div>

                    </div>
                </div>
            </div>

        
        </div>






                <div class="col-md-4">
     
            <div class="x_panel">
                <div class="x_title">
                    <h2>Farmers with <= 0.5ha of farm area</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-8 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                            <div class="total_container" id="range_p5_farmers"><i class="fa fa-users"></i> --</div>
                        </div>
                        <div class="col-md-4 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">
                                <div class="row ml-3">
                                    <div class="col-md-12 col-sm-6 col-xs-6">
                                        <div class="sub-count" ><i class="fa fa-users" id="range_p5_male">-</i></div>
                                    </div>

                                    <div class="col-md-12 col-sm-6 col-xs-6">
                                        <div class="sub-count" ><i class="fa fa-users" id="range_p5_female">-</i></div>
                                    </div>
                                </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="x_panel">
                <div class="x_title">
                    <h2>Farmers with > 1ha & <= 1.5ha of farm area</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-8 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                            <div class="total_container" id="range_1_1p5_farmers"><i class="fa fa-users"></i> --</div>
                        </div>
                        <div class="col-md-4 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">
                                <div class="row ml-3">
                                    <div class="col-md-12 col-sm-6 col-xs-6">
                                        <div class="sub-count" ><i class="fa fa-users" id="range_1_1p5_male">-</i></div>
                                    </div>

                                    <div class="col-md-12 col-sm-6 col-xs-6">
                                        <div class="sub-count" ><i class="fa fa-users" id="range_1_1p5_female">-</i></div>
                                    </div>
                                </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="x_panel">
                <div class="x_title">
                    <h2>Farmers with > 2ha & <= 2.5ha of farm area</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-8 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                            <div class="total_container" id="range_2_2p5_farmers"><i class="fa fa-users"></i> --</div>
                        </div>
                        <div class="col-md-4 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">
                                <div class="row ml-3">
                                    <div class="col-md-12 col-sm-6 col-xs-6">
                                        <div class="sub-count" ><i class="fa fa-users" id="range_2_2p5_male">-</i></div>
                                    </div>

                                    <div class="col-md-12 col-sm-6 col-xs-6">
                                        <div class="sub-count" ><i class="fa fa-users" id="range_2_2p5_female">-</i></div>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

         <div class="col-md-4">
      
            <div class="x_panel">
                <div class="x_title">
                    <h2>Farmers with > 0.5ha & <= 1ha of farm area</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-8 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                            <div class="total_container" id="range_p5_1_farmers"><i class="fa fa-users"></i> --</div>
                        </div>
                         <div class="col-md-4 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">
                                <div class="row ml-3">
                                    <div class="col-md-12 col-sm-6 col-xs-6">
                                        <div class="sub-count" ><i class="fa fa-users" id="range_p5_1_male">-</i></div>
                                    </div>

                                    <div class="col-md-12 col-sm-6 col-xs-6">
                                        <div class="sub-count" ><i class="fa fa-users" id="range_p5_1_female">-</i></div>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="x_panel">
                <div class="x_title">
                    <h2>Farmers with > 1.5ha & <= 2ha of farm area</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-8 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                            <div class="total_container" id="range_1p5_2_farmers"><i class="fa fa-users"></i> --</div>
                        </div>
                        <div class="col-md-4 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">
                                <div class="row ml-3">
                                    <div class="col-md-12 col-sm-6 col-xs-6">
                                        <div class="sub-count" ><i class="fa fa-users" id="range_1p5_2_male">-</i></div>
                                    </div>

                                    <div class="col-md-12 col-sm-6 col-xs-6">
                                        <div class="sub-count" ><i class="fa fa-users" id="range_1p5_2_female">-</i></div>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="x_panel">
                <div class="x_title">
                    <h2>Farmers with > 2.5ha & <= 3ha of farm area</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-8 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                            <div class="total_container" id="range_2p5_3_farmers"><i class="fa fa-users"></i> --</div>
                        </div>

                         <div class="col-md-4 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">
                                <div class="row ml-3">
                                    <div class="col-md-12 col-sm-6 col-xs-6">
                                        <div class="sub-count" ><i class="fa fa-users" id="range_2p5_3_male">-</i></div>
                                    </div>

                                    <div class="col-md-12 col-sm-6 col-xs-6">
                                        <div class="sub-count" ><i class="fa fa-users" id="range_2p5_3_female">-</i></div>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>


        <div class="col-md-4">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Farmers with > 3ha of farm area</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-8 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                            <div class="total_container" id="range_3_farmers"><i class="fa fa-users"></i> --</div>
                        </div>
                         <div class="col-md-4 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">
                                <div class="row ml-3">
                                    <div class="col-md-12 col-sm-6 col-xs-6">
                                        <div class="sub-count" ><i class="fa fa-users" id="range_3_male">-</i></div>
                                    </div>

                                    <div class="col-md-12 col-sm-6 col-xs-6">
                                        <div class="sub-count" ><i class="fa fa-users" id="range_3_female">-</i></div>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>





        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Summary Per Variety</h2>
                    <button id="switch_graph_btn" class="btn btn-success btn-sm pull-right" style="display:block;"><i class="fa fa-bar-chart"></i> SWITCH TO GRAPH VIEW</button>
                    <button id="switch_table_btn" class="btn btn-success btn-sm pull-right" style="display:none;"><i class="fa fa-table"></i> SWITCH TO TABLE VIEW</button>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <table class="table table-bordered table-striped" id="summary_variety_tbl">
                        <thead>
                            <th>Province</th>
                            <th>Municipality</th>
                            <th>Seed Variety</th>
                            <th># of bags</th>
                            <th>Percentage (bags)</th>
                            <th># of farmers</th>
                            <th>Percentage (farmers)</th>
                        </thead>
                    </table>


                    <div id="summary_chart" style="width:100%; height:800px;display:none;"></div>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Top 10 Provinces (Inspected & Accepted Seeds)</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal">
                    <img id="chart_gif" src="{{asset('public/images/load_chart.gif')}}" alt="" id="loading_gif" style="display: block;margin: auto;height: 300px;padding-top: 25px;">
                    <div id="province_chart" style="width:100%; height:500px;"></div>
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
            url: "{{ route('analytics.top5') }}",
            data: {
                _token: "{{ csrf_token() }}",
            },
            success: function(data){
                load_top5(data);
            }
        }).done(function(e){
            load_area_range_data();
        }).then(function(e){
            load_province_data();
        }).then(function(e){
            load_summary_data();
        }).then(function(e){
            load_summary_chart();
        });


        function load_summary_data(){
            $('#summary_variety_tbl').DataTable().clear();
            $("#summary_variety_tbl").DataTable({
                "pageLength": 25,
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('analytics.summary.per_variety') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                    }
                },
                "columns":[
                    {"data": "province"},
                    {"data": "municipality"},
                    {"data": "seed_variety"},
                    {"data": "total_bags_text"},
                    {"data": "bags_percentage"},
                    {"data": "total_farmers_text"},
                    {"data": "area_percentage"},
                ]
            });
        }

        function load_province_chart(provinces, bags){
            $('#province_chart').highcharts({
                chart: {
                        type: 'column'
                    },
                    title:{
                        text:''
                    },
                    xAxis: {
                        categories: provinces
                    },
                    yAxis: {
                        title: {
                            text: ''
                        }
                    },
                    series: [{
                        name: 'Total volume (20kg/bag)',
                        data: bags,
                        color: "rgb(120,235,117)"
                    }]
            });
        }

        function load_area_range_data(){
            $("#range_total_beneficiaries").empty().html("please wait...");

            $("#range_p5_farmers").empty().html("please wait...");
            $("#range_p5_1_farmers").empty().html("please wait...");
            $("#range_1_1p5_farmers").empty().html("please wait...");
            $("#range_1p5_2_farmers").empty().html("please wait...");
            $("#range_2_2p5_farmers").empty().html("please wait...");
            $("#range_2p5_3_farmers").empty().html("please wait...");
            $("#range_3_farmers").empty().html("please wait...");



            $.ajax({
                type: 'POST',
                url: "{{ route('analytics.area_range') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                },
                success: function(data){
                    $("#range_total_beneficiaries").empty().html(data["total_beneficiaries"]);
                        $("#range_total_male").empty().html(data["total_beneficiaries_male"]);
                        $("#range_total_female").empty().html(data["total_beneficiaries_female"]);
                    $("#range_p5_farmers").empty().html(data["total_p5_farmers"]);
                        $("#range_p5_male").empty().html(data["total_p5_male"]);
                        $("#range_p5_female").empty().html(data["total_p5_female"]);
                    $("#range_p5_1_farmers").empty().html(data["total_p5_1_farmers"]);
                        $("#range_p5_1_male").empty().html(data["total_p5_1_male"]);
                        $("#range_p5_1_female").empty().html(data["total_p5_1_female"]);
                    $("#range_1_1p5_farmers").empty().html(data["total_1_1p5_farmers"]);
                        $("#range_1_1p5_male").empty().html(data["total_1_1p5_male"]);
                        $("#range_1_1p5_female").empty().html(data["total_1_1p5_female"]);
                    $("#range_1p5_2_farmers").empty().html(data["total_1p5_2_farmers"]);
                        $("#range_1p5_2_male").empty().html(data["total_1p5_2_male"]);
                        $("#range_1p5_2_female").empty().html(data["total_1p5_2_female"]);
                    $("#range_2_2p5_farmers").empty().html(data["total_2_2p5_farmers"]);
                        $("#range_2_2p5_male").empty().html(data["total_2_2p5_male"]);
                        $("#range_2_2p5_female").empty().html(data["total_2_2p5_female"]);
                    $("#range_2p5_3_farmers").empty().html(data["total_2p5_3_farmers"]);
                        $("#range_2p5_3_male").empty().html(data["total_2p5_3_male"]);
                        $("#range_2p5_3_female").empty().html(data["total_2p5_3_female"]);
                    $("#range_3_farmers").empty().html(data["total_3_farmers"]);
                        $("#range_3_male").empty().html(data["total_3_male"]);
                        $("#range_3_female").empty().html(data["total_3_female"]);
                       
                }
            })
        }

        function load_top5(pie_data){            
            Highcharts.chart('top5_chart', {
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie',
                    margin: [0, 0, 0, 0],
                    spacingTop: 0,
                    spacingBottom: 0,
                    spacingLeft: 0,
                    spacingRight: 0
                },
                title: {
                    text: ''
                },
                tooltip: {
                    useHTML: true,
                    headerFormat: '',
                    pointFormat: '{point.name}: <b>{point.y:,.0f} bag(s)</b>'
                },
                plotOptions: {
                    pie: {
                        size:'100%',
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>',
                            distance: '-40%'
                        },
                        showInLegend: false
                    }
                },
                series: [{
                    name: 'Total',
                    colorByPoint: true,
                    data: pie_data
                }]
            });
        }

        function load_province_data(){
            $.ajax({
                type: 'POST',
                url: "{{ route('analytics.top_provinces') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                },
                success: function(data){
                    load_province_chart(data["provinces"], data["seeds"]);
                    $("#chart_gif").css("display", "none");
                }
            })
        }


        function load_summary_chart(){
            $.ajax({
                type: 'POST',
                url: "{{ route('analytics.summary.per_variety_chart') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                },
                success: function(data){
                    summary_chart(data["varities"], data["bags"], data["farmers"]);
                }
            })
        }

        function summary_chart(varities, bags, farmers){
            $('#summary_chart').highcharts({
                chart: {
                        type: 'bar'
                    },
                    title:{
                        text:''
                    },
                    xAxis: {
                        categories: varities
                    },
                    yAxis: {
                        title: {
                            text: ''
                        }
                    },
                    series: [{
                        name: 'Total Bags',
                        data: bags,
                        color: "rgb(214, 237, 5)"
                    },{
                        name: 'Total Farmers',
                        data: farmers,
                        color: "rgb(212, 34, 61)"
                    }]
            });
        }

        $("#switch_graph_btn").on("click", function(e){
            $("#switch_graph_btn").css("display", "none");
            $("#switch_table_btn").css("display", "block");

            $("#summary_variety_tbl").css("display", "none");
            $("#summary_chart").css("display", "block");
        });

        $("#switch_table_btn").on("click", function(e){
            $("#switch_graph_btn").css("display", "block");
            $("#switch_table_btn").css("display", "none");

            $("#summary_variety_tbl").css("display", "block");
            $("#summary_chart").css("display", "none");
        });
    </script>
@endpush

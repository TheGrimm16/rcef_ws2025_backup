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

    <div class="row">
        <div class="col-md-5">
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
        <div class="col-md-7">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Total Beneficiaries</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                            <div class="count" id="range_total_beneficiaries"><i class="fa fa-users"></i> --</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="x_panel">
                <div class="x_title">
                    <h2>Farmers with <= 1ha of farm area</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                            <div class="count" id="range_1ha_farmers"><i class="fa fa-users"></i> --</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="x_panel">
                <div class="x_title">
                    <h2>Farmers with <= 2ha of farm area</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                            <div class="count" id="range_2ha_farmers"><i class="fa fa-users"></i> --</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="x_panel">
                <div class="x_title">
                    <h2>Farmers with <= 3ha of farm area</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                            <div class="count" id="range_3ha_farmers"><i class="fa fa-users"></i> --</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="x_panel">
                <div class="x_title">
                    <h2>Farmers with > 3ha of farm area</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                            <div class="count" id="range_big_farmers"><i class="fa fa-users"></i> --</div>
                        </div>
                    </div>
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
        });

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
            $("#range_total_beneficiaries").empty().html("loading please wait...");
            $("#range_1ha_farmers").empty().html("loading please wait...");
            $("#range_2ha_farmers").empty().html("loading please wait...");
            $("#range_3ha_farmers").empty().html("loading please wait...");
            $("#range_big_farmers").empty().html("loading please wait...");

            $.ajax({
                type: 'POST',
                url: "{{ route('analytics.area_range') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                },
                success: function(data){
                    $("#range_total_beneficiaries").empty().html(data["total_beneficiaries"]);
                    $("#range_1ha_farmers").empty().html(data["total_1ha_farmers"]);
                    $("#range_2ha_farmers").empty().html(data["total_2ha_farmers"]);
                    $("#range_3ha_farmers").empty().html(data["total_3ha_farmers"]);
                    $("#range_big_farmers").empty().html(data["total_big_farmers"]);
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
    </script>
@endpush

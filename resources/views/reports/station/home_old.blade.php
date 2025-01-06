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
    .tile_count .tile_stats_count:before {
        content: "";
        position: absolute;
        left: 0;
        height: 65px;
        border-left: 0;
        margin-top: 10px;
    }
    .coop-link:hover{
        color: #1b0000;
    }
  </style>
@endsection

@section('content')

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="col-md-12 col-sm-12 col-xs-12">

        <div class="row">
            <div class="col-md-7">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>
                            Covered Areas of the Station
                        </h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <select name="region" id="region" class="form-control">
                            <option value="0">Please select a PhilRice branch station</option>
                            @foreach ($covered_regions as $row)
                                <option value="{{$row->region_name}}">{{$row->region_name}}</option>
                            @endforeach
                        </select>
                    
                        <button class="btn btn-success form-control" style="margin-top:10px;" id="load_btn"><i class="fa fa-database"></i> LOAD REPORT</button>
                    </div>
                </div>

                <div class="x_panel">
                    <div class="x_title">
                        <h2>Participating Seed Cooperatives</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <table class="table table-bordered table-striped" id="station_seedCoop_tbl">
                            <thead>
                                <tr>
                                    <th style="width: 30px !important">#</th>
                                    <th>Seed Cooperative</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Total Provinces</h2>
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
                        <h2>Total Municipalities</h2>
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
                        <h2>Total Bags Delivered (Confirmed)</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <div class="row tile_count" style="margin: 0">
                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                <div class="count" id="count_bags_confirmed">--</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="x_panel">
                    <div class="x_title">
                        <h2>Total Bags Delivered (Inspected & Accepted)</h2>
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
            </div>
        </div>
        <hr>

        <div class="row">
            <div class="col-md-6">
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
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div id="seed_chart_container" style="width:100%; height:500px;"></div>   
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
                    <div id="coop_chart_container" style="width:100%; height:400px;display: none"></div>             
                </div>
            </div>
        </div>
    </div>
    <!-- SEED COOPERATIVE MODAL -->

@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>
    <script src=" {{ asset('public/js/highcharts.js') }} "></script>

    <script>
        Highcharts.setOptions({
            lang: {
                decimalPoint: '.',
                thousandsSep: ','
            },

            tooltip: {
                yDecimals: 2 // If you want to add 2 decimals
            }
        });
        $("#station_seedCoop_tbl").DataTable();
        $("#sg_per_coop_tbl").DataTable();
        $("#seed_variety_tbl").DataTable();


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

        function populateSeedChart(chart_data, region){
            Highcharts.chart('seed_chart_container', {
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: 'Seed Variety List for `'+region+'`'
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

        function load_region_cooperatives(region){
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
                        region : region
                    }
                },
                "columns":[
                    {data: 'row_count'},
                    {data: 'coop_link'}
                ]
            });
        }

        function load_seedVariety_list(region){
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
                        "region": region
                    }
                },
                "columns":[
                    {"data": "row_count"},
                    {"data": "seed_variety"}
                ]
            });
        }

        function load_seed_chart(region){
            $.ajax({
                type: 'POST',
                url: "{{ route('station_report.seed.chart') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    region: region
                },
                success: function(data){
                    populateSeedChart(data, region);
                }
            });
        }

        $("#load_btn").on("click", function(e){
            var region = $("#region").val();

            $("#count_provinces").empty().html("loading...");
            $("#count_municipalities").empty().html("loading...");
            $("#count_bags_accepted").empty().html("loading...");
            $("#count_bags_confirmed").empty().html("loading...");

            $.ajax({
                type: 'POST',
                url: "{{ route('station_report.load_total_values') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    region: region
                },
                success: function(data){
                    $("#count_provinces").empty().html(data["total_provinces"]);
                    $("#count_municipalities").empty().html(data["total_municipalities"]);
                    $("#count_bags_accepted").empty().html(data["total_bags"]);
                    $("#count_bags_confirmed").empty().html(data["total_confirmed"]);
                }

            }).done(function(){
                load_region_cooperatives(region);
            
            }).done(function(){
                load_seedVariety_list(region);
            
            }).done(function(){
                load_seed_chart(region);
            });
        });
    </script>
@endpush

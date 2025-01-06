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
  </style>
@endsection

@section('content')

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="col-md-12 col-sm-12 col-xs-12">
	
	
		<!-- <div class="row">
            <div class="col-md-12" style="margin-bottom: -10px;">
                <div class="alert alert-warning alert-dismissible fade in" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <strong><i class="fa fa-info-circle"></i> Reminder:</strong> The system is displaying the latest update as of: <strong><u>{{$latest_mirror_delivery_date}}</u></strong>
                </div>
            </div>
        </div> -->

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" id="headingOne">
                        <h5 class="mb-0" style="margin:0">
                            <button style="color: #7387a8;text-decoration:none;" class="btn btn-link">
                                <strong>SEED COOPERATIVE: <u>{{strtoupper($coop_name)}}</u></strong>
                            </button>
                        </h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Target Commitment (20kg/bag)</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <div class="row tile_count" style="margin: 0">
                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                <div class="count" id="total_commitment">--</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Confirmed Deliveries (20kg/bag)</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <div class="row tile_count" style="margin: 0">
                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                <div class="count" id="total_confirmed">--</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Accepted Seeds (20kg/bag)</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <div class="row tile_count" style="margin: 0">
                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                <div class="count" id="total_delivered">--</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>COMMITMENT SUMMARY</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <!-- <img src="{{asset('public/images/load_chart.gif')}}" alt="" id="loading_gif" style="display: block;margin: auto;height: 300px;padding-top: 25px;"> -->
                        <!-- <div id="container" style="width:100%; height:400px;" style="display:none"></div> -->
                        <table class="table table-bordered table-striped" id="coop_commitment_summary">
                            <thead>
                                <th>VARIETY</th>                               
                                @foreach ($coop_regions as $region)
                                    <th>{{ $region->region_name }} (20kg/bag)</th>
                                @endforeach
                                <th>TOTAL COMMITMENT (20kg/bag)</th>
                                <th>ACTION</th>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>DELIVERY SUMMARY</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <img src="{{asset('public/images/load_chart.gif')}}" alt="" id="loading_gif" style="display: block;margin: auto;height: 300px;padding-top: 25px;">
                        <div id="container" style="width:100%; height:400px;" style="display:none"></div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>


    <!-- Seed Growers commitments -->
    <div id="sg_commitment_modal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Seed Grower Commitments</h4>
                </div>
                <div class="modal-body form-horizontal form-label-left">
					<!-- <input type="hidden" class="form-control" id="member_id" name="member_id"> -->
					<div id="sg_variety_container"></div>
                </div>
				<!-- <div class="modal-footer">
					<button type="button" class="btn btn-warning" id="edit_sg_btn"><i class="fa fa-edit"></i> Edit SG Profile</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-trash"></i> Close</button>
                </div> -->
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

        $("#sg_tags_tbl").DataTable();
       

        Highcharts.setOptions({
            lang: {
                decimalPoint: '.',
                thousandsSep: ','
            },

            tooltip: {
                yDecimals: 2 // If you want to add 2 decimals
            }
        });

        $("#total_commitment").empty().html("loading...");
        $("#total_delivered").empty().html("loading...");
        $("#total_confirmed").empty().html("loading...");

        var coop_accre = "{{$coop_accre}}";
        $.ajax({
            type: 'POST',
            url: "{{ route('load.coop_operator.total_values') }}",
            data: {
                _token: "{{ csrf_token() }}",
                coop_accre: coop_accre
            },
            success: function(data){
                populateChart(data['variety_list'], data['commitment_list'], data['delivered_list'], data['confirmed_list']);
                $("#total_commitment").empty().html(data["total_commitment"]);
                $("#total_delivered").empty().html(data["total_delivered"]);
                $("#total_confirmed").empty().html(data["total_confirmed"]);

                $("#loading_gif").css("display", "none");
                $("#container").css("display", "inline-block");
            }
        });

        function populateChart(varieties, commitment, delivered, confirmed){
            $('#container').highcharts({
                chart: {
                    type: 'bar'
                },
                title:{
                    text:''
                },
                xAxis: {
                    categories: varieties
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
                        y: 50,
                        opacity: 0.5
                    },
                series: [{
                        name: 'Target Commitment (20kg/bag)',
                        data: commitment
                    }, {
                        name: 'Confirmed Deliveries (20kg/bag)',
                        data: confirmed
                    }, {
                        name: 'Accepted Deliveries (20kg/bag)',
                        data: delivered
                    }]
            });
        }
        

        function getRegionDetails(index){
            $("#icon_id_"+index).toggleClass('fa-plus fa-minus');
            var region = $("#region_title_"+index).html();
            var coop_accre = "{{$coop_accre}}";
            
            $("#table_"+index).DataTable().clear();
            $("#table_"+index).DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('load.deliveries.region') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        "coop_accre": coop_accre,
                        "region": region
                    }
                },
                "columns":[
                    {"data": "batchTicketNumber"},
                    {"data": "province"},
                    {"data": "municipality"},
                    {"data": "dropOffPoint"},
                    {"data": "seedVariety"},
                    {"data": "date_inspected"},
                ]
            });
        }

        function getRegionScheduleDetails(index){
            $("#icon_schedule_id_"+index).toggleClass('fa-plus fa-minus');
            var region = $("#region_schedule_title_"+index).html();
            var coop_accre = "{{$coop_accre}}";

            $("#table_schedule_"+index).DataTable().clear();
            $("#table_schedule_"+index).DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('load.coop.schedule_details') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        "coop_accre": coop_accre,
                        "region": region
                    }
                },
                "columns":[
                    {"data": "province"},
                    {"data": "municipality"},
                    {"data": "dropOffPoint"},
                    {"data": "expected_delivery_volume"},
                    {"data": "actual_delivery_volume"},
                    {"data": "delivery_date"},
                ]
            });
        }
        
        $('#blacklist_sg_modal').on('show.bs.modal', function (e) {
            var sg_id = $(e.relatedTarget).data('id');
            $("#sg_id").val(sg_id);
            $("#blacklist_reason").val('');
        });

        $('#sg_tags_modal').on('show.bs.modal', function (e) {
            var sg_id = $(e.relatedTarget).data('id');
            var coop_accre = "{{$coop_accre}}";

            $.ajax({
                type: 'POST',
                url: "{{ route('load.sg.details') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    coop_accre: coop_accre,
                    sg_id: sg_id
                },
                success: function(data){
                    $("#sg_tags_name").empty().html(data["sg_name"]);
                    $("#sg_tags_coop").empty().html(data["coop_name"]);
                }
            }).done(function(e){
                $("#sg_tags_tbl").DataTable().clear();
                $("#sg_tags_tbl").DataTable({
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "ajax": {
                        "url": "{{ route('load.sg.tags') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            "sg_id": sg_id,
                            "coop_accre": coop_accre
                        }
                    },
                    "columns":[
                        {"data": "seed_tag"},
                        {"data": "seedVariety"},
                        {"data": "bags_passed"}
                    ]
                });
            });
        });

        // new input as of 03-11-2021
        // $.ajax({
        //     type: "POST",
        //     url: "{{ route('table.coop_operator.coop_commitment_variety') }}",
        //     data: {
        //         _token: "{{ csrf_token() }}",
        //     },
        //     success: function (response) {
        //         console.log(response);
        //     }
        // }).done(function(e){
        //     $("#coop_commitment_summary").DataTable().clear();
            
        // });
        var regions_count = "{{ $coop_regions_count }}";
        var committed_data = [
            {"data": "seed_variety", "searchable": false}
        ];

        for (let index = 1; index <= regions_count; index++) {
            const data = 'region_col_no_' + index;
            committed_data.push({'data' : data, "searchable": false});
        }
        committed_data.push({'data' : "volume_commitment", "searchable": false});
        committed_data.push({'data' : "action", "searchable": false});
        // $("#coop_commitment_summary").DataTable();
        $("#coop_commitment_summary").DataTable({
            "bDestroy": true,
            "autoWidth": false,
            "searchHighlight": true,
            "processing": true,
            "serverSide": true,
            "orderMulti": true,
            "order": [],
            "ajax": {
                "url": "{{ route('table.coop_operator.coop_commitment_variety') }}",
                "dataType": "json",
                "type": "POST",
                "data":{
                    _token: "{{ csrf_token() }}",
                }
            },
            "columns": committed_data
        });

        $('#coop_commitment_summary tbody').on('click', 'td #view_sg',function (e) {
           var variety = $(this).data('variety');

           $.ajax({
                type: "POST",
                url: "{{ route('table.coop_operator.sg_commitment_variety_html') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    variety: variety
                },
                dataType: "HTML",
                success: function (response) {
                    $("#sg_variety_container").html(response);
                    get_sg_commitments_table(variety);
                }
           });
        });

        function get_sg_commitments_table(variety){
            $("#sg_commitment_summary").DataTable().clear();
            $("#sg_commitment_summary").DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('table.coop_operator.sg_commitment_variety_table') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": {
                        _token: "{{ csrf_token() }}",
                        variety: variety
                    }
                },
                "columns": [{
                        data: "first_name",
                    },
                    {
                        data: "last_name"
                    },
                    {
                        data: "allocated_area"
                    },
                    {
                        data: "commitment_variety"
                    },{
                        data: "target_volume", name: 'allocated_area'
                    },
                ]
            });
        }
        
    </script>
@endpush

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
{{"Page Closed"}}

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="col-md-12 col-sm-12 col-xs-12">

        <!-- FILTER PANEL -->
        <div class="x_panel">
            <div class="x_title">
                <h2>
                    Select a Seed Cooperative
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <div class="row">
                    <div class="col-md-9">
                        <select name="coop" id="coop" class="form-control">
                            <option value="0">Please select a seed cooperative</option>
                            @foreach ($coop_list as $row)
                                <option value="{{$row->accreditation_no}}">{{$row->coopName}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <button class="btn btn-success form-control" id="load_btn"><i class="fa fa-database"></i> LOAD DASHBOARD DATA</button>
                    </div>
                </div>
            </div>
        </div><br>
        <!-- FILTER PANEL -->

        <div class="x_panel" id="sched_section" style="display: none">
            <div class="x_title">
                <h2>
                    Confirmed Deliveries
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="alert alert-success alert-dismissible fade in" role="alert" style="margin-bottom: 0;">
                    <strong><i class="fa fa-info-circle"></i> Notice:</strong> These are deliveries confirmed by the seed cooperative using the RCEP-DI app, Please click the <i class="fa fa-plus"></i> icon to view more details.
                </div>
                <div class="accordion" id="sched_accordion">
                    
                </div>
            </div>
        </div><br>

        <div class="x_panel" id="accordion_section" style="display: none">
            <div class="x_title">
                <h2>Inspected and Accepted Deliveries</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="alert alert-warning alert-dismissible fade in" role="alert" style="margin-bottom: 0;">
                    <strong><i class="fa fa-info-circle"></i> Notice:</strong> Please click the <i class="fa fa-plus"></i> icon to expand & view the delivery details for the selected region.
                </div>
                <div class="accordion" id="region_accordion">
                    
                </div>
            </div>
        </div>

        <div class="x_panel" id="sg_section" style="display: none">
            <div class="x_title">
                <h2 id="sg_table_title">
                    Participating SG List
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <table class="table table-hover table-striped table-bordered" id="sg_table">
                    <thead>
                        <th style="width: 150px;">Full Name</th>
                        <th style="width: 300px">Varities (Max of 3 displayed)</th>
                        <th>Bags Passed</th>
                        <th>Status</th>
                        <th><center>Action</center></th>
                    </thead>
                </table>
            </div>
        </div><br>
 
        <div class="x_panel" id="chart_section" style="display: none">
            <div class="x_title">
                <h2>
                    Delivery Status: Commitment vs Delivered <span id="seeds_total"></span>
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <div class="row">
                    <div class="col-md-10">
                        <div id="container" style="width:100%; height:400px;"></div>
                     
                    </div>
                    <div class="col-md-2">
                        <div class="row tile_count">
                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count">
                                <span class="count_top">Total Commitment</span>
                                <div class="count" id="total_commitment">--</div>
                            </div>

                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count">
                                <span class="count_top">Total Delivered</span>
                                <div class="count" id="total_delivered">--</div>
                            </div>
                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count">
                               <button onclick="loadRegionalData(this.value);" id="switchButton" value="1" class="btn btn-success"> Switch To Region Data </button>
                             
                            </div>
                        </div>
                        
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                           <div id="regional_tbl" style="display: none;">
                            <table class="table table-hover table-striped table-bordered" id="region_table">
                                <thead>
                                    <th >Region</th>
                                    <th >Variety</th>
                                    <th>Commitment</th>
                                    <th>Delivered</th>
                                </thead>
                            </table>  
                        </div>
                    </div>
                </div>


            </div>
        </div><br>

        <!-- SG TAGS MODAL -->
        <div id="sg_tags_modal" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="sg_tags_name">
                            {SG_NAME}
                        </h4>
                        <span id="sg_tags_coop">{COOP_NAME}</span>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <img src="{{asset('public/images/farm.png')}}" alt="" style="max-width:100%;max-height:100%;">
                            </div>
                            <div class="col-md-6">
                                <table class="table table-bordered table-striped" id="sg_tags_tbl">
                                    <thead>
                                        <th>SeedTag</th>
                                        <th>Variety</th>
                                        <th>Bags Passed</th>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- SG TAGS MODAL -->
      

        <!-- BLACKLIST MODAL -->
        <div id="blacklist_sg_modal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">
                            CHANGE SEED GROWER STATUS: {BLACKLISTED}
                        </h4>
                    </div>
                    <div class="modal-body">
                        <p>You are about to change the classification of the selected seed grower as `blacklisted`, by doing so, you will disable the lots (passed) tagged to selected seed grower 
                            from the selection in the delivery process of the associated seed cooperative. Do you wish to proceed?</p>

                        <textarea name="blacklist_reason" id="blacklist_reason" class="form-control" rows="5" placeholder="Pleasee state a valid reason..."></textarea>
                            
                        <input type="hidden" value="" id="sg_id" name="sg_id">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" id="add_to_blacklist_btn"><i class="fa fa-tags"></i> Add to Blacklist</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- BLACKLIST MODAL -->

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

        $("#load_btn").on("click", function(e){
            var coop_accre = $("#coop").val();
            $("#accordion_section").css("display", "none");
            $("#sched_section").css("display", "none");
            $("#chart_section").css("display", "none");
            $("#sg_section").css("display", "none");
            $("#load_btn").empty().html('<i class="fa fa-spinner fa-spin"></i> Generating data...');
            $("#load_btn").attr("disabled", "");

            $("#region_accordion").empty().append("loading delivery data please wait...");
            $("#sched_accordion").empty().append("loading schedule data please wait...");

            $.ajax({
                type: 'POST',
                url: "{{ route('load.coop.schedule') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    coop_accre: coop_accre
                },
                success: function(data){
                    $("#sched_accordion").empty();
                    var region_str = '';
                    jQuery.each(data, function(index, array_value){
                        region_str = region_str + '<div class="card">';
                        region_str = region_str + '<div class="card-header" id="headingOne">';
                        region_str = region_str + '<h5 class="mb-0" style="margin:0">';
                        region_str = region_str + '<button style="color: #7387a8;text-decoration:none;" class="btn btn-link" id="region_schedule_title_'+index+'">'+array_value+'</button>';
                        region_str = region_str + '<i class="fa fa-plus pull-right" id="icon_schedule_id_'+index+'" style="margin-top: 12px;margin-right: 10px;" data-toggle="collapse" data-target="#collapse_schedule_'+index+'" aria-controls="'+index+'" onclick="getRegionScheduleDetails('+index+')"></i>';
                        region_str = region_str + '</h5>';
                        region_str = region_str + '</div>';
                        region_str = region_str + '<div id="collapse_schedule_'+index+'" class="collapse" aria-labelledby="headingOne" data-parent="#accordion" style="margin-top: .5vw;">';
                        region_str = region_str + '<div class="card-body">';
                        region_str = region_str + '<table class="table table-bordered table-striped" id="table_schedule_'+index+'">';
                        region_str = region_str + '<thead>';
                        region_str = region_str + '<th>Province</th>';
                        region_str = region_str + '<th>Municipalitty</th>';
                        region_str = region_str + '<th>Dropoff Point</th>';
                        region_str = region_str + '<th>Expected Delivery</th>';
                        region_str = region_str + '<th>Actual Delivery</th>';
                        region_str = region_str + '<th>Date of Delivery</th>';
                        region_str = region_str + '</thead>';
                        region_str = region_str + '</table>';
                        region_str = region_str + '</div>';
                        region_str = region_str + '</div>';
                        region_str = region_str + '</div>';
                    });

                    $("#sched_section").css("display", "inline-grid");
                    $("#sched_accordion").append(region_str);
                }
            }).done(function(){
                $.ajax({
                    type: 'POST',
                    url: "{{ route('load.coop.deliveries') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        coop_accre: coop_accre
                    },
                    success: function(data){
                        $("#region_accordion").empty();
                        var region_str = '';
                        jQuery.each(data, function(index, array_value){
                            region_str = region_str + '<div class="card">';
                            region_str = region_str + '<div class="card-header" id="headingOne">';
                            region_str = region_str + '<h5 class="mb-0" style="margin:0">';
                            region_str = region_str + '<button style="color: #7387a8;text-decoration:none;" class="btn btn-link" id="region_title_'+index+'">'+array_value+'</button>';
                            region_str = region_str + '<i class="fa fa-plus pull-right" id="icon_id_'+index+'" style="margin-top: 12px;margin-right: 10px;" data-toggle="collapse" data-target="#collapse'+index+'" aria-controls="'+index+'" onclick="getRegionDetails('+index+')"></i>';
                            region_str = region_str + '</h5>';
                            region_str = region_str + '</div>';
                            region_str = region_str + '<div id="collapse'+index+'" class="collapse" aria-labelledby="headingOne" data-parent="#accordion" style="margin-top: .5vw;">';
                            region_str = region_str + '<div class="card-body">';
                            region_str = region_str + '<table class="table table-bordered table-striped" id="table_'+index+'">';
                            region_str = region_str + '<thead>';
                            region_str = region_str + '<th>Bactch Ticket #</th>';
                            region_str = region_str + '<th>Province</th>';
                            region_str = region_str + '<th>Municipalitty</th>';
                            region_str = region_str + '<th>Dropoff Point</th>';
                            region_str = region_str + '<th>Variety</th>';
                            region_str = region_str + '<th>Date Inspected</th>';
                            region_str = region_str + '</thead>';
                            region_str = region_str + '</table>';
                            region_str = region_str + '</div>';
                            region_str = region_str + '</div>';
                            region_str = region_str + '</div>';
                        });

                        $("#accordion_section").css("display", "inline-grid");
                        $("#region_accordion").append(region_str);
                    }
                });
            }).done(function(e){
                $("#sg_section").css("display", "inline-grid");
                $("#sg_table_title").empty().html($("#coop option:selected").text()+" - Participating SG List");
                $("#sg_table").DataTable().clear();
                $("#sg_table").DataTable({
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
                            "coop_accre": coop_accre
                        }
                    },
                    "columns":[
                        {"data": "full_name"},
                        {"data": "seed_tags", searchable: false},
                        {"data": "bags_passed", searchable: false},
                        {"data": "blacklist_status", searchable: false},
                        {"data": "action", searchable: false},
                    ]
                });
            }).done(function(){
                $.ajax({
                    type: 'POST',
                    url: "{{ route('load.coop.seeds') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        coop_accre: coop_accre
                    },
                    success: function(data){
                        populateChart(data['variety_list'], data['commitment_list'], data['delivered_list']);
                        $("#chart_section").css("display", "inline-grid");
                        //$("#seeds_total").empty().html("["+data["total_commitment"]+" vs "+data["total_delivered"]+"]")
                        $("#total_commitment").empty().html(data["total_commitment"]);
                        $("#total_delivered").empty().html(data["total_delivered"]);
                        
                        $("#load_btn").removeAttr("disabled");
                        $("#load_btn").empty().html('<i class="fa fa-database"></i> LOAD DASHBOARD DATA');
                    }
                });



            });
        });




    function loadRegionalData(val) {
        HoldOn.open(holdon_options);
        var coop_accre = $("#coop").val();
        if(val==1){
            
         $.ajax({
                    type: 'POST',
                    url: "{{ route('load.coop.regional') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        coop_accre: coop_accre
                    },
                    success: function(data){
                        $("#container").removeAttr("style");
                        $("#container").attr("style", "width:100%; height:700px;");

                        $("#regional_tbl").removeAttr("style");
                        $("#regional_tbl").attr("style", "width:100%; height:400px;")

                        populateRegionalChart(data['region_list'],data['variety_list'],data['commitment_list'], data['delivered_list'], data['series_arr']);
                        $("#chart_section").css("display", "inline-grid");
                        //$("#seeds_total").empty().html("["+data["total_commitment"]+" vs "+data["total_delivered"]+"]")
                        $("#total_commitment").empty().html(data["total_commitment"]);
                        $("#total_delivered").empty().html(data["total_delivered"]);
                        
                        $("#load_btn").removeAttr("disabled");
                        $("#load_btn").empty().html('<i class="fa fa-database"></i> LOAD DASHBOARD DATA');



                        
                        HoldOn.close();
                    }
                }); 
        
         $("#switchButton").val("0");
         $("#switchButton").text("Switch to seed variety");
        }else{
        $.ajax({
                    type: 'POST',
                    url: "{{ route('load.coop.seeds') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        coop_accre: coop_accre
                    },
                    success: function(data){
                        $("#container").removeAttr("style");
                        $("#container").attr("style", "width:100%; height:400px;");
                        $("#regional_tbl").removeAttr("style");
                        $("#regional_tbl").attr("style", "display:none;")

                        populateChart(data['variety_list'], data['commitment_list'], data['delivered_list']);
                        $("#chart_section").css("display", "inline-grid");
                        //$("#seeds_total").empty().html("["+data["total_commitment"]+" vs "+data["total_delivered"]+"]")
                        $("#total_commitment").empty().html(data["total_commitment"]);
                        $("#total_delivered").empty().html(data["total_delivered"]);

                        
                        HoldOn.close();
                    }
                });

        $("#switchButton").val("1");
        $("#switchButton").text("Switch To Region Data");
          
     }



     
    }




    function populateRegionalChart(regionlist, varieties, commitment, delivered, series){



           $('#region_table').DataTable( {
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                data: series,
                columns: [
                    { "data": "region" },
                    { "data": "variety" },
                    { "data": "commitment" },
                    { "data": "delivered" },
                ]
            } );




    var region_list = regionlist.filter((value, index) => {
    return regionlist.indexOf(value) === index;
    });  

    var varietylist = varieties.filter((value, index) => {
    return varieties.indexOf(value) === index;
    });  

    var series_data = [];

    for (var i=0; i < varietylist.length; i++){   
    var commit = [];
    var deliver = [];



        for (var b=0; b < varieties.length; b++){       
            if (varietylist[i] === varieties[b] ){
                commit.push([commitment[b]]);
                deliver.push([delivered[b]]);
            }
        }




    series_data.push({name: varietylist[i]+"_commitment", data:commit, color:"#F3E2A9", dataLabels: {
                format: varietylist[i]+"_commitment: "+"{point.y}",
            }});
    series_data.push({name: varietylist[i]+"_delivered", data:deliver, color:"#81F79F",  dataLabels: {
                format: varietylist[i]+"_delivered: "+"{point.y}",
            }});
    //series_data.push({name: 'NSIC2', data:[200]});
        
    }











            $('#container').highcharts({
                chart: {
                    type: 'bar'
                },
                title:{
                    text:''
                },
                  xAxis: {
                        categories: region_list,
                        title: {
                            text: null
                        }
                    },
                yAxis: {
                    title: {
                        text: ''
                    }
                },
                plotOptions: {
                    bar: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            inside: true
                        },
                        showInLegend: false
                    }
                },
                series:series_data
            });
    }



        function populateChart(varieties, commitment, delivered){
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
                series: [{
                        name: 'Commitment',
                        data: commitment
                    }, {
                        name: 'Delivered',
                        data: delivered
                    }]
            });
        }

        function getRegionDetails(index){
            $("#icon_id_"+index).toggleClass('fa-plus fa-minus');
            var region = $("#region_title_"+index).html();
            var coop_accre = $("#coop").val();
            
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
            var coop_accre = $("#coop").val();

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
            var coop_accre = $(e.relatedTarget).data('coop');

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

        $("#add_to_blacklist_btn").on("click", function(e){
            var sg_id = $("#sg_id").val();
            var blacklist_reason = $("#blacklist_reason").val();
            var coop_accre = $("#coop").val();
            
            if($("#blacklist_reason").val() == ''){
                alert("Please state a valid reason for this action.");
            }else{
                $.ajax({
                    type: 'POST',
                    url: "{{ route('coop.member.blacklist') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        sg_id: sg_id,
                        blacklist_reason: blacklist_reason
                    },
                    success: function(data){
                        $('#blacklist_sg_modal').modal('hide');
                    }
                }).done(function(){
                    $("#sg_table").DataTable().clear();
                    $("#sg_table").DataTable({
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
                                "coop_accre": coop_accre
                            }
                        },
                        "columns":[
                            {"data": "full_name"},
                            {"data": "seed_tags", searchable: false},
                            {"data": "bags_passed", searchable: false},
                            {"data": "blacklist_status", searchable: false},
                            {"data": "action", searchable: false},
                        ]
                    });
                });
            }
        });
        
    </script>
@endpush

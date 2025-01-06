@extends('layouts.index')

@section('styles')
    <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
@endsection

@section('content')
{{-- CSRF TOKEN --}}
<input type="hidden" name="_token" value="{{ csrf_token() }}">

<div>
    <div class="page-title">
        <div class="title_left">
            <h3>Monitoring Dashboard</h3>
        </div>
    </div>

    <div class="clearfix"></div>


    <div class="row">
        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>RCEF System - Summary</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <div class="row tile_count">
                        <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
                            <span class="count_top">Total Confirmed Delivery (bags)</span>
                            <div class="count">{{number_format($confirmed->total_bag_count)}}</div>
                        </div>
                        <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
                            <span class="count_top">Total Actual Delivery (bags)</span>
                            <div class="count">{{number_format($actual->total_bag_count)}}</div>
                        </div>
                        <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
                            <span class="count_top">Transferred - DS2019 seeds (bags)</span>
                            <div class="count">{{number_format($transferred->total_bag_count)}}</div>
                        </div>
                        <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
                            <span class="count_top">Distributed (bags) - as of 12AM today</span>
                            <div class="count">{{number_format($distributed->total_bags)}}</div>
                        </div>
                    </div>

                    <div class="row" style="margin-bottom: 20px;">
                        <div class="col-md-3">
                            <select class="form-control" id="region_select" name="region_select" style="margin-bottom:10px;">
                                <option value="0">Please select a Region</option>
                                @foreach ($regions as $r_row)
                                    <option value="{{$r_row->region}}">{{$r_row->region}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="province_select" name="province_select" style="margin-bottom:10px;">
                                <option value="0">Please select a province</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="municipality_select" name="municipality_select" style="margin-bottom:10px;">
                                <option value="0">Please select a municipality</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-success form-control" id="generate_delivery_btn"><i class="fa fa-bar-chart-o"></i> GENERATE DELIVERY DATA</button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered table-striped" id="delivery_summary_table">
                                <thead>
                                    <tr>
                                        <th>Dropoff Point</th>
                                        <th>Confirmed Delivery (bags)</th>
                                        <th>Actual Delivery (bags)</th>
                                        <th>Transferred (bags)</th>
                                    </tr>
                                </thead>
                            </table>
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
                    <h2 style="margin-top: 10px;">Delivery Schedule</h2>
                    <div class="input-group pull-right" style="width: 500px;">
                        <input type="text" name="date_of_delivery" id="date_of_delivery" class="form-control" value="{{$filter_start}} - {{$filter_end}}" />
                        <div class="input-group-btn">
                            <button class="btn btn-success" id="load_schedule_btn" style="margin:0">LOAD SCHEDULE</button>
                        </div>
                    </div>
                    
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <table class="table table-bordered table-striped" id="delivery_sched_tbl">
                        <thead>
                            <tr>
                                <th style="width: 200px;">Province</th>
                                <th>Municipality</th>
                                <th style="width: 200px;">Dropoff Point</th>
                                <th>Expected</th>
                                <th>Accepted</th>
                                <th>Date of Delivery</th>
                                <th>Status</th>
                            </tr>
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
                    <h2>Delivery Commitment per Seed Cooperative </h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div class="accordion">
                        <?php
                        $ctr = 0;
                        foreach ($coops as $coop):
                            $show = ($ctr == 0 ? "show" : "");
                            $collapse = ($ctr != 0 ? "collapsed" : "");
                            $expanded = ($ctr == 0 ? "true" : "false");
                            echo '
                            <div class="card"  aria-expanded="' . $expanded . '" data-toggle="collapse" data-target="#collapse' . $ctr . '" aria-controls="collapse' . $ctr . '">
                                <div class="card-header" id="headingOne">
                                    <h5 class="mb-0" style="margin:0">
                                        <button style="color: #7387a8;text-decoration:none;" class="btn btn-link ' . $collapse . '">
                                            ' . $coop['region'] . ' <strong><span class="badge badge-dark">' . $coop['coop_count'] . '</span></strong>
                                        </button>
                                    </h5>
                                </div>

                                <div id="collapse' . $ctr . '" class="collapse" aria-labelledby="headingOne" data-parent="#accordion" style="margin-top: .5vw;">
                                    <div class="card-body">
                                    <ul class="list-group row" style="width: 90%;margin-left: 1vw;">';

                            echo '<li class = "list-group-item col-xs-6"><strong>Seed Cooperative</strong></li> 
                                      <li class = "list-group-item col-xs-6"><strong>Commitment (20kg/bag)</strong></li>';
                            foreach ($coop['cooperatives'] as $item):
                                echo '<li class = "list-group-item col-xs-6">' . $item->coopName . '</li> 
                                      <li class = "list-group-item col-xs-6">' . number_format($item->total_value) . ' bags</li>';
                            endforeach;

                            echo '</ul>
                                    </div>
                                </div>
                            </div>';
                            $ctr++;
                        endforeach;
                        ?>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

@endsection()

@push('scripts')
@endpush

@push('scripts')
<script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
<script src=" {{ asset('public/js/select2.min.js') }} "></script>
<script src=" {{ asset('public/js/parsely.js') }} "></script>
<script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

<script>
    $('#delivery_summary_table').DataTable();
    $("#date_of_delivery").daterangepicker(null,function(a,b,c){
        //console.log(a.toISOString(),b.toISOString(),c)
    });
    /*$('#delivery_summary_table').DataTable({
        columns: [
            {data: 'region', name: 'region'},
            {data: 'province', name: 'province'},
            {data: 'municipality', name: 'municipality'},
            {data: 'dropoff_point', name: 'dropoff_point'},
            {data: 'confirmed_delivery', name: 'confirmed_delivery'},
            {data: 'actual_delivery', name: 'actual_delivery'},
            {data: 'transferred', name: 'transferred'}
        ],
        processing: true,
        serverSide: true,
        ajax: {
            url: 'delivery_summary/datatable',
            method: 'GET'
        }
    });*/

    $("#delivery_sched_tbl").DataTable({
        "bDestroy": true,
        "autoWidth": false,
        "searchHighlight": true,
        "processing": true,
        "serverSide": true,
        "orderMulti": true,
        "order": [],
        "ajax": {
            "url": "{{ route('dashboard.delivery_schedule') }}",
            "dataType": "json",
            "type": "POST",
            "data":{
                "_token": "{{ csrf_token() }}",
                "week_start" : "{{ $week_start }}",
                "week_end" : "{{$week_end}}"
            }
        },
        "columns":[
            {data: 'province'},
            {data: 'municipality'},
            {data: 'dropOffPoint'},
            {data: 'expected_delivery_volume'},
            {data: 'actual_delivery_volume'},
            {data: 'delivery_date'},
            {data: 'status'},
        ]
    });

    $("#load_schedule_btn").on("click", function(e){
        var date_duration = $("#date_of_delivery").val();

        $("#delivery_sched_tbl").DataTable({
            "bDestroy": true,
            "autoWidth": false,
            "searchHighlight": true,
            "processing": true,
            "serverSide": true,
            "orderMulti": true,
            "order": [],
            "ajax": {
                "url": "{{ route('dashboard.delivery_schedule.custom') }}",
                "dataType": "json",
                "type": "POST",
                "data":{
                    "_token": "{{ csrf_token() }}",
                    date_duration : date_duration
                }
            },
            "columns":[
                {data: 'province'},
                {data: 'municipality'},
                {data: 'dropOffPoint'},
                {data: 'expected_delivery_volume'},
                {data: 'actual_delivery_volume'},
                {data: 'delivery_date'},
                {data: 'status'},
            ]
        });
    });

    $("#region_select").on("change", function(e){
        var region = $(this).val();

        $("#province_select").empty().append("<option value='0'>Loading provinces please wait...</option>");
        $.ajax({
            type: 'POST',
            url: "{{ route('delivery_summary.provinces') }}",
            data: {
                _token: "{{ csrf_token() }}",
                region: region
            },
            success: function(data){
                $("#province_select").empty().append("<option value='0'>Please select a province</option>");
                $("#province_select").append(data);
            }
        });
    });

    $("#province_select").on("change", function(e){
        var region = $("#region_select").val();
        var province = $(this).val();

        $("#municipality_select").empty().append("<option value='0'>Loading municipalities please wait...</option>");
        $.ajax({
            type: 'POST',
            url: "{{ route('delivery_summary.municipalities') }}",
            data: {
                _token: "{{ csrf_token() }}",
                region: region,
                province: province
            },
            success: function(data){
                $("#municipality_select").empty().append("<option value='0'>Please select a municipality</option>");
                $("#municipality_select").append(data);
            }
        });
    });

    $("#generate_delivery_btn").on("click", function(e){
        var region = $("#region_select").val();
        var province = $("#province_select").val();
        var municipality = $("#municipality_select").val();

        $('#delivery_summary_table').DataTable().clear();
        $("#delivery_summary_table").DataTable({
            "bDestroy": true,
            "autoWidth": false,
            "searchHighlight": true,
            "processing": true,
            "serverSide": true,
            "orderMulti": true,
            "order": [],
            "ajax": {
                "url": "{{ route('delivery_summary.datatable') }}",
                "dataType": "json",
                "type": "POST",
                "data":{
                    "_token": "{{ csrf_token() }}",
                    region: region,
                    province: province,
                    municipality: municipality
                }
            },
            "columns":[
                {data: 'dropoff_point', name: 'dropoff_point'},
                {data: 'confirmed_delivery', name: 'confirmed_delivery'},
                {data: 'actual_delivery', name: 'actual_delivery'},
                {data: 'transferred', name: 'transferred'}
            ]
        });
    });

    
</script>
@endpush
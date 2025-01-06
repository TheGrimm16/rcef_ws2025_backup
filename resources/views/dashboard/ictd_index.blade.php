@extends('layouts.index')

@section('styles')
    <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
@endsection

@section('content')
{{-- CSRF TOKEN --}}
<input type="hidden" name="_token" value="{{ csrf_token() }}">

<div>
    <div class="row">
	
		<div class="col-md-12">
            <div class="alert alert-warning alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <strong><i class="fa fa-info-circle"></i> Reminder:</strong> The system is displaying the latest update as of: <strong><u>{{$latest_mirror_delivery_date}}</u></strong>
            </div>
        </div>

        <div class="col-md-4">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Total confirmed delivery (20kg/bags)</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                            <div class="count"><i class="fa fa-truck"></i> {{number_format($confirmed->total_bag_count)}}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Total actual delivery (20kg/bags)</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                            <div class="count"><i class="fa fa-truck"></i> {{number_format($actual->total_bag_count)}}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Distributed (20kg/bags) - as of 12 MN</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                            <div class="count"><i class="fa fa-check-circle"></i> {{number_format($distributed->total_bags)}}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Total seed beneficiaries</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                            <div class="count"><i class="fa fa-users"></i> {{number_format($distributed->total_farmers)}}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Estimated area planted (ha)</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                            <div class="count"><i class="fa fa-map-marker"></i> {{number_format($distributed->total_actual_area,'2','.',',')}}</div>
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
                            <button class="btn btn-success" id="load_schedule_btn" style="margin:0">LOAD DELIVERIEIS</button>
                        </div>
                    </div>
                    
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left" id="delivery_sched_div">
                    @if($delivery_regions != "no_deliveries")
                        @foreach ($delivery_regions as $row)
                            <div class="card">
                                <div class="card-header" id="headingOne">
                                    <h5 class="mb-0" style="margin:0">
                                        <button style="color: #7387a8;text-decoration:none;" class="btn btn-link">
                                            {{$row->region}}
                                        </button>
                                        <a href="#" data-toggle="modal" data-target="#show_region_sched" data-region="{{$row->region}}" class="btn btn-warning btn-xs pull-right" style="margin-top: 9px;margin-right: 10px;"><i class="fa fa-eye"></i> View Deliveries</a>
                                    </h5>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="no_delivery_wrapper" style="width:100%;height:400px;background-color:#d8d8d8;">
                            <img src="{{asset('public/images/no_delivery.png')}}" alt="" style="display: block;margin: auto;height: 300px;padding-top: 25px;">
                            <p style="text-align: center;font-size: 26px;color:black;">No seed deliveries found for the selected dates...</p>
                        </div>
                    @endif

                    <!--<div style="width:100%;height:500px;background-color:#d8d8d8;">
                        <img src="{{asset('public/images/load_del.gif')}}" alt="" style="display: block;margin: auto;width: 100%;height:100%">
                    </div>-->
                </div>
            </div>
        </div>
    </div>


    <div id="show_region_sched" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg" style="width: 1300px; margin: auto; position: relative; top: 10%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="region_sched_title">
                        {REGION}
                    </h4>
                    <!--<span id="coop_accreditation_title">{COOP_ACCREDITATION}</span>-->
                </div>
                <div class="modal-body" style="max-height: 500px;overflow: auto;">
                    <table class="table table-bordered table-striped" id="delivery_sched_tbl">
                        <thead>
                            <th>Province</th>
                            <th>Municipality</th>
                            <th>Dropoff Point</th>
                            <th>Expected</th>
                            <th>Accepted</th>
                            <th>Date of delivery</th>
                            <th>Status</th>
                        </thead>
                    </table>        
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
    $("#date_of_delivery").daterangepicker(null,function(a,b,c){
        //console.log(a.toISOString(),b.toISOString(),c)
    });

    $("#load_schedule_btn").on("click", function(e){
        var date_duration = $("#date_of_delivery").val();
        $("#delivery_sched_div").empty();
        var delivery_div = '';

        delivery_div = delivery_div + '<div style="width:100%;height:400px;background-color:#d8d8d8;">';
        delivery_div = delivery_div + '<img src="{{asset('public/images/load_del.gif')}}" alt="" style="display: block;margin: auto;width: 100%;height:100%">';
        delivery_div = delivery_div + '</div>';
        $("#delivery_sched_div").append(delivery_div)

        $.ajax({
            type: 'POST',
            url: "{{ route('dashboard.delivery_schedule.search_regions') }}",
            data: {
                _token: "{{ csrf_token() }}",
                date_duration : date_duration,
            },
            success: function(data){
                delivery_div = '';
                $("#delivery_sched_div").empty();
                if(data == "no_deliveries"){
                    delivery_div = delivery_div + '<div class="no_delivery_wrapper" style="width:100%;height:400px;background-color:#d8d8d8;">';
                    delivery_div = delivery_div + '<img src="{{asset('public/images/no_delivery.png')}}" alt="" style="display: block;margin: auto;height: 300px;padding-top: 25px;">';
                    delivery_div = delivery_div + '<p style="text-align: center;font-size: 26px;color:black;">No seed deliveries found for the selected dates...</p>';
                    delivery_div = delivery_div + '</div>';
                    $("#delivery_sched_div").append(delivery_div)
                }else{
                    jQuery.each(data, function(index, array_value){
                        delivery_div = delivery_div + '<div class="card">';
                        delivery_div = delivery_div + '<div class="card-header" id="headingOne">';
                        delivery_div = delivery_div + '<h5 class="mb-0" style="margin:0">';
                        delivery_div = delivery_div + '<button style="color: #7387a8;text-decoration:none;" class="btn btn-link">';
                        delivery_div = delivery_div + array_value;
                        delivery_div = delivery_div + '</button>';
                        delivery_div = delivery_div + '<a href="#" data-toggle="modal" data-target="#show_region_sched" data-region="'+array_value+'" class="btn btn-warning btn-xs pull-right" style="margin-top: 9px;margin-right: 10px;"><i class="fa fa-eye"></i> View Deliveries</a>';
                        delivery_div = delivery_div + '</h5>';
                        delivery_div = delivery_div + '</div>';
                        delivery_div = delivery_div + '</div>';
                    });
                    $("#delivery_sched_div").append(delivery_div)
                }
            }
        });
    }); 

    $('#show_region_sched').on('show.bs.modal', function (e) {
        var date_duration = $("#date_of_delivery").val();
        var region = $(e.relatedTarget).data('region');

        $("#region_sched_title").empty().html("Seed Deliveries for the region of: "+region);

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
                    date_duration : date_duration,
                    region: region
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

    
</script>
@endpush
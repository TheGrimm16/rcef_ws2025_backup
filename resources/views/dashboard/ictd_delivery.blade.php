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
                    <h2>Select Location</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <select class="form-control" id="region_select" name="region_select" style="margin-bottom:10px;">
                        <option value="0">Please select a Region</option>
                        @foreach ($regions as $r_row)
                            <option value="{{$r_row->region}}">{{$r_row->region}}</option>
                        @endforeach
                    </select>

                    <select class="form-control" id="province_select" name="province_select" style="margin-bottom:10px;">
                        <option value="0">Please select a province</option>
                    </select>

                    <select class="form-control" id="municipality_select" name="municipality_select" style="margin-bottom:10px;">
                        <option value="0">Please select a municipality</option>
                    </select>

                    <button class="btn btn-success form-control" id="generate_delivery_btn"><i class="fa fa-bar-chart-o"></i> GENERATE DELIVERY DATA</button>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Delivery Summary</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered table-striped" id="delivery_summary_table">
                                <thead>
                                    <tr>
                                        <th style="width:250px; !important">Dropoff Point</th>
                                        <th>Expected (20kg/bag)</th>
                                        <th>Accepted (20kg/bag)</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
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

    $("#generate_delivery_btn").on("click", function(e){
        var region = $("#region_select").val();
        var province = $("#province_select").val();
        var municipality = $("#municipality_select").val();

        if(region != '' && region != '0' &&
           province != '' && province != '0' &&
           municipality != '' && municipality != '0'){
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
                    ]
                });
        }else{
            alert("Please select a region, province, and municipality");
        }
        
    });

    
</script>
@endpush
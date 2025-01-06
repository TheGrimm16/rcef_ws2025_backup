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
            <div class="x_panel">
                <div class="x_title">
                    <h2 style="margin-top: 10px;">FILTER</h2>                    
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left" id="delivery_sched_div">
                    <div class="row">
                        <div class="col-md-10" style="padding: 0">
                            <select name="province" id="province" class="form-control">
                                <option value="0">Please select a Province</option>
                                @foreach ($provinces as $p_row)
                                    <option value="{{$p_row->province}}">{{$p_row->province}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button id="filter_btn" class="btn btn-success btn-block" style="border-radius:20px;"><i class="fa fa-database"></i> FILTER DATA</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2 style="margin-top: 10px;">Seed beneficiaries (per municipality)</h2>                    
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left" id="municipal_list_div">
                    @foreach ($municipalities as $row)
                    @php 
                        $prv = $row->province;
                        $mun = $row->municipality;
                      
                    @endphp
                    
                        <div class="card" style="margin-top:0">
                            <div class="card-header" id="headingOne">
                                <h5 class="mb-0" style="margin:0">
                                    <button style="color: #7387a8;text-decoration:none;" class="btn btn-link">
                                        {{$row->province}} < {{$mun}}
                                    </button>
                                    <a href="#" data-toggle="modal" data-target="#municipal_distribution_modal" data-province="{{$prv}}" data-municipality="{{$mun}}" class="btn btn-warning btn-xs pull-right" style="margin-top: 9px;margin-right: 10px;"><i class="fa fa-eye"></i> View Beneficiaries</a>
                                    <a href="{{route('paymaya.report.municipal', ['province' => $prv, 'municipality' => $mun])}}" target="_blank" data-toggle="modal" class="btn btn-success btn-xs pull-right" style="margin-top: 9px;margin-right: 10px;"><i class="fa fa-table"></i> Export to Excel</a>
                                </h5>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>


    <div id="municipal_distribution_modal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg" style="width: 1200px; margin: auto; position: relative; top: 5%;">
            <div class="modal-content">
                <div class="modal-header">           
                    <!--<a id="export_mun_btn" href="#" target="_blank" data-toggle="modal" class="btn btn-success btn-sm pull-right" style="margin-top: 9px;margin-right: 10px;"><i class="fa fa-table"></i> Export to Excel</a>-->
                    <h4 class="modal-title" id="modal_title" style="font-size: 20px;font-weight: 600;margin-top: 10px;">
                        {PROVINCE NAME} < {MUNICIPALITY NAME}
                    </h4>
                </div>
                <form action="">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>Farmer Beneficiaries</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content form-horizontal form-label-left">
                                    <div class="row tile_count" style="margin: 0">
                                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                            <div class="count" id="total_farmers"><i class="fa fa-users"></i> --</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>Bags for Distribution (20kg/bag)</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content form-horizontal form-label-left">
                                    <div class="row tile_count" style="margin: 0">
                                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                            <div class="count" id="total_bags"><i class="fa fa-truck"></i> --</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>Area Planted (ha)</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content form-horizontal form-label-left">
                                    <div class="row tile_count" style="margin: 0">
                                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                            <div class="count" id="total_area"><i class="fa fa-map-marker"></i> --</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr style="border: 0.6px solid;opacity: 0.6;margin-top: 7px;">

                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered table-striped" id="municipal_tbl">
                                <thead>
                                    <th style="width:120px;">RSBSA #</th>
                                    <th>First Name</th>
                                    <th>Middle Name</th>
                                    <th>Last Name</th>
                                    <th>Ext. Name</th>
                                    <th>Baranggay</th>
                                    <th>Contact #</th>
                                    <th>Area</th>
                                    <th>Bags</th>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>

</div>

@endsection()

@push('scripts')
<script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
<script src=" {{ asset('public/js/select2.min.js') }} "></script>
<script src=" {{ asset('public/js/parsely.js') }} "></script>
<script src=" {{ asset('public/js/daterangepicker.js') }} "></script>
<script src=" {{ asset('public/js/highcharts.js') }} "></script>

<script>
    $("#municipal_tbl").DataTable();

    $("#filter_btn").on("click", function(e){
        var province = $("#province").val();
        if(province != "0"){
            $("#filter_btn").empty().html("loading...");
            $("#filter_btn").attr("disabled", "");

            $.ajax({
                type: 'POST',
                url: "{{ route('paymaya.seed_distribution.municipal_list') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    province: province
                },
                success: function(data){
                    municipal_div = '';
                    jQuery.each(data, function(index, array_value){

                        var url = "{{ route('paymaya.report.municipal', ['province' => ':province', 'municipality' => ':municipality']) }}";
                        url = url.replace(':province', province);
                        url = url.replace(':municipality', array_value);

                        municipal_div = municipal_div + '<div class="card" style="margin-top:0">';
                        municipal_div = municipal_div + '<div class="card-header" id="headingOne">';
                        municipal_div = municipal_div + '<h5 class="mb-0" style="margin:0">';
                        municipal_div = municipal_div + '<button style="color: #7387a8;text-decoration:none;" class="btn btn-link">';
                        municipal_div = municipal_div + $("#province").val() + ' < ' + array_value;
                        municipal_div = municipal_div + '</button>';
                        municipal_div = municipal_div + '<a href="#" data-toggle="modal" data-target="#municipal_distribution_modal" data-province="'+province+'" data-municipality="'+array_value+'" class="btn btn-warning btn-xs pull-right" style="margin-top: 9px;margin-right: 10px;"><i class="fa fa-eye"></i> View Seed beneficiaries</a>';
                        municipal_div = municipal_div + '<a href="'+url+'" target="_blank" data-toggle="modal" class="btn btn-success btn-xs pull-right" style="margin-top: 9px;margin-right: 10px;"><i class="fa fa-table"></i> Export to Excel</a>'
                        municipal_div = municipal_div + '</h5>';
                        municipal_div = municipal_div + '</div>';
                        municipal_div = municipal_div + '</div>';
                    });
                    $("#municipal_list_div").empty().append(municipal_div);

                    $("#filter_btn").empty().html("<i class='fa fa-database'></i> FILTER DATA");
                    $("#filter_btn").removeAttr("disabled");
                },
                error: function(data){
                    alert("There was an error encountered while executing this action, please refresh the page & try again.");
                    $("#filter_btn").empty().html("<i class='fa fa-database'></i> FILTER DATA");
                    $("#filter_btn").removeAttr("disabled");
                }
            });
        }else{
            alert("Please select a province.");
        }
    });

    $("#municipal_distribution_modal").on("show.bs.modal", function(e){
        var province = $(e.relatedTarget).data('province');
        var municipality = $(e.relatedTarget).data('municipality');
        $("#modal_title").empty().html(province + " < " + municipality);

        $("#total_farmers").empty().html("Loading...");
        $("#total_bags").empty().html("Loading...");
        $("#total_area").empty().html("Loading...");

        var url = "{{ route('paymaya.report.municipal', ['province' => ':province', 'municipality' => ':municipality']) }}";
        url = url.replace(':province', province);
        url = url.replace(':municipality', municipality);
        
        $("#export_mun_btn").attr("href", url);

        $.ajax({
            type: 'POST',
            url: "{{ route('paymaya.seed_distribution.municipal_totals') }}",
            data: {
                _token: "{{ csrf_token() }}",
                province: province,
                municipality: municipality
            },
            success: function(data){
                $("#total_farmers").empty().html("<i class='fa fa-users'></i> "+data["total_farmers"]);
                $("#total_bags").empty().html("<i class='fa fa-truck'></i> "+data["total_bags"]);
                $("#total_area").empty().html("<i class='fa fa-map-marker'></i> "+data["total_area"]);
            },
            error: function(data){
                $("#total_farmers").empty().html("<i class='fa fa-users'></i> --");
                $("#total_bags").empty().html("<i class='fa fa-truck'></i> --");
                $("#total_area").empty().html("<i class='fa fa-map-marker'></i> --");
            }

        }).done(function(e){
            $('#municipal_tbl').DataTable().clear();
            $("#municipal_tbl").DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('paymaya.seed_distribution.tbl_municipal') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        province: province,
                        municipality: municipality
                    }
                },
                "columns":[
                    {"data": "rsbsa_control_no"},
                    {"data": "firstname"},
                    {"data": "middname"},
                    {"data": "lastname"},
                    {"data": "extension_name", searchable: false},
                    {"data": "barangay"},
                    {"data": "contact_no"},
                    {"data": "area"},
                    {"data": "bags"},
                ]
            });
        });        
    });
</script>
@endpush
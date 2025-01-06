@extends('layouts.index')

@section('styles')
    <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
@endsection

@section('content')

<input type="hidden" name="_token" value="{{ csrf_token() }}">

<div>

        <div class="x_panel">
            <div class="x_title">
                <h2>
        
                    Filters
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <div class="row">
                    <div class="col-md-5">
                        <select name="region" id="region" class="form-control">
                            <option value="0" selected>Please select Region</option>
                            @foreach ($regions as $row)
                                <option value="{{$row->region}}">{{$row->region}}</option>    
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <select name="province_select" id="province_select" class="form-control province_select">
                            <option value="0">Please select a Province</option>
                        </select>
                    </div>  

                    <div class="col-md-2">
                        <button class="btn btn-success btn-block" id="filter_btn1"><i class="fa fa-database"></i> FILTER TABLE</button>
                    </div>
                </div>
            </div>
        </div><br>

        <div class="row">

        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2 style="margin-top: 10px;">Replacement Request (Municipality)</h2>                    
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left" id="municipal_list_div">
                    @foreach ($datas as $row)
                        <div class="card" style="margin-top:0">
                            <div class="card-header" id="headingOne">
                                <h5 class="mb-0" style="margin:0">
                                    <button style="color: #7387a8;text-decoration:none;" class="btn btn-link">
                                    <label class="pull-left">{{$row->region}} < {{$row->province}} < {{$row->municipality}}</label><br>
                                    <p class="pull-left">Open for replacement</p><br><br>
                                    <p class="pull-left">Due to: </p>
                                        @if($row->replacement_reason == 0)
                                        <p class="pull-left">&nbsp;Typhoon</p>
                                        @elseif($row->replacement_reason == 1)
                                        <p class="pull-left">&nbsp;Typhoon</p>
                                        @elseif($row->replacement_reason == 2)
                                        <p class="pull-left">&nbsp;Pest Infestations</p> 
                                        @elseif($row->replacement_reason == 3)
                                        <p class="pull-left">&nbsp;Volcanic Eruptions</p>
                                        @elseif($row->replacement_reason == 4)
                                        <p class="pull-left">&nbsp;Earthquake</p>
                                        @elseif($row->replacement_reason == 5)
                                        <p class="pull-left">&nbsp;Storm Surge</p>
                                        @elseif($row->replacement_reason == 6)
                                        <p class="pull-left">&nbsp;Prolonged Drought</p>
                                        @endif
                                    
                                    </button>
                                    @if($row->status == 1)
                                        <button href="#" data-province="{{$row->province}}" data-municipality={{$row->municipality}} class="btn btn-success btn-xs pull-right" style="margin-top: 9px;margin-right: 10px; size: 10px;" onclick='OpenModal_2({{$row->id}});'><i class="fa fa-check">&nbsp;&nbsp;</i> Approved&nbsp;&nbsp;&nbsp;&nbsp;</button>
                                    @elseif($row->status == 2)
                                         <button href="#" data-province="{{$row->province}}" data-municipality={{$row->municipality}} class="btn btn-warning btn-xs pull-right" style="margin-top: 9px;margin-right: 10px;" onclick='OpenModal({{$row->id}});'><i class="fa fa-pencil"></i> Allow/Decline</button>
                                    @elseif($row->status == 3)
                                        <button href="#" data-province="{{$row->province}}" data-municipality={{$row->municipality}} class="btn btn-danger btn-xs pull-right" style="margin-top: 9px;margin-right: 10px;" onclick='OpenModal_3({{$row->id}});'><i class="fa fa-ban">&nbsp;&nbsp;&nbsp;&nbsp;</i> Declined&nbsp;&nbsp;&nbsp;&nbsp;</button>
                                    @endif
                           
                                </h5>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div id="confirm_modal" class="modal fade " role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title" id="confirmStock_modal_title">Open For Replacement</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id_val" value="">
                    <div class="form-group">
                        You are about to Open Municipality for Seed Repalcement. Please be reminded that by opening these area
                        the changes will be immediately reflect to the mobile app being used.<br><br>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="btn_decline"><i class="fa fa-ban"></i> Decline</button>
                    <button type="button" class="btn btn-success" id="btn_confirm"><i class="fa fa-check"></i> Allow</button>
                    
                </div>
            </div>
        </div>
    </div>

    <div id="already_modal" class="modal fade " role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                    
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id_val" value="">
                    <div class="form-group">
                        <h4 class="modal-title" id="confirmStock_modal_title">This Municipality Already Open For Replacement</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="declined_modal" class="modal fade " role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id_val" value="">
                    <div class="form-group">
                        <h4 class="modal-title" id="confirmStock_modal_title">This Municipality Already Declined For Replacement</h4>
                    </div>
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

    $("#filter_btn1").on("click", function(e){
        var region = $("#region").val();
        var province = $("#province_select").val();

        if(region != "0"){
            $("#filter_btn").empty().html("loading...");
            $("#filter_btn").attr("disabled", "");

            $.ajax({
                type: 'POST',
                url: "{{ route('approve.replacement.filter') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    region: region, 
                    province: province
                },
                success: function(data){    
                    municipal_div = '';

                    jQuery.each(data, function(index, array_value){
                        console.log(array_value["replacement_reason"]);
             

                        municipal_div = municipal_div + '<div class="card" style="margin-top:0">';
                        municipal_div = municipal_div + '<div class="card-header" id="headingOne">';
                        municipal_div = municipal_div + '<h5 class="mb-0" style="margin:0">';
                        municipal_div = municipal_div + '<button style="color: #7387a8;text-decoration:none;" class="btn btn-link">';
                        municipal_div = municipal_div + '<label class="pull-left">'+array_value["region"]+' < '+array_value["province"]+'< '+array_value["municipality"]+'</label><br>';
                        municipal_div = municipal_div + '<p class="pull-left">Open for replacement</p><br><br>';
                        municipal_div = municipal_div + '<p class="pull-left">Due to: </p>';
                        if(array_value["replacement_reason"] == 1){
                          municipal_div = municipal_div + '<p class="pull-left">&nbsp;Typhoon</p>';
                        }else if (array_value["replacement_reason"] == 2) {
                          municipal_div = municipal_div + '<p class="pull-left">&nbsp;Try Crop</p>';
                        }else if (array_value["replacement_reason"] == 3) {
                          municipal_div = municipal_div + '<p class="pull-left">&nbsp;Others</p>';
                        }else{
                            municipal_div = municipal_div + '<p class="pull-left">&nbsp;Others</p>';
                        }
                        municipal_div = municipal_div + '</button>';
                        if(array_value["status"] == 1){
                         municipal_div = municipal_div + '<button href="#" class="btn btn-success btn-xs pull-right" style="margin-top: 9px;margin-right: 10px;" onclick="OpenModal_2('+array_value["id"]+');"><i class="fa fa-check"></i> Approved</button>';
                        }else if(array_value["status"] == 2){     
                         municipal_div = municipal_div + '<button href="#" class="btn btn-warning btn-xs pull-right" style="margin-top: 9px;margin-right: 10px;" onclick="OpenModal('+array_value["id"]+');"><i class="fa fa-pencil"></i> Allow/Decline</button>';
                        }else if(array_value["status"] == 3){
                            municipal_div = municipal_div + '<button href="#" class="btn btn-danger btn-xs pull-right" style="margin-top: 9px;margin-right: 10px;" onclick="OpenModal_3('+array_value["id"]+');"><i class="fa fa-ban"></i> Declined</button>';  
                        }
                        
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
            alert("Please select a region.");
        }
    });

    $("#region").on("change", function(e){
            var region = $("#region").val();
            $("#province_select").empty().append("<option value='0'>Loading provinces...</option>");
            $("#municipality").empty().append("<option value='0'>Please select a municipality</option>");

            $.ajax({
                type: 'POST',
                url: "{{ route('approve.replacement.provinces') }}",
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

        $("#btn_confirm").on("click", function(e){ 
             HoldOn.open(holdon_options) 
            var id_val = $("#id_val").val();
            var region = $("#region").val();

            $.ajax({
                type: 'POST',
                url: "{{ route('approve.replacement.status') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    id_val:id_val
      
                },
                success: function(data){
                    alert('Status Successfully Updated');
                    
                    if(region == 0){
                        location.reload();
                    }else{
                        $("#filter_btn1").trigger("click");
                    }
                    $('#confirm_modal').modal('toggle');
                    
                    HoldOn.close()
                    
                }
            });
        });

        $("#btn_decline").on("click", function(e){ 
             HoldOn.open(holdon_options) 
            var id_val = $("#id_val").val();
            var region = $("#region").val();

            $.ajax({
                type: 'POST',
                url: "{{ route('decline.replacement.status') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    id_val:id_val
      
                },
                success: function(data){
                    alert('Status Successfully Updated');
                    
                    if(region == 0){
                        location.reload();
                    }else{
                        $("#filter_btn1").trigger("click");
                    }
                    $('#confirm_modal').modal('toggle');
                    
                    HoldOn.close()
                    
                }
            });
        });

          // OpenModal('request');
          function OpenModal(id_val){
            var id_val = id_val;            
            $('#id_val').val(id_val);
            $('#confirm_modal').modal('show');    
        }

          // OpenModal('request');
          function OpenModal_2(id_val){
            $('#already_modal').modal('show');    
        }


        function OpenModal_3(id_val){
            $('#declined_modal').modal('show');    
        }

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
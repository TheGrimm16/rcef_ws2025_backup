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

    <div class="col-md-12 col-sm-12 col-xs-12">

        <!-- <div class="row">
            <div class="alert alert-warning alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <strong><i class="fa fa-info-circle"></i> Notice!</strong> This is a scheduled report, please note that the values displayed based on live data are <b><u>updated everyday @ 12 MN</u></b> to eliminate or minimize loading time. <br>
            
            </div>
        </div> -->

        <!-- FILTER PANEL -->
        <div class="x_panel">
            <div class="x_title">
                <h2>
                    1). Select a Province
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <div class="row">
                    <div class="col-md-8">
                        <select name="province_2" id="province" class="form-control">
                            <option value="0">Please select a province</option>
                            @foreach ($municipal_list as $row)
                                <option value="{{$row->province}}">{{$row->province}}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- <div class="col-md-2">
                    <button class="btn btn-success form-control" id="load_report_btn"><i class="fa fa-cloud-download" aria-hidden="true"></i> LOAD PROCESSED DATA</button>
                    </div>  -->

                    <div class="col-md-4">
                       
                        <!-- <button class="btn btn-success form-control" id="load_report_btn_live"><i class="fa fa-circle-o" aria-hidden="true"></i> LOAD LIVE DATA OLD</button> -->
                        <button class="btn btn-success form-control" id="load_report_btn_live_py"><i class="fa fa-circle-o" aria-hidden="true"></i> LOAD LIVE DATA</button>

                        

                   {{-- <button class="btn btn-success form-control" id="load_report_btn_without"><i class="fa fa-database"></i> LOAD WITHOUT EBINHI DATA </button>  --}}
                    </div>
                </div>
            </div>
        </div><br>
        <!-- FILTER PANEL -->

        <!-- distribution details -->
        <div class="x_panel">
            <div class="x_title">
                <h2>
                    2). Municipal Report
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <table class="table table-hover table-striped table-bordered" id="mun_tbl">
                    <thead>
                        <th>Municipality</th>
                        <th>Accepted & Transferred</th>
                        <th style="width: 200px;">Bags Distributed (20kg/bag)</th>
                        <th>Total Beneficiaries</th>   
                        <th style="width: 100px;" >Male</th>
                        <th style="width: 100px;" >Female</th>
                        <th>Actual Area (ha)</th> 
                        <th>Yield (T/ha)</th>
                        <th>Area Claimed (ha)</th> 
                        <th>Action</th>

                    </thead>
                </table>
            </div>
        </div><br>
        <!-- /distribution details -->
    </div>

    <div id="confirm_export_municipality" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="province_export_title">
                        EXPORT OPTION [USER LEVEL - {{strtoupper($user_level)}}]
                    </h4>
                </div>
                <div class="modal-body" style="max-height: 500px;overflow: auto;">
                    <input type="hidden" id="munReport_province" value="">
                    <input type="hidden" id="munReport_municipality" value="">

                    <p>
                        @if ($user_level == "rcef-pmo")
                            The system detected that you are now using an `rcef-pmo` account, this allows you to upate the seed beneficiary list whilst performing the export functionality, 
                            but please bear in mind that updating the list takes time: (depending on the current updates of the selected municipality).
                        @else
                            You are now about to export the data of the selected municipality, please wait patiently... 
                        @endif
                    </p>  
                </div>
                <div class="modal-footer">
                 
                        <button id="noUpdate_export_btn_py" type="button" class="btn btn-success"> EXPORT CONVENTIONAL EXCEL</button>
                        <!-- <button id="noUpdate_export_btn" type="button" class="btn btn-success"> EXPORT CONVENTIONAL EXCEL</button> -->
                        <button id="noUpdate_export_btn_ebinhi" type="button" class="btn btn-success">PROCEED E-BINHI EXCEL </button>
                        
                </div>
            </div>
        </div>
    </div>

	
		
    <!-- BREAKDOWN PREVIEW MODAL -->
<div id="show_breakdown_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
               <h4 class="modal-title">
                    <span>Transfer Bags Breakdown</span><br>
                </h4>
            </div>
            <div class="modal-body">
                <label for="" class="col-xs-3">Region:</label>
                <label id="modal_region"></label> <br>
                <label for="" class="col-xs-3">Province: </label>
                <label id="modal_province"></label> <br>
                <label for="" class="col-xs-3">Municipality: </label>
                <label id="modal_municipality"></label> <br>
                


        <div class="form-group">
                <div>
                    <br>
                     <table class="table table-hover table-striped table-bordered" id="stocks_tbl">
                        <thead>
                            <th width="3%">Code</th>
                            <th>Description</th>
                            <th>Volume</th>
                        </thead>
                        <tbody id="stocks_tbl_body">
                 
                        </tbody>

                    </table>
                </div>
        </div>  
        </div>
    </div>
</div>
<!-- BREAKDOWN PREVIEW MODAL -->
	
	
	
@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

    <script>
		

        $("#province").select2();
		 $("#stocks_tbl").DataTable({
            "order": [],
            "pageLength": 10
        });

         $('#show_breakdown_modal').on('show.bs.modal', function (e) {
             var region = $(e.relatedTarget).data('region');
            var province = $(e.relatedTarget).data('province');
            var municipality = $(e.relatedTarget).data('municipality');

             $("#modal_region").empty().html(region);
             $("#modal_province").empty().html(province);
             $("#modal_municipality").empty().html(municipality);

                $('#stocks_tbl').DataTable().clear();
                    $('#stocks_tbl').DataTable({
                        "bDestroy": true,
                        "autoWidth": false,
                        "searchHighlight": true,
                        "searching": true,
                        "processing": true,
                        "serverSide": true,
                        "orderMulti": true,
                        "order": [],
                        "pageLength": 10,
                        "ajax": {
                            "url": "{{route('genTable.report.break_down.modal')}}",
                            "dataType": "json",
                            "type": "POST",
                            "data":{
                                "_token": "{{ csrf_token() }}",
                                region: region,
                                province: province,
                                municipality: municipality
                            },
                            "dataSrc": function(res){

                                return res.data;
                            }
                        },
                        "columns":[
                            {"data": "code", 'orderable': false},
                             {"data": "description", 'searchable': true, 'orderable': true},
                            {"data": "volume", 'searchable': false, 'orderable': false},
                        ]
                    });

         });
         





	
	
	
         
	
        $("#mun_tbl").DataTable({
            "order": [],
            "pageLength": 25
        });


        $("#load_report_btn_live").on("click", function(e){
            var province = $("#province").val();

            $('#mun_tbl').DataTable().clear();
            $("#mun_tbl").DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "pageLength": 25,
                "ajax": {
                    "url": "{{ route('generate.live_municipal.report') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        province: province,
                        "ebinhi" : "true"
                    }
                },
                "columns":[
                    {"data": "municipality"},
                    {"data": "accepted_transferred"},
                    {"data": "total_bags_distributed"},
                    {"data": "total_beneficiaries"},
                    {"data": "total_male_count"},
                    {"data": "total_female_count"},
                    {"data": "total_registered_area"},
					{"data": "total_yield", "className": "text-right"},
                    {"data": "total_area_claimed"},
                    
                    {"data": "action", 'searchable': false }
                ]
            });
        });
        $("#load_report_btn_live_py").on("click", function(e){
            var province = $("#province").val();

            $('#mun_tbl').DataTable().clear();
            $("#mun_tbl").DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "pageLength": 25,
                "ajax": {
                    "url": "{{ route('generate.live_municipal_py.report') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        province: province,
                        "ebinhi" : "true"
                    }
                },
                "columns":[
                    {"data": "municipality"},
                    {"data": "accepted_transferred"},
                    {"data": "total_bags_distributed"},
                    {"data": "total_beneficiaries"},
                    {"data": "total_male_count"},
                    {"data": "total_female_count"},
                    {"data": "total_registered_area"},
					{"data": "total_yield", "className": "text-right"},
                    {"data": "total_area_claimed"},
                    
                    {"data": "action", 'searchable': false }
                ]
            });
        });


        $("#load_report_btn").on("click", function(e){
            var province = $("#province").val();

            $('#mun_tbl').DataTable().clear();
            $("#mun_tbl").DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "pageLength": 25,
                "ajax": {
                    "url": "{{ route('generate.municipal.report') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        province: province,
                        "ebinhi" : "true"
                    }
                },
                "columns":[
                    {"data": "municipality"},
                    {"data": "accepted_transferred"},
                    {"data": "total_bags_distributed"},
                    {"data": "total_beneficiaries"},
                    {"data": "total_male_count"},
                    {"data": "total_female_count"},
                    {"data": "total_registered_area"},
					{"data": "total_yield", "className": "text-right"},
                    {"data": "total_area_claimed"},
                    
                    {"data": "action", 'searchable': false }
                ]
            });
        });

    

        $("#load_report_btn_without").on("click", function(e){
            var province = $("#province").val();

            $('#mun_tbl').DataTable().clear();
            $("#mun_tbl").DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "pageLength": 25,
                "ajax": {
                    "url": "{{ route('generate.municipal.report') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        province: province,
                        "ebinhi" : "false"
                    }
                },
                "columns":[
                    {"data": "municipality"},
                    {"data": "accepted_transferred"},
                    {"data": "total_bags_distributed"},
                    {"data": "total_beneficiaries"},
                    {"data": "total_male_count"},
                    {"data": "total_female_count"},
                    {"data": "total_registered_area"},
					{"data": "total_yield", "className": "text-right"},
                    {"data": "total_area_claimed"},
                    
                    {"data": "action", 'searchable': false }
                ]
            });
        });

        $("#excel_btn").on("click", function(e){
            $("#excel_btn").empty().html('<i class="fa fa-cog fa-spin"></i> Exporting data to Excel...');
            $("#excel_btn").attr('disabled', '');

            $.ajax({
                type: 'POST',
                url: "{{ route('rcef.report.excel') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    excel_type: 'municipal'
                },
                success: function (response, textStatus, request) {
                    var a = document.createElement("a");
                    a.href = response.file; 
                    a.download = response.name;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();

                    $("#excel_btn").removeAttr('disabled');
                    $("#excel_btn").empty().html('Export to Excel (Statistics)');
                }
            });
        });

        $('#confirm_export_municipality').on('show.bs.modal', function (e) {
            
            var province = $(e.relatedTarget).data('province');
            var municipality = $(e.relatedTarget).data('municipality');
            var ebinhi = $(e.relatedTarget).data('ebinhi');

            if(ebinhi == 1){
                $("#noUpdate_export_btn_ebinhi").removeAttr("disabled");
               
            }else{
                $("#noUpdate_export_btn_ebinhi").removeAttr("disabled");
                // $("#noUpdate_export_btn_ebinhi").attr("disabled","");
            }

            $("#munReport_province").val(province);
            $("#munReport_municipality").val(municipality);
            
        });

        $("#noUpdate_export_btn_ebinhi").on("click", function(e){
            var url = 'https://rcef-seed.philrice.gov.ph/rcef_ws2025/report/excel_ebinhi/'+$("#munReport_province").val()+'/'+$("#munReport_municipality").val()+'/no_update';
            var redirectWindow = window.open(url, '_blank');
            redirectWindow.location;
        });
        

/*         $("#noUpdate_export_btn").on("click", function(e){
            var url = 'https://rcef-seed.philrice.gov.ph/rcef_ws2025/report/excel/'+$("#munReport_province").val()+'/'+$("#munReport_municipality").val()+'/no_update';
            // var url = 'http://localhost/rcef_ws2025/report/excel/'+$("#munReport_province").val()+'/'+$("#munReport_municipality").val()+'/no_update';
            var redirectWindow = window.open(url, '_blank');
            redirectWindow.location;
        }); */


        $("#noUpdate_export_btn_py").click(function() {

            $selected_prv = $("#munReport_province").val();
            console.log($selected_prv+'qweqweqweq');
            $selected_mun = $("#munReport_municipality").val();
            console.log($selected_mun+'qweqwe');
            $("#noUpdate_export_btn_py").text("Processing...");
            $("#noUpdate_export_btn_py").attr("disabled", true);
            $.ajax({
                type: 'POST',
                url: "{{ route('export_muni_noUpdate_pyCsv') }}", 
                data: {
                    _token: "{{ csrf_token() }}",
                    prv: $selected_prv,
                    mun: $selected_mun
                },
                success: function(data){
                    //console.log(data.message);
                    console.log(window.location.hostname+'/'+data.output);
                    window.open(data.output, '_blank');
                    $(".notification_toast").addClass("notif_show");
                    $("#noUpdate_export_btn_py").removeAttr("disabled");
                    $("#noUpdate_export_btn_py").text("EXPORT CONVENTIONAL EXCEL USING PYTHON");
                    setTimeout(() => {
                        $.ajax({
                        type: 'GET',
                        url: "{{ route('py_unlinking') }}", 
                        data: {
                            _token: "{{ csrf_token() }}",
                            uri: /* './public/public/'+ */data.output
                        },
                        success: function(data){
                            // console.log(data);
                        }
                    });
                    setTimeout(() => {
                        $(".notification_toast").removeClass("notif_show");
                    }, 2000);
                    }, 1000);
                }
            });
        });

        $("#update_export_btn").on("click", function(e){
            var url = 'https://rcef-seed.philrice.gov.ph/rcef_ws2025/report/excel/'+$("#munReport_province").val()+'/'+$("#munReport_municipality").val()+'/with_update';
            var redirectWindow = window.open(url, '_blank');
            redirectWindow.location;
        });
    </script>
@endpush

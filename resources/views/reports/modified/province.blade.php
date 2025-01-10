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

        <div class="row">
            <div class="alert alert-warning alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <strong><i class="fa fa-info-circle"></i> Notice!</strong> This is a scheduled report, please note that the values displayed based on live data are <b><u>updated everyday @ 12 MN</u></b> to eliminate or minimize loading time.
            </div>
        </div>

        <!-- FILTER PANEL -->
        <div class="x_panel">
            <div class="x_title">
                <h2>
                    1). Select a Region
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <div class="row">
                    <div class="col-md-8">
                        <select name="region" id="region" class="form-control">
                            <option value="0">Please select a region</option>
                            @foreach ($region_list as $row)
                                <option value="{{$row->region}}">{{$row->region}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <button class="btn btn-success form-control" id="load_report_btn"><i class="fa fa-database"></i> LOAD PROVINCIAL REPORT</button>
                    </div>
                </div>
            </div>
        </div><br>
        <!-- FILTER PANEL -->

        <!-- distribution details -->
        <div class="x_panel">
            <div class="x_title">
                <h2>
                    2). Provincial Report
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <table class="table table-hover table-striped table-bordered" id="province_tbl">
                    <thead>
                        <th>Province</th>
                        <th>Accepted & Transferred</th>
                        <th>Bags Distributed (20kg/bag)</th>
                        <th>Total Beneficiaries</th>
                        <th  style="width: 100px;">Male</th>
                        <th  style="width: 100px;">Female</th>
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


    <!-- SEED COOPERATIVE MODAL -->
    <div id="confirm_export_pmo" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="province_export_title">
                        EXPORT OPTION [USER LEVEL - {{strtoupper($user_level)}}]
                    </h4>
                </div>
                <div class="modal-body" style="max-height: 500px;overflow: auto;">
                    <input type="hidden" id="pmo_province" value="">
                    <p>
                        @if ($user_level == "rcef-pmo")
                            The system detected that you are now using an `rcef-pmo` account, this allows you to upate the seed beneficiary list whilst performing the export functionality, 
                            but please bear in mind that updating the list takes time: (depending on the current updates of the selected province / municipality).
                        @else
                            You are now about to export the data of the selected province, please wait patiently... 
                        @endif
                    </p>  
                </div>
                <div class="modal-footer">
                    @if ($user_level == "rcef-pmo")
                        <button id="update_export_btn" type="button" class="btn btn-warning">UPDATE & EXPORT</button>
                        <button id="noUpdate_export_btn" type="button" class="btn btn-success">PROCEED TO EXPORT</button>
                    @else
                        <button id="noUpdate_export_btn" type="button" class="btn btn-success">PROCEED TO EXPORT</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- SEED COOPERATIVE MODAL -->

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
								   <!-- <label for="" class="col-xs-3">Municipality: </label>
									<label id="modal_municipality"></label> <br> -->
									


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
             //$("#modal_municipality").empty().html(municipality);

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
         
        $("#province_tbl").DataTable(); 
        $("#load_report_btn").on("click", function(e){
            var region = $("#region").val();

            $('#province_tbl').DataTable().clear();
            $("#province_tbl").DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('generate.province.report') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        region: region
                    }
                },
                "columns":[
                    {"data": "province"},
                    {"data": "accepted_transferred"},
                    {"data": "total_bags_distributed"},
                    {"data": "total_beneficiaries"},
                    {"data": "total_male_count"},
                    {"data": "total_female_count"},
                    {"data": "total_registered_area"},
					{"data": "total_yield", "className": "text-right"},
                    {"data": "total_area_claimed"},
                    {"data": "action"}
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
                    excel_type: 'provincial'
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

        $('#confirm_export_pmo').on('show.bs.modal', function (e) {
            var pmo_province = $(e.relatedTarget).data('province');
            $("#pmo_province").val(pmo_province);
        });

        $("#noUpdate_export_btn").on("click", function(e){
            var url = 'https://rcef-seed.philrice.gov.ph/rcef_ws2025/report/excel/'+$("#pmo_province").val()+'/no_update';
            var redirectWindow = window.open(url, '_blank');
            redirectWindow.location;
        });

        $("#update_export_btn").on("click", function(e){
            var url = 'https://rcef-seed.philrice.gov.ph/rcef_ws2025/report/excel/'+$("#pmo_province").val()+'/with_update';
            var redirectWindow = window.open(url, '_blank');
            redirectWindow.location;
        });
    </script>
@endpush

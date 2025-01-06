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

        <div class="alert alert-warning alert-dismissible fade in" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <strong><i class="fa fa-info-circle"></i> Notice!</strong> This is a scheduled report, please note that the values displayed based on live data are <b><u>updated everyday @ 12 MN</u></b> to eliminate or minimize loading time.
        </div>

        <!-- distribution details -->
        <div class="x_panel">
        <div class="x_title">
            <h2>
                Regional Report
            </h2>
            <!--<button class="btn btn-success btn-sm" style="float:right;" id="excel_btn">
                Export to Excel (Statistics)
            </button>-->
            <div class="clearfix"></div>
        </div>
        <div class="x_content form-horizontal form-label-left">
            <table class="table table-hover table-striped table-bordered" id="region_tbl">
                <thead>
                    <th>Region</th>
                    <th>Accepted & Transferred</th>
                    <th>Bags Distributed (20kg/bag)</th>  
                    <th>Total Beneficiaries</th>
                    <th>Male</th>
                    <th>Female</th>
                    <th>Actual Area (ha)</th>
					
                    <th>Claimed_area</th>
                    <th>Yield / ha</th>
                </thead>
                <tbody>
                    @foreach($regional_data as $region)
                        <tr>
                       
                            <td>{{$region['region']}}</td>
                            <td>Regular distribution: {{number_format($region['total_actual'])}}
                                @if($region['ebinhi']> 0)
                                      <br>Binhi e-padala: {{number_format($region['ebinhi'])}}
                                    
                                    

                                @endif


                                @if($region['total_transferred']> 0)
                                    <br>Transferred: {{number_format($region['total_transferred'])}}

                                   

                                @endif

                            </td>
                          
                            <td> {{number_format($region['total_bags'])}}</td>

                            <td align="right">{{number_format($region['total_farmers'])}}</td>
                            <td align="right" ><?php echo number_format($region['total_male']); ?></td>
                            <td align="right" ><?php echo number_format($region['total_female']); ?></td>
                            <td align="right">{{number_format($region['total_actual_area'])}}</td>
                           



                            @if(Auth::user()->roles->first()->name == "da-icts")
                                <td>N/A</td>
                            @else
                                <td align="right">
                                    {{number_format($region['total_claimed_area'])}}

                                </td>
                            @endif
                            <td align="right">{{number_format($region['yield'],'2','.',',')}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

                
                
        </div>
        </div><br>
        <!-- /distribution details -->
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
						   <!-- <label for="" class="col-xs-3">Province: </label>
							<label id="modal_province"></label> <br>
							<label for="" class="col-xs-3">Municipality: </label>
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
             //$("#modal_province").empty().html(province);
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
	
	
	
	
	
	
        $("#region_tbl").DataTable({
            "order": [],
            "pageLength": 50
        });

        $("#excel_btn").on("click", function(e){
            $("#excel_btn").empty().html('<i class="fa fa-cog fa-spin"></i> Exporting data to Excel...');
            $("#excel_btn").attr('disabled', '');

            $.ajax({
                type: 'POST',
                url: "{{ route('rcef.report.excel') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    excel_type: 'regional'
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
    </script>
@endpush

@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <style>
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
	.card-header {
		margin-bottom: 0;
		background-color: #ffffff;
		border-bottom: 1px solid rgb(90 90 90 / 13%);
	}
	.select2-container--default .select2-selection--single .select2-selection__rendered {
		color: #c1c1c1;
		line-height: 28px;
	}
  </style>
@endsection

@section('content')

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="col-md-12 col-sm-12 col-xs-12">			
		<div class="row">
            <div class="col-md-7">
                <div class="x_panel">
                    <div class="x_title">
                        <h2 style="margin-top: 10px;">
							SG Enrollment Form
						</h2>
						<div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
						<div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2">Name:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10">
                                <input type="text" id="first_name" name="first_name" class="form-control" placeholder="Enter First Name" required/>
								<input type="text" id="middle_name" name="middle_name" class="form-control" placeholder="Enter Middle Name (optional)" style="margin-top:5px;"/>
								<input type="text" id="last_name" name="last_name" class="form-control" placeholder="Enter Last Name" style="margin-top:5px;" required/>
								<input type="text" id="extension_name" name="extension_name" class="form-control" placeholder="Enter Extension Name (optional)" style="margin-top:5px;"/>
							</div>
                        </div>
						<div class="form-group" style="margin-bottom:0;">
							<label class="control-label col-md-2 col-sm-2 col-xs-2">Accreditation #:</label>
							<div class="input-group">
								<div class="col-md-10 col-sm-10 col-xs-10">
									<span class="input-group-btn">
										<button id="coop_accreditation_prefix" type="button" class="btn btn-default" style="margin:0;" disabled readonly>{{substr_replace($tagged_accreditation,"",-5)}}</button>
									</span>
									<input type="text" id="sg_accreditation" name="sg_accreditation" class="form-control" style="position:absolute;top:0;left:100%;width: 475px;max-width: 475px;margin-left: 15px;" maxlength="5" onkeypress="return onlyNumberKey(event)" required/>
								</div>						
							</div>
                        </div>
						<div class="form-group" style="margin-bottom:0;">
							<label class="control-label col-md-2 col-sm-2 col-xs-2">Area (ha):</label>
							<div class="col-md-10 col-sm-10 col-xs-10">
								<input type="number" id="area" name="area" class="form-control"/>
							</div>						
                        </div><br>
						<div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"></label>
							<div class="col-md-10 col-sm-10 col-xs-10">
								<button id="enroll_btn" class="btn btn-success"><i class="fa fa-book"></i> Enroll Seed Grower</button>
							</div>                            
                        </div>
                    </div>
                </div>
            </div>
			<div class="col-md-5">
				<div class="x_panel">
					<div class="x_title">
						<h2>Total Enrolled Seed Growers</h2>
						<div class="clearfix"></div>
					</div>
					<div class="x_content form-horizontal form-label-left">
						<div class="row tile_count" style="margin: 0">
							<div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
								<div class="count" id="total_sg_enrolled"><i class="fa fa-users"></i> {{$total_sg_enrolled}}</div>
							</div>
						</div>
					</div>
				</div><br>
				<div class="x_panel">
					<div class="x_title">
						<h2>Total Area Allocated for RCEF</h2>
						<div class="clearfix"></div>
					</div>
					<div class="x_content form-horizontal form-label-left">
						<div class="row tile_count" style="margin: 0">
							<div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
								<div class="count" id="total_sg_area"><i class="fa fa-map-marker"></i> {{$total_sg_area}}</div>
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
						<h2>Participating Seed Growers</h2>
						<div class="clearfix"></div>
					</div>
					<div class="x_content form-horizontal form-label-left">
						<table class="table table-bordered table-striped" id="sg_tbl">
							<thead>
								<th>First Name</th>
								<th>Middle Name</th>
								<th>Last Name</th>
								<th>Extension Name</th>
								<th>SG Accreditation #</th>
								<th>Area (ha)</th>
								<th>Action</th>
							</thead>
						</table>
					</div>
				</div>
			</div>			
		</div>
    </div>
	
	
	<div id="edit_member_modal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Edit Member Details</h4>
                </div>
                <div class="modal-body form-horizontal form-label-left">
					<input type="hidden" class="form-control" id="member_id" name="member_id">
					<div class="form-group">
						<label class="control-label col-md-2 col-sm-2 col-xs-2">Name:</label>
						<div class="col-md-10 col-sm-10 col-xs-10">
							<input type="text" id="member_first_name" name="member_first_name" class="form-control" placeholder="Enter First Name" required/>
							<input type="text" id="member_middle_name" name="member_middle_name" class="form-control" placeholder="Enter Middle Name (optional)" style="margin-top:5px;"/>
							<input type="text" id="member_last_name" name="member_last_name" class="form-control" placeholder="Enter Last Name" style="margin-top:5px;" required/>
							<input type="text" id="member_extension_name" name="member_extension_name" class="form-control" placeholder="Enter Extension Name (optional)" style="margin-top:5px;"/>
						</div>
					</div>
					<div class="form-group" style="margin-bottom:0;">
						<label class="control-label col-md-2 col-sm-2 col-xs-2">Accreditation #:</label>
						<div class="input-group">
							<div class="col-md-10 col-sm-10 col-xs-10">
								<span class="input-group-btn">
									<button id="coop_accreditation_prefix" type="button" class="btn btn-default" style="margin:0;" disabled readonly>{{substr_replace($tagged_accreditation,"",-5)}}</button>
								</span>
								<input type="text" id="member_sg_accreditation" name="sg_accreditation" class="form-control" style="position:absolute;top:0;left:100%;width: 580px;max-width: 575px;margin-left: 15px;" maxlength="5" onkeypress="return onlyNumberKey(event)" required/>
							</div>						
						</div>
					</div>
					<div class="form-group" style="margin-bottom:0;">
						<label class="control-label col-md-2 col-sm-2 col-xs-2">Area (ha):</label>
						<div class="col-md-10 col-sm-10 col-xs-10">
							<input type="number" id="member_area" name="member_area" class="form-control"/>
						</div>						
					</div>
                </div>
				<div class="modal-footer">
					<button type="button" class="btn btn-warning" id="edit_sg_btn"><i class="fa fa-edit"></i> Edit SG Profile</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-trash"></i> Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
	<script>
		$(":input").inputmask();
		$("#seed_variety").select2();
		
		function onlyNumberKey(evt) { 			  
			// Only ASCII charactar in that range allowed 
			var ASCIICode = (evt.which) ? evt.which : evt.keyCode 
			if (ASCIICode > 31 && (ASCIICode < 48 || ASCIICode > 57)) 
				return false; 
			return true; 
		}
		
		refresh_sg_tbl();
		function refresh_sg_tbl(){
			$('#sg_tbl').DataTable().clear();
			$("#sg_tbl").DataTable({
				"bDestroy": true,
				"autoWidth": false,
				"searchHighlight": true,
				"processing": true,
				"serverSide": true,
				"orderMulti": true,
				"order": [],
				"ajax": {
					"url": "{{ route('coop_operator.sg_enrollment.tbl') }}",
					"dataType": "json",
					"type": "POST",
					"data":{
						"_token": "{{ csrf_token() }}",
						"coop_accreditation": "{{$tagged_accreditation}}"
					}
				},
				"columns":[
					{"data": "first_name"},
					{"data": "middle_name"},
					{"data": "last_name"},
					{"data": "extension_name"},
					{"data": "accreditation_number"},
					{"data": "area"},
					{"data": "action", searchable: false}
				]
			});
		}
		
		function refresh_enrolled_values(){
			$("#total_sg_enrolled").empty().append("updating..");
			$("#total_sg_area").empty().append("updating..");
					
			$.ajax({
				type: 'POST',
				url: "{{ route('coop_operator.sg_enrollment.summary') }}",
				data: {
					_token: "{{ csrf_token() }}",
				},
				success: function(data){
					$("#total_sg_enrolled").empty().append(data["total_sg_enrolled"]);
					$("#total_sg_area").empty().append(data["total_sg_area"]);
				}
			});
		}
		
		function clear_enrollement_fields(){
			$("#first_name").val("");
			$("#middle_name").val("");
			$("#last_name").val("");
			$("#extension_name").val("");
			$("#sg_accreditation").val("");
			$("#area").val("");
		}
		
		$("#enroll_btn").on("click", function(e){
			var first_name = $("#first_name").val();
			var middle_name = $("#middle_name").val();
			var last_name = $("#last_name").val();
			var extension_name = $("#extension_name").val();
			var sg_accreditation = $("#sg_accreditation").val();
			var coop_accreditation = "{{$tagged_accreditation}}";
			var area = $("#area").val();
			
			if(first_name != "" && last_name != "" && sg_accreditation != "" &&  area != ""){
				$.ajax({
					type: 'POST',
					url: "{{ route('coop_operator.sg_enrollment.save') }}",
					dataType: "json",
					data: {
						_token: "{{ csrf_token() }}",
						first_name: first_name,
						middle_name: middle_name,
						last_name: last_name,
						extension_name: extension_name,
						sg_accreditation: sg_accreditation,
						coop_accreditation: coop_accreditation,
						area: area
					},
					success: function(data){
						console.log(data);
						if(data == "accreditation_no_exists"){
							alert("The specified accreditation number is already saved in the database, this transaction will not proceed, please input the correct accreditation for the seed grower and try again..");
						}else if(data == "insert_success"){
							alert("You have successfully enrolled the seed grower to the seed cooperative!");
							clear_enrollement_fields();
							refresh_sg_tbl();
							refresh_enrolled_values();
						}
					}
				});
			}else{
				alert("Please fill-up the required fields!");
			}
		});
		
		function deleteSG(coop_member_id){
			$.ajax({
				type: 'POST',
				url: "{{ route('coop_operator.sg_enrollment.delete') }}",
				data: {
					_token: "{{ csrf_token() }}",
					coop_member_id: coop_member_id
				},
				success: function(data){
					refresh_sg_tbl();
					refresh_enrolled_values();
				}
			});
		}
		
		$('#edit_member_modal').on('show.bs.modal', function (e) {
			var coop_member_id = $(e.relatedTarget).data('id');
        
            $.ajax({
                type: 'POST',
                url: "{{ route('coop_operator.sg_enrollment.edit_show') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    coop_member_id: coop_member_id
                },
                success: function(data){
					$("#member_first_name").empty().val(data["first_name"]);
					$("#member_middle_name").empty().val(data["middle_name"]);
					$("#member_last_name").empty().val(data["last_name"]);
					$("#member_extension_name").empty().val(data["extension_name"]);
					$("#member_sg_accreditation").empty().val(data["accreditation_number"]);
					$("#member_area").empty().val(data["area"]);
					$("#member_id").val(coop_member_id);
                }
            });
		});
		
		$("#edit_sg_btn").on("click", function(e){
			var first_name = $("#member_first_name").val();
			var middle_name = $("#member_middle_name").val();
			var last_name = $("#member_last_name").val();
			var extension_name = $("#member_extension_name").val();
			var sg_accreditation = $("#member_sg_accreditation").val();
			var area = $("#member_area").val();
			var member_id = $("#member_id").val();
			
			if(first_name != "" && last_name != "" && sg_accreditation != "" &&  area != ""){
				$.ajax({
					type: 'POST',
					url: "{{ route('coop_operator.sg_enrollment.edit_confirm') }}",
					dataType: "json",
					data: {
						_token: "{{ csrf_token() }}",
						first_name: first_name,
						middle_name: middle_name,
						last_name: last_name,
						extension_name: extension_name,
						sg_accreditation: sg_accreditation,
						area: area,
						member_id: member_id
					},
					success: function(data){
						if(data == "update_success"){
							alert("You have successfully updated the seed grower profile, the values and tables of this page will perform a refresh after this message");
							$("#edit_member_modal").modal("hide");
							refresh_sg_tbl();
							refresh_enrolled_values();
						}else if(data == "update_failed"){
							alert("An unexpected error has occurred while performing the command, please hit refresh and try again.");
						}
					}
				});
			}else{
				alert("Please fill-up all required fields");
			}
		});
	</script>
@endpush

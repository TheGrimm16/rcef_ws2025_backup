@extends('layouts.index')

@section('content')
	<div>
		<div class="page-title">
            <div class="title_left">
              <h3>User Management</h3>
            </div>
        </div>

        <div class="clearfix"></div>

		<div class="row">
			<div class="col-md-12">
				@include('layouts.message')
				<div class="x_panel">
					<div class="x_title">
						<h2>Users</h2>
						<div class="clearfix"></div>
					</div>
					<div class="x_content">
						@if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->roles->first()->name == "branch-it")
							@permission('user-create')
							<a href="{{ route('users.create') }}" class="btn btn-success addBtn" style="margin-bottom: 20px;"><i class="fa fa-plus"></i> Add New User</a>
							@endpermission

						{{-- <a href="{{ route('users.create.request') }}" class="btn btn-success addBtn" style="margin-bottom: 20px;"><i class="fa fa-plus"></i> Request New User</a> --}}
						@endif

						@if(Auth::user()->roles->first()->name == "branch-it")

						<a href="{{ route('users.create.request') }}" class="btn btn-success addBtn" style="margin-bottom: 20px;"><i class="fa fa-plus"></i> Add New Branch User</a>
						@endif
					
						<table class="table table-striped table-bordered" id="usersTbl">
							<thead>
								<tr>
									<th style="width: 20%;">Name</th>
									<th style="width: 15%;">Username</th>
									<th style="width: 20%;">Email</th>
									<th style="width: 15%;">Province</th>
									<th style="width: 15%;">Municipality</th>
									<th style="width: 15%;">Roles</th>
									<th style="width: 10%;">Status</th>
									<th style="width: 20%;">Actions</th>
								</tr>
							</thead>
						</table>
					</div>
				</div>
			</div>
		</div>

		<div id="reset_password_modal" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<form action="{{ route('users.reset') }}" method="POST" data-parsley-validate>
				{!! csrf_field() !!}
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Reset Password</h4>
						</div>
						<div class="modal-body">
							<input type="text" class="form-control" name="reset_pass" id="reset_pass" value="P@ssw0rd" required>
							<button class="btn btn-info" style="margin-top:5px;" onclick="generateRandomPass(event)"><i class="fa fa-power-off"></i> GENERATE RANDOM PASSWORD (6-CHARACTERS)</button>
										
							</select>
							<input type="hidden" id="userID_reset" name="userID_reset" value="">
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							<input type="submit" role="submit" class="btn btn-success" value="RESET PASSWORD">
						</div>
					</div>
				</form>
			</div>
		</div>

		<div id="assignProvince" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<form action="{{ route('users.update.province') }}" method="POST" data-parsley-validate>
				{!! csrf_field() !!}
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title" id="">Change Tagged Address</h4>
						</div>
						<div class="modal-body">
							Select Province:
							<select name="changeProvince" id="changeProvince" class="form-control" style="width:100%;" required>
								
							</select>
							<input type="hidden" id="prv_userID" name="prv_userID" value="">

							Select Municipaliy:
							<select name="changeMunicipality" id="changeMunicipality" class="form-control" style="width:100%;" required>
								<option value="0">Select a Municipality</option>
							</select>
						</div>
						


						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							<input type="submit" role="submit" class="btn btn-success" value="Update Location">
						</div>
					</div>
				</form>
			</div>
		</div>


		<div id="changeInfo" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<form action="{{ route('users.update.info') }}" method="POST" data-parsley-validate>
					{!! csrf_field() !!}
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title" id="">Information Update</h4>
						</div>
						<div class="modal-body">
							<div class="row">
								<div class="col-md-3">First Name</div>
								<div class="col-md-9">
									<input type="text" name="firstName" id="firstName" class="form-control" placeholder="First Name">
								</div>
							</div>

							<div class="row">
								<div class="col-md-3">Middle Name</div>
								<div class="col-md-9">
									<input type="text" name="midName" id="midName" class="form-control" placeholder="Middle Name">
								</div>
							</div>

							<div class="row">
								<div class="col-md-3">Last Name</div>
								<div class="col-md-9">
									<input type="text" name="lastName" id="lastName" class="form-control" placeholder="Last Name">
								</div>
							</div>

							<div class="row">
								<div class="col-md-3">Extension Name</div>
								<div class="col-md-9">
									<input type="text" name="extName" id="extName" class="form-control" placeholder="Ext Name">
								</div>
							</div>

							
							
							
							<input type="hidden" id="info_userID" name="info_userID" value="">
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							<input type="submit" role="submit" class="btn btn-success" value="Update Information">
						</div>
					</div>
				</form>
			</div>
		</div>










		<div id="changeRole" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<form action="{{ route('users.update.role') }}" method="POST" data-parsley-validate>
					{!! csrf_field() !!}
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title" id="">Change Role</h4>
						</div>
						<div class="modal-body">
							
							<b>Current Role: </b><span id="currentRole"></span><br>
							Select Role:
							<select name="changeRoleSelect" id="changeRoleSelect" class="form-control" style="width:100%;" required>
								<option value="">Select Role</option>
								@foreach ($roles as $k => $r)
									<option value="{{$k}}">{{$r}}</option>
								@endforeach
							</select>
							
							<input type="hidden" id="role_userID" name="role_userID" value="">
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							<input type="submit" role="submit" class="btn btn-success" value="Change Role">
						</div>
					</div>
				</form>
			</div>
		</div>

		<div id="assignModal" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<form action="{{ route('users.assign.coopID') }}" method="POST" data-parsley-validate>
				{!! csrf_field() !!}
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title" id="SG_name"></h4>
						</div>
						<div class="modal-body">
							<select name="seed_coop" id="seed_coop" class="form-control" style="width:100%;" required>
								
							</select>
							<input type="hidden" id="userID" name="userID" value="">
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							<input type="submit" role="submit" class="btn btn-success" value="Save Accreditation Number">
						</div>
					</div>
				</form>
			</div>
		</div>

		<div id="update_accre_modal" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<form action="{{ route('users.update.coopID') }}" method="POST" data-parsley-validate>
				{!! csrf_field() !!}
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title" id="SG_name_update"></h4>
						</div>
						<div class="modal-body">
							<select name="seed_coop_update" id="seed_coop_update" class="form-control" style="width:100%;">
								
							</select>
							<input type="hidden" id="userID_update" name="userID_update" value="">
						</div>
						<div class="modal-footer">
							<input type="submit" role="submit" class="btn btn-default" value="Edit tagged Seed Cooperative">
						</div>
					</div>
				</form>
			</div>
		</div>

	</div>
@endsection

@push('scripts')
	<script>
		window.Laravel = {!! json_encode([
			'api_token' => $data['api_token'],
			'csrf_token' => csrf_token(),
			'tableRoute' => route('users.datatable')
		]) !!};
		

		function generateRandomPass(e) {
			e.preventDefault();
			chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			length = 6;

			var result = '';
			for (var i = length; i > 0; --i) result += chars[Math.floor(Math.random() * chars.length)];
			//return result.toUpperCase();

			$("#reset_pass").empty().val(result.toUpperCase());
		}

		$('#reset_password_modal').on('show.bs.modal', function (e) {
			var userID = $(e.relatedTarget).data('id');
			$("#userID_reset").empty().val(userID);
		});


		$('#assignProvince').on('show.bs.modal', function (e) {
			var userID = $(e.relatedTarget).data('id');
			
			$("#prv_userID").val(userID);
			
			$("#changeProvince").empty().append("<option value='0'>Loading library please wait...</option>");
			$.ajax({
				type: 'POST',
				url: "{{ route('user.province.list') }}",
				data: {
					_token: "{{ csrf_token() }}",
					userID: userID
				},
				success: function(data){
					$("#changeProvince").empty().append("<option value='0'>Please select a Province</option>");

					$("#changeProvince").append(data);


					$.ajax({
							type: 'POST',
							url: "{{ route('user.municipality.list') }}",
							data: {
								_token: "{{ csrf_token() }}",
								userID: userID,
								province: $("#changeProvince").val()
							},
							success: function(data){
								$("#changeMunicipality").empty();

								$("#changeMunicipality").append(data);
							}
						});

				}
			});
		});

		$("#changeProvince").on("change", function(){
			var province = $(this).val();

			$.ajax({
				type: 'POST',
				url: "{{ route('user.municipality.list') }}",
				data: {
					_token: "{{ csrf_token() }}",
					province: province,
					userID: $("#prv_userID").val()
				},
				success: function(data){
					$("#changeMunicipality").empty();

					$("#changeMunicipality").append(data);
				}
			});



		});


		$('#changeRole').on('show.bs.modal', function (e) {
			var userID = $(e.relatedTarget).data('id');
			
			$("#role_userID").val(userID);
			console.log(userID);
			
			$("#currentRole").empty().append($(e.relatedTarget).parent().siblings('td').find('span.label-primary').html());
			$("#changeProvince").empty().append("<option value='0'>Loading library please wait...</option>");
			$.ajax({
				type: 'POST',
				url: "{{ route('user.province.list') }}",
				data: {
					_token: "{{ csrf_token() }}",
					userID: userID
				},
				success: function(data){
					$("#changeProvince").empty().append("<option value='0'>Please select a Province</option>");
					$("#changeProvince").append(data);
				}
			});
		});


		$('#changeInfo').on('show.bs.modal', function (e) {
			var userID = $(e.relatedTarget).data('id');
			var lastName = $(e.relatedTarget).data('last_name');
			var firstName = $(e.relatedTarget).data('first_name');
			var midName = $(e.relatedTarget).data('mid_name');
			var extName = $(e.relatedTarget).data('ext_name');
			
			$("#info_userID").val(userID);
			$("#lastName").val(lastName);
			$("#firstName").val(firstName);
			$("#midName").val(midName);
			$("#extName").val(extName);
			
		});
		
		$('#assignModal').on('show.bs.modal', function (e) {
			var userID = $(e.relatedTarget).data('id');
			var name = $(e.relatedTarget).data('name');

			$("#userID").val(userID);
			$("#SG_name").html(name);

			$("#seed_coop").empty().append("<option value='0'>Loading library please wait...</option>");
			$.ajax({
				type: 'POST',
				url: "{{ route('coop.list') }}",
				data: {
					_token: "{{ csrf_token() }}",
					coop: 0
				},
				success: function(data){
					$("#seed_coop").empty().append("<option value='0'>Please select a seed cooperative</option>");
					$("#seed_coop").append(data);
				}
			});
		});
		
		$('#update_accre_modal').on('show.bs.modal', function (e) {
			var userID = $(e.relatedTarget).data('id');
			var name = $(e.relatedTarget).data('name');
			var coop = $(e.relatedTarget).data('coop');

			$("#userID_update").val(userID);
			$("#SG_name_update").html(name);
			$("#coopAccreditation_update").val(coop);

			$("#seed_coop_update").empty().append("<option value='0'>Loading library please wait...</option>");
			$.ajax({
				type: 'POST',
				url: "{{ route('coop.list') }}",
				data: {
					_token: "{{ csrf_token() }}",
					coop: coop
				},
				success: function(data){
					$("#seed_coop_update").empty().append(data);
				}
			});
        });
	</script>
@endpush

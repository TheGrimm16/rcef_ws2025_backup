@extends('layouts.index')

@section('content')
	<div>
		<div class="page-title">
            <div class="title_left">
              <h3>SED Users Management</h3>
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
						
						<button class="btn btn-success " style="margin-bottom: 20px;" id="addCallersBtn"><i class="fa fa-plus"></i> Add New User</button>
						
						<table class="table table-striped table-bordered" id="userTbl">
							<thead>
								<tr>
									<th style="width: auto">User Code</th>
									<th style="width: auto">Name</th>
									<th style="width: auto">Username</th>
									<th style="width: auto">Email</th>
									<th style="width: auto">Total Assigned</th>
									<th style="width: auto">Status</th>
									<th style="width: auto">Actions</th>
								</tr>
							</thead>
						</table>
					</div>
				</div>
			</div>
		</div>


        <div id="userModal" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<div class="modal-content">
					<div id="userModalContent"></div>
				</div>
			</div>
		</div>

        <div id="assignMunicipality" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<div class="modal-content">
					<div id="assignMunicipalityContent"></div>
				</div>
			</div>
		</div>
		

	</div>
@endsection

@push('scripts')
	<script>

		var userTbl = $('#userTbl').DataTable({
			"serverSide": true,
			"ajax": {
				"url": "{!! route('sed.users.datatable') !!}",
				"type": "get",
				"data": function(d) {
					d._token = "{{csrf_token()}}";
				}
			},
			columns: [{
					data: 'userId',
					name: 'userId'
				},
				{
					data: 'name',
					name: 'name'
				},
				{
					data: 'username',
					name: 'username'
				},
				{
					data: 'email',
					name: 'email'
				},
				{
					data: 'total_count',
					name: 'total_count',
					orderable: false,
					searchable: false
				},
				{
					data: 'status',
					name: 'status'
				},
				{
					data: 'actions',
					name: 'actions',
					orderable: false,
					searchable: false
				}
			]
		});

		$("#addCallersBtn").on('click', function () {
            $.ajax({
				type: "POST",
				url: "{{url('sed/users/form')}}",
				data: {
					_token: "{{csrf_token()}}"
				},
				success: function (response) {
					if(typeof response.error === 'undefined'){
						$("#userModalContent").html(response);
						$('#userModal').modal('show');
					}else{
						alert(response.message);
					}
					// usersTbl.ajax.reload( null, false );
                    // location.reload();
				}
			});
        });

        $('#assignMunicipality').on('show.bs.modal', function (e) {
			var userID = $(e.relatedTarget).data('id');
			$("#assignMunicipalityContent").html("loading.....");

			$.ajax({
				type: 'POST',
				url: "{{ route('sed.assign.municipality') }}",
				data: {
					_token: "{{ csrf_token() }}",
					userID: userID
				},
				success: function(response){
					$("#assignMunicipalityContent").html(response);
				}
			});
		});

		
        $('#userTbl tbody').on('click', 'tr td button.deleteUser', function(e) {
			var userID = $(this).data('id');
			let text = "Press OK to delete user";
			if (confirm(text) == true) {
				$.ajax({
					type: 'POST',
					url: "{{ route('sed.users.delete') }}",
					data: {
						_token: "{{ csrf_token() }}",
						id: userID
					},
					success: function(response){
						alert(response.message)
						userTbl.ajax.reload();
					}
				});
			} 
			
		});
        
		
		
	</script>
@endpush

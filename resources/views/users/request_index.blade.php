@extends('layouts.index')

@section('content')
	<div>
		<div class="page-title">
            <div class="title_left">
              <h3>User For Approval</h3>
            </div>
        </div>

        <div class="clearfix"></div>

		<div class="row">
			<div class="col-md-12">
				@include('layouts.message')
				<div class="x_panel">
					<div class="x_title">
						<h2>Users List</h2>
						<div class="clearfix"></div>
					</div>
					<div class="x_content">
						
						<table class="table table-striped table-bordered" id="usersTbl_request">
							<thead>
								<tr>
									<th style="width: 20%;">Name</th>
									<th style="width: 15%;">Username</th>
									<th style="width: 20%;">Station</th>
									<th style="width: 15%;">Roles</th>
									<th style="width: 10%;">Requested By</th>
									<th style="width: 20%;">Actions</th>
								</tr>
							</thead>
						</table>
					</div>
				</div>
			</div>
		</div>

		<div id="assignProvince" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<form action="{{ route('request.approve') }}" method="POST" data-parsley-validate>
				{!! csrf_field() !!}
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title" id="">Approve this user?</h4>
						</div>
						<div class="modal-body">
                            <label id="name"> Name: </label> <br>
                            <label id="username">Username: </label>
                            
							<input type="hidden" id="prv_userID" name="prv_userID" value="">
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							<input type="submit" role="submit" class="btn btn-success" value="Approve">
						</div>
					</div>
				</form>
			</div>
		</div>



	
@endsection

@push('scripts')
	<script>
		window.Laravel = {!! json_encode([
			'api_token' => $data['api_token'],
			'csrf_token' => csrf_token(),
		]) !!};

            generateTable();
        function generateTable(){
            
            
            $('#usersTbl_request').DataTable({
			processing: true,
			"bDestroy": true,
			"autoWidth": false,				
			serverSide: true,
			//ajax: "{!! route('palaysikatan.farmers.datatable') !!}",
			"ajax": {
                        "url": "{{ route('users.datatable.request') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            
                        }
                    },
			columns: [
                {"data": "name", 'orderable': false},
                {"data": "username", 'orderable': false},
                {"data": "station", 'searchable': false, 'orderable': true},
                {"data": "role", 'searchable': false, 'orderable': false},
                {"data": "requested", 'orderable': false},
                {"data": "action", 'searchable': false , 'orderable': false}  

			]
		});

       
       
       
       
        }
      

	


		$('#assignProvince').on('show.bs.modal', function (e) {
			var userID = $(e.relatedTarget).data('id');
			var username = $(e.relatedTarget).data('username');
			var name = $(e.relatedTarget).data('name');
	
            $("#prv_userID").val(userID);
            $("#name").empty().text("Name: "+name);
            $("#username").empty().text("Username: "+username);
	
            

		});
		

		
	
	</script>
@endpush

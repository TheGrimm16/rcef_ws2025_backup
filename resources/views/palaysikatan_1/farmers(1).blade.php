@extends('layouts.index')

@section('content')

<style type="text/css">
	
	.tbl{
		color: black;
	}

</style>


	<div>
		<div class="page-title">
            <div class="title_left tbl">
              <h3>Palaysikatan</h3>
            </div>
        </div>
		<div>
		@if (Session::has('success_farmer'))
        	<li>{!! session('success_farmer') !!}</li>
   		@endif
		</div>

        <div class="clearfix"></div>

		<div class="row">
			<div class="col-md-12">
				@include('layouts.message')
				<div class="x_panel">
					<div class="x_title tbl">
						<h2>Palaysikatan Farmers</h2>
						<div class="clearfix"></div>
					</div>
					<div class="x_content">
						<a href="{{url('palaysikatan/form/farmer')}}" class="btn btn-success addBtn" style="margin-bottom: 20px;"><i class="fa fa-plus"></i> New Farmer</a>
						
						<button class="btn btn-success btn-sm" onclick="export_excel();" style="float: right;">FDS Report</button>
						<button class="btn btn-success btn-sm" onclick="export_excel_table();" style="float: right;">SED x RCEF Report</button>


						<table class="table table-striped table-bordered tbl" id="farmersTbl">
							<thead>
								<tr>
									<th style="width: auto;">ID</th>
									<th style="width: auto;">Farmer Name</th>
									<th style="width: auto;">Respondent</th>
									<!-- <th style="width: auto;">Address</th> -->
									<th style="width: auto;">Age</th>
									<th style="width: auto;">Sex</th>
									<th style="width: auto;">Membership</th>
									<th style="width: auto;">Action</th>
								</tr>
							</thead>
						</table>
					</div>
				</div>
			</div>
		</div>
		

	</div>
@endsection

@push('scripts')
	<script>

		function export_excel(){

			window.open("{{url($export_link)}}");

		}

		function export_excel_table(){

			window.open("{{url($export_link_table)}}");

		}

		window.Laravel = {!! json_encode([
			'csrf_token' => csrf_token()
		]) !!};

		let farmersTbl = $('#farmersTbl').DataTable({
			processing: true,
			serverSide: true,
			ajax: "{!! route('palaysikatan.farmers.datatable') !!}",
			columns: [
				{data: 'farmer_id', name: 'farmer_id' },
				{data: 'f_full_name', name: 'f_full_name'},
				{data: 'r_full_name', name: 'r_full_name'},
				{data: 'age', name: 'age'},
				{data: 'sex', name: 'sex'},
				{data: 'org_membership', name: 'org_membership'},
				{data: 'actions', name: 'actions', orderable: false, searchable: false}
			]
		});
		
	</script>
@endpush

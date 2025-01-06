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
							
						<a href="{{url('palaysikatan/form/farmer')}}" class="btn btn-success addBtn col-md-2" style="margin-bottom: 20px;"><i class="fa fa-plus"></i> New Farmer</a>	
							
					
						<button class="btn btn-success btn-sm col-md-2" onclick="export_excel_npk();" style="float: right;">Baseline Matrix</button>
						<button class="btn btn-success btn-sm col-md-2 " onclick="export_excel();" style="float: right;">FDS Report</button>

						

						@if( Auth::user()->username == "justine.ragos" || Auth::user()->username == "tdo-dagabriel" || Auth::user()->username == "tdo-icuaresma" || Auth::user()->username == "rm.capiroso")
						<button class="btn btn-success btn-sm col-md-1 province_div" onclick="loadTable();" style="float: right; display: none;">Filter</button>
						<div class="col-md-2 province_div" id="province_div"   style="display: none; float: right;">                           
                            <select class="form-control" id="province_data" name="province_data">
                                <option value="">Filter Per Province </option>
                                
                            </select>
                        </div>

						<div class="col-md-2 "  style="float: right;">                           
                            <select class="form-control" id="station" name="station" >
                                <option value="">Filter Per Station</option>
                                <option value="0">All</option>

                                @foreach ($stations as $station)
                                    <option value="{{ $station->stationId }}">{{ $station->stationName }}</option>
                                @endforeach>
                            </select>
                        </div>
						@endif


						@if(Auth::user()->roles->first()->name == "techno_demo_officer"  && Auth::user()->username != "tdo-dagabriel" && Auth::user()->username != "tdo-icuaresma")
						<button class="btn btn-success btn-sm col-md-1 province_div" onclick="loadTable();" style="float: right; display: none;">Filter</button>
						<div class="col-md-2 province_div" id="province_div"   style="display: none; float: right;">                           
                            <select class="form-control" id="province_data" name="province_data">
                                <option value="">Filter Per Province </option>
                                
                            </select>
                        </div>

						<div class="col-md-2 "  style="float: right;">                           
                            <select class="form-control" id="station" name="station" >
                                <option value="">Filter Per Station</option>
                                @foreach ($stations as $station)
									@if (Auth::user()->stationId == $station->stationId)
									<option value="{{ $station->stationId }}">{{ $station->stationName }}</option>
									@endif                                 
                                @endforeach>
                            </select>
                        </div>
						@endif

						
						{{-- <button class="btn btn-success btn-sm" onclick="export_excel_table();" style="float: right;">SED x RCEF Report</button> --}}


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
									<th style="width: auto;">Area</th>
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

		$('#station').change(function(){
			if($(this).val() != ""){
				$('select[name="province_data"]').empty();
				$.ajax({
                type: 'POST',
                url: "{{ route('palaysikatan.farmers.get-station-province') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    station:$(this).val()
                },
                success: function(data){			
					$('select[name="province_data"]').append('<option alue="">Filter Per Province</option>');
					data.forEach(element => {                        
                        $('select[name="province_data"]').append('<option value="' + element
                            .province + '">' + element.province + '</option>');
                    });
					$('.province_div').show();


				  }				  

            });
			}else{
				$('.province_div').hide();
				
			}
		})
		loadData(1);
		function loadTable(){
			$('#farmersTbl').DataTable().clear();			
			loadData(2);
		}
		function deleteFca(id){
			
			var proceed = confirm("Are you sure you want to proceed?");
			if (proceed) {

			$.ajax({
                type: 'POST',
                url: "{{ route('palaysikatan.farmers.delete') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    fcaId: id
                },
                success: function(data){
                  if(data == "true" || data == true){
					$('#farmersTbl').DataTable().ajax.reload();
					//loadData();
				  }				  

                }
            });
			} else {
			  //don't proceed
			}
		
		}
		function export_excel_npk(){
			var station = $('#station').val();
			if(station==""){
				alert("Please Select Station");
			}else{
				window.open("export/report_matrix/"+station+"");
			}
			
		}

		function export_excel(){
			var station = $('#station').val();
			if(station==""){
				alert("Please Select Station");
			}else{
				window.open("export/report/"+station+"");
			}
		}

		function export_excel_table(){

			window.open("{{url($export_link_table)}}");

		}

		window.Laravel = {!! json_encode([
			'csrf_token' => csrf_token()
		]) !!};

		
		function loadData(state){
			console.log(state);
			if(state==1){

			let farmersTbl = $('#farmersTbl').DataTable({
			processing: true,
			"bDestroy": true,
			"autoWidth": false,				
			serverSide: true,
			ajax: "{!! route('palaysikatan.farmers.datatable') !!}",
			columns: [
				{data: 'farmer_id', name: 'farmer_id' },
				{data: 'f_full_name', name: 'f_full_name'},
				{data: 'r_full_name', name: 'r_full_name'},
				{data: 'age', name: 'age'},
				{data: 'sex', name: 'sex'},				
				{data: 'org_membership', searchable: false},
				{data: 'techno_area', searchable: false},
				{data: 'actions', name: 'actions', orderable: false, searchable: false}
			]
		});
			}else{
			var province = $('#province_data').val();
			if($('#station').val() == 0){
				province="%";
			}
			let farmersTbl = $('#farmersTbl').DataTable({
			processing: true,
			"bDestroy": true,
			"autoWidth": false,				
			serverSide: true,
			//ajax: "{!! route('palaysikatan.farmers.datatable') !!}",
			"ajax": {
                        "url": "{{ route('palaysikatan.farmers.datatable-filter') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            province: province,
                         
                        }
                    },
			columns: [
				{data: 'farmer_id', name: 'farmer_id' },
				{data: 'f_full_name', name: 'f_full_name'},
				{data: 'r_full_name', name: 'r_full_name'},
				{data: 'age', name: 'age'},
				{data: 'sex', name: 'sex'},				
				{data: 'org_membership',searchable: false},
				{data: 'techno_area',searchable: false},
				{data: 'actions', name: 'actions', orderable: false, searchable: false}
			]
		});
			}


		}
		
	</script>
@endpush

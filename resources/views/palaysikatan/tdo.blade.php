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
						<div class="clearfix">

							<button class="btn btn-success btn-sm col-md-1 pull-right" id="loadBtn" style="float: right;">Filter</button>
						<div class="col-md-3 pull-right">                           
                            <select class="form-control" id="station" name="station">
                                <option value="">Filter Per Station </option>
                                <option value="0">All</option>

                                @foreach ($stations as $station)
                                    <option value="{{ $station->stationId }}">{{ $station->stationName }}</option>
                                @endforeach>
                            </select>
                        </div>
						</div>
					</div>
					<div class="x_content">
							
						<div class="x_title">
							<h2>Station Status</h2>
							{{-- <button class="excel_btn" id="export_loa_status"><i class="fa fa-file-excel-o" aria-hidden="true"></i>&nbsp; EXPORT</button> --}}
							<div class="clearfix"></div>
		
		
						</div>
						<div class="x_content form-horizontal myTile">
							<div class="row tile_count" style="margin: 0">
								{{-- <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0; height: 300px;" id="loa_graph"></div> --}}
								<table class="table table-bordered table-striped" id="tbl_encoderStatus">
									<thead>
										<th>TDO</th>
										<th>Need to Encode</th>
										<th>No. of Encoded</th>
										<th>Total Area (ha)</th>
										<th>Male</th>
										<th>Female</th>
										<th>Total</th>
										<th>Action</th>
									</thead>
								</table>
							</div>
						</div>
					
					</div>
				</div>
			</div>
		</div>
		


		{{-- modal--}}

		<div class="modal fade" id="tdoForm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
			  <div class="modal-content">
				<div class="modal-header">
				  <h5 class="modal-title" id="exampleModalLabel">TDO</h5>
				  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				  </button>
				</div>
				<div class="modal-body">
					<input type="hidden" value="" id="username">
					<input type="hidden" value="" id="state">
					<input type="number" id="ntencode" placeholder="TDO Target Input" class="form-control">
				
				</div>
				<div class="modal-footer">
				  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				  <button type="button" onclick="savedata()" class="btn btn-primary">Save</button>
				</div>
			  </div>
			</div>
		  </div>

	</div>
@endsection

@push('scripts')
	<script>

	function tdoEncoded(username){
		$('#tdoForm').modal('show');
		$('#username').val(username);
		$('#state').val("add");

	}

	
	function tdoEncoded_update(username){
		$('#username').val(username);		
		$.ajax({
            type: "POST",
            url: "{{url('palaysikatan/tdoNtEncode_get_one')}}",
            data: {
                _token: "{{ csrf_token() }}",
                username: username,               
            },
            success: function(response) {
				$('#tdoForm').modal('show');
                $('#ntencode').val(response);
				$('#state').val("update");


            }
        });

	}

	function savedata(){
		var username = $('#username').val();
		var ntencode = $('#ntencode').val();
		var state = $('#state').val();
		if(ntencode<=0){
			alert("Please Input Number in Text Box")
		}else{
	
				$.ajax({
            	type: "POST",
            	url: "{{url('palaysikatan/tdoNtEncode')}}",
            	data: {
            	    _token: "{{ csrf_token() }}",
            	    username: username,
            	    ntencode: ntencode,
					state:state
            	},
            	success: function(response) {
					console.log(response)
            	   if(response==true || response=='true'){				
					   $('#tdoForm').modal('toggle');
					   alert("Success")
					   var station = $('#station').val();
						var encoder = "ALL"
						encoder_load_tbl(station, encoder);
				   }
			   
            	}
        		});
		
			

		}
	}

		var station = $('#station').val();
		var encoder = "ALL"
		encoder_load_tbl(station, encoder);
		  $("#tbl_encoderStatus").DataTable();
	$( "#loadBtn" ).click(function() {
		var station = $('#station').val();
		var encoder = "ALL"
		encoder_load_tbl(station, encoder);
	});
	
	function encoder_load_tbl(station, encoder) {
		
            $('#tbl_encoderStatus').DataTable().clear();
            $("#tbl_encoderStatus").DataTable({
                "pageLength": 25,
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('palaysikatan.station-status-list') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": {
                        "_token": "{{ csrf_token() }}",
                        "station": station,
                        "encoder": encoder,
                    }
                },
                "columns": [{
                        "data": "encoder"
                    },{
                        "data": "noNeedToEncode"
                    },
                    {
                        "data": "noOfEncoded"
                    },					
                    {
                        "data": "area"
                    },
                    {
                        "data": "male"
                    },
                    {
                        "data": "female"
                    },
                    {
                        "data": "total"
                    },
					{
                        "data": "action"
                    },
                    
                ]
            });
        }
	
		
	</script>
@endpush

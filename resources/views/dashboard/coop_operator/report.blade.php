@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
  <style>
    .tab_link_active {
        color: #fff;
        background-color: #337ab7;
        border-color: #2e6da4;
        text-align: center;
    } 
    .tab_link_active .x_content div span{
        border-bottom: 2px solid white;
    }
    span {
        cursor: pointer;
    }
    .coop-link:hover{
        color: blue;
        text-decoration: underline;
    }

    .tile_count .tile_stats_count:before {
        content: "";
        position: absolute;
        left: 0;
        height: 65px;
        border-left: 0;
        margin-top: 10px;
    }
  </style>
@endsection

@section('content')

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="col-md-12 col-sm-12 col-xs-12">			
		<div class="row">
            <div class="col-md-8">
                <div class="x_panel">
                    <div class="x_title">
                        <h2 style="margin-top: 10px;">
							<span style="color: #a4abb9;"><strong>SEED COOPERATIVE:</strong> {{$coop_details->coopName}}</span><br>
							<span style="color: #a4abb9;"><strong>ACCREDITATION NUMBER:</strong> {{$coop_details->accreditation_no}}</span><br>
							<span style="color: #a4abb9;"><strong>CURRENT MOA:</strong> {{$coop_details->current_moa}}</span><br>
						</h2>
						<div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <table class="table table-bordered table-striped" id="coop_members_tbl">
                            <thead>
                                <th>Seed Grower</th>
								<th>Seed Varities</th>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
			<div class="col-md-4">
				<div class="x_panel">
                    <div class="x_title">
                        <h2 style="margin-top: 10px;">
							Pie chart for member  variety
						</h2>
						<div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        
                    </div>
                </div>
			</div>
        </div>
		
		<div class="row">
			<div class="col-md-12">
				<div class="x_panel">
                    <div class="x_title">
                        <h2 style="margin-top: 10px;">
							Delivery Summary
						</h2>
						<div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        
                    </div>
                </div>
			</div>
		</div>

    </div>

@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>
    <script src=" {{ asset('public/js/highcharts.js') }} "></script>

    <script>
		function load_members(){
			$("#coop_members_tbl").DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('coop_operator.report.sg') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        "coop_accreditation": "{{$coop_details->accreditation_no}}",
						"moa_number": "{{$coop_details->current_moa}}",
                    }
                },
                "columns":[
                    {data: 'sg_name'},
					{data: 'variety_list', searchable: false}
                ]
            });
		}
		
		load_members();
    </script>
@endpush

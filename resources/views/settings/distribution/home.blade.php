@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
@endsection

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>
        <div class="clearfix"></div>

            <div class="col-md-12 col-sm-12 col-xs-12">
                @include('layouts.message')
                

                <div class="x_panel">
					<div class="x_title">
						<h2>Distribution Settings for each Dropoff Point</h2>
						<div class="clearfix"></div>
					</div>
					<div class="x_content">
						<table class="table table-striped table-bordered" id="drop_tbl">
							<thead>
								<tr>
									<th>Region</th>
                                    <th>Province</th>
                                    <th>Municipality</th>
                                    <th>Dropoff Point</th>
                                    <th>Action</th>
								</tr>
                            </thead>
						</table>
					</div>
				</div>
                
            </div>

            <div class="modal fade" id="add_variables_modal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-md">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                            <h4 class="modal-title" id="myModalLabel">Set Variables</h4>
                        </div>
                        <form action="{{ route('system.settings.distribution.add') }}" method="POST">
                            {!! csrf_field() !!}
                            <div class="modal-body">                                
                                <label for="lgu_limit">LGU Limit (ha): </label>
                                <input type="number" class="form-control" name="lgu_limit" id="lgu_limit" placeholder="Please enter an LGU limit">

                                <input type="hidden" id="dropoffID" name="dropoffID">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times-circle"></i> Cancel</button>
                                <button type="submit" role="submit" class="btn btn-warning"><i class="fa fa-pencil"></i> Edit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="update_variables_modal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-md">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                            <h4 class="modal-title" id="myModalLabel">Set Variables</h4>
                        </div>
                        <form action="{{ route('system.settings.distribution.update') }}" method="POST">
                            {!! csrf_field() !!}
                            <div class="modal-body">                                
                                <label for="lgu_limit_update">LGU Limit (ha): </label>
                                <input type="number" class="form-control" name="lgu_limit_update" id="lgu_limit_update">
                                <br>

                                <input type="hidden" id="dropoffID_update" name="dropoffID_update">
                            </div>
                            <div class="modal-footer">
                                <button type="submit" role="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
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

    <script>
        $("#drop_tbl").DataTable({
            "bDestroy": true,
            "autoWidth": false,
            "searchHighlight": true,
            "processing": true,
            "serverSide": true,
            "orderMulti": true,
            "order": [],
            "ajax": {
                "url": "{{ route('system.settings.distribution.table') }}",
                "dataType": "json",
                "type": "POST",
                "data":{
                    "_token": "{{ csrf_token() }}"
                }
            },
            "columns":[
                {"data": "region"},
                {"data": "province"},
                {"data": "municipality"},
                {"data": "dropOffPoint"},
                {"data": "action", searchable:false}
            ]
        });

        $('#add_variables_modal').on('show.bs.modal', function (e) {
			var dropoffID = $(e.relatedTarget).data('id');
			$("#dropoffID").val(dropoffID);
		});

        $('#update_variables_modal').on('show.bs.modal', function (e) {
			var dropoffID_update = $(e.relatedTarget).data('id');
            var lgu_limit_update = $(e.relatedTarget).data('lgu');
            var pmo_limit_update = $(e.relatedTarget).data('pmo');

			$("#dropoffID_update").val(dropoffID_update);
            $("#lgu_limit_update").val(lgu_limit_update);
            $("#pmo_limit_update").val(pmo_limit_update);
		});
    </script>
@endpush

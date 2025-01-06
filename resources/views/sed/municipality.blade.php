@extends('layouts.index')

@section('content')
	<div>
		<div class="page-title">
            <div class="title_left">
              <h3>E-binhi Municipality Management</h3>
            </div>
        </div>

        <div class="clearfix"></div>

		<div class="row">
			<div class="col-md-12">
				<div class="x_panel">
					<div class="x_title">
						<h2>Municipalities</h2>
						<div class="clearfix"></div>
					</div>
					<div class="x_content">
												
						<table class="table table-striped table-bordered" id="muniTbl">
							<thead>
								<tr>
									<th style="width: 25%;">Region</th>
									<th style="width: 25%;">Province</th>
									<th style="width: 25%;">Municipality</th>
									<th style="width: 25%;">Participating?</th>
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
        var muniTbl = $('#muniTbl').DataTable({
            processing: true,
            serverSide: true,
            ajax: { 
                url : "{!! route('ebinhi.municipality.datatable') !!}"
            },
            columns: [
                {data: 'regionName', name: 'regionName'},
                {data: 'province', name: 'province'},
                {data: 'municipality', name: 'municipality'},
                {data: 'isEbinhi', name: 'isEbinhi'}
            ]
        });
		
		$('#muniTbl tbody').on('click', 'tr td button.editFarmer', function (e) {
			var prv = $(this).data('id');
			var value = $(this).data('val');
			$.ajax({
				type: "POST",
				url: "{{url('ebinhi/municipality/edit')}}",
				data: {
					prv: prv,
                    value: value,
					_token: "{{csrf_token()}}"
				},
				success: function (response) {
					muniTbl.ajax.reload( null, false );
				}
			});	
		});
	</script>
@endpush

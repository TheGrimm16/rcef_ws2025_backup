<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_title">
					<div class="container">
						<div class="row">
							<div class="col-md-2">
								<h2>Farmer List</h2>
							</div>
						</div>
					</div>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left" style="padding-bottom: 0;padding-left: 0;">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count">
                            <table class="table table-striped table-bordered wrap" id="farmersTbl{{$brgy}}">
                                <thead>
                                    <tr>
										<th style="width: auto;">ID</th>
										<th style="width: auto;">Farmer Name</th>
										<th style="width: auto;">Contact No.</th>
										<th style="width: auto;">Verified Contact No.</th>
										<!-- <th style="width: auto;">Address</th> -->
										<!-- <th style="width: auto;">Area WS2021 (ha)</th>
										<th style="width: auto;">Area DS2021 (ha)</th> -->
										<th style="width: auto;">Verified Area (ha)</th>
										<!-- <th style="width: auto;">Previous E-Binhi benificiaries</th> -->
										<th style="width: auto;">Sex</th>
										<th style="width: auto;">Expected Sowing</th>
										<th style="width: auto;">Variety 1</th>
										<th style="width: auto;">Variety 2</th>
										<th style="width: auto;">Status</th>
									</tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="verifyModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div id="verifyModalContent"></div>
        </div>
    </div>
</div>

<div id="checkParti" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div id="checkPartiContent"></div>
        </div>
    </div>
</div>
<script>
    var farmersTbl = $('#farmersTbl{{$brgy}}').DataTable({
			serverSide: true,
			ajax: { 
                type: "POST",
				url : "{!! route('sra.paymaya.load.farmers.datatable') !!}",
				"data": function(d) {
					d._token = "{{csrf_token()}}";
					d.muni = "{{$muni}}";
					d.brgy = "{{$brgy}}";
					d.month = "{{$month}}";
				}
			},
			columns: [
				{data: 'sed_id', name: 'farmer_id'},
				{data: 'fullname', name: 'fullname'},
				{data: 'contact_no', name: 'contact_no'},
				{data: 'secondary_contact_no', name: 'secondary_contact_no'},
				// {data: 'farm_area_ws2021', name: 'farm_area_ws2021'},
				// {data: 'farm_area_ds2021', name: 'farm_area_ds2021'},
				{data: 'committed_area', name: 'committed_area'},
				// {data: 'has_claim', name: 'has_claim'},
				{data: 'ver_sex', name: 'ver_sex'},
				{data: 'sowing', name: 'sowing'},
				{data: 'preffered_variety1', name: 'preffered_variety1'},
				{data: 'preffered_variety2', name: 'preffered_variety2'},
				{data: 'isScheduled', name: 'isScheduled', searchable: false}
			]
		});

        $('#farmersTbl tbody').on('click', 'tr td button.verifyFarmer', function (e) {
			var farmerid = $(this).data('id');
			$.ajax({
				type: "POST",
				url: "{{url('sed/verification/form/first')}}",
				data: {
					farmerid: farmerid,
					_token: "{{csrf_token()}}"
				},
				success: function (response) {
					if(typeof response.error === 'undefined'){
						$("#checkPartiContent").html(response);
						$('#checkParti').modal({
							backdrop: 'static',
							keyboard: false
						});
					}else{
						alert(response.message);
					}
					farmersTbl.ajax.reload( null, false );
				}
			});	
		});

		
		$("#finalizeList").on("click", function () {
			var answer = window.confirm("Once the verified farmers are push you will not be able to edit all verified farmers. Are you sure?");
			if (answer) {
				$.ajax({
					type: "POST",
					url: "{{url('sed/verification/push/verified')}}",
					data: {
						_token: "{{csrf_token()}}"
					},
					success: function (response) {
						alert(response.message);
						$('#verifyModal').modal('hide');
						farmersTbl.ajax.reload( null, false );
					}
				});
			}
		});
</script>
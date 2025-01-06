<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_title">
					<div class="container">
						<div class="row">
							<div class="col-md-4">
								<h2>Farmer List</h2>
							</div>
                            <div class="col-md-8 text-right">
                                <button class="btn btn-button btn-danger" id='deleteBatch'>Delete Batch</button>
                                <button class="btn btn-button btn-primary" id='sendSMS'>Send SMS</button>
                            </div>
						</div>
					</div>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left" style="padding-bottom: 0;padding-left: 0;">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count">
                            <table class="table table-striped table-bordered wrap" id="farmersTbl{{$batch}}">
                                <thead>
                                    <tr>
										<th style="width: auto;">RSBSA Control No.</th>
										<th style="width: auto;">QR Code</th>
										<th style="width: auto;">Farmer Name</th>
										<th style="width: auto;">Contact No.</th>
										<!-- <th style="width: auto;">Province</th>
										<th style="width: auto;">Municipality</th> -->
										<th style="width: auto;">Barangay</th>
										<th style="width: auto;">Drop Off Point</th>
										<th style="width: auto;">Sex</th>
										<th style="width: auto;">Area</th>
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
    var farmersTbl = $('#farmersTbl{{$batch}}').DataTable({
			serverSide: true,
			ajax: { 
                type: "POST",
				url : "{!! route('sra.paymaya.load.scheduled.farmers.datatable') !!}",
				"data": function(d) {
					d._token = "{{csrf_token()}}";
					d.muni = "{{$muni}}";
					d.batch = "{{$batch}}";
				}
			},
			columns: [
				{data: 'rsbsa_control_no', name: 'rsbsa_control_no'},
				{data: 'paymaya_code', name: 'paymaya_code'},
				{data: 'fullname', name: 'fullname'},
				{data: 'contact_no', name: 'contact_no'},
				// {data: 'province', name: 'province'},
				// {data: 'municipality', name: 'municipality'},
				{data: 'barangay', name: 'barangay'},
				{data: 'drop_off_point', name: 'drop_off_point'},
				{data: 'sex', name: 'sex'},
				{data: 'area', name: 'area'},
				{data: 'isSent', name: 'isSent', searchable: false}
			]
		});

		
		$("#sendSMS").on("click", function () {
			var answer = window.confirm("Send SMS to all farmers in this batch, Are you sure?");
			if (answer) {
				$.ajax({
					type: "POST",
					url: "{{url('sra/paymaya/get/sms/farmers')}}",
					data: {
						_token: "{{csrf_token()}}",
                        batch: "{{$batch}}"
					},
					success: function (data) {
                       var farmers = data.data;
                       var message1 = "";
                       var message2 = "";
                       var count = 0;
                       var name = "";
                       if(farmers.length > 0){
                            $('#sendSMS').prop('disabled', true);
                            $('#sendSMS').html('Sending SMS...');
                       }else{
                           alert("No Data to Send")
                       }
                       
						farmers.forEach(e => {
                            name = e.firstname+" "+e.lastname;
                            message1 = message(data.time, data.date, e.bags, e.drop_off_point, e.paymaya_code, name);
                            message2 = "[2] Kung hindi makakapunta, maaaring kunin ng iyong kinatawan ang libreng binhi. Ipakita lamang ang claim code, pirmadong authorization letter, at valid ID ng kinatawan. Mangyaring huwag burahin o balewalain ang mensaheng ito. Kung may tanong, magtext sa PhilRice Text Center 0917-111-7423";
                            
                            $.ajax({
                                type: "GET",
                                url: "https://isd.philrice.gov.ph/ptc_v2/_api/send/27ef4e0f8002f3dd298bd0b3bfdc37a5/"+message1+"/"+ e.contact_no,
                                success: function (response) {
                                    $.ajax({
                                    type: "GET",
                                    url: "https://isd.philrice.gov.ph/ptc_v2/_api/send/27ef4e0f8002f3dd298bd0b3bfdc37a5/"+message2+"/"+ e.contact_no,
                                        success: function (response) {}
                                    });
                                    $.ajax({
                                        type: "POST",
                                        url: "{{url('sra/paymaya/update/sent/status')}}",
                                        data: {
                                            _token: "{{csrf_token()}}",
                                            isSent: "1",
                                            beneficiary_id: e.beneficiary_id
                                        },
                                        success: function (response) {
                                            count++;
                                            if(count === farmers.length){
                                                $('#sendSMS').prop('disabled', false);
                                                $('#sendSMS').html('Send SMS');
                                                farmersTbl.ajax.reload(null, false);
                                            }
                                            
                                        }
                                    });
                                    
                                   
                                }
                            });
                           
                        });
					}
				});
			}
		});

        function message(time, date, bags, dop, claim_code, name){
            var message = '[1] RCEF Seeds: Magandang araw po, '+ name +'! Maaari mo nang makuha ang ' + bags + ' sakong binhi gamit ang claim code "' + claim_code + '". Pumunta sa ' + dop + " mula " + date+ ", "+ time + ". Ipakita ang claim code, valid ID at RSBSA stub.";
            return message;
        }

        $("#deleteBatch").on("click", function () {
           
			var answer = window.confirm("Delete this batch, Are you sure?");
			if (answer) {
                $('#deleteBatch').prop('disabled', true);
            $('#deleteBatch').html('Deleting Batch...');
				$.ajax({
					type: "POST",
					url: "{{url('sra/paymaya/delete/scheduled/farmers')}}",
					data: {
						_token: "{{csrf_token()}}",
                        batch: "{{$batch}}"
					},
					success: function (data) {
                       alert(data.message);
                       $('#deleteBatch').prop('disabled', false);
                        $('#deleteBatch').html('Delete Batch');
                        farmersTbl.ajax.reload(null, false);
					}
				});
			}
		});
</script>
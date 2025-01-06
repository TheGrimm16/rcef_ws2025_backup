<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_title container">
                    <div class="row">
                        <div class="col-md-2">
                            <h2>Farmer List</h2>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="radio" class="control-label col-xs-2">FILTER:</label>
                                <div class="col-xs-10">
                                    <label class="radio-inline">
                                        <input type="radio" name="filter" value="yes" checked>
                                            YES
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="filter" value="no">
                                            NO
                                    </label>
                                    
                                    <!-- <label class="radio-inline">
                                        <input type="radio" name="filter" value="next">
                                            NEXT SEASON
                                    </label> -->
                                    <label class="radio-inline">
                                        <input type="radio" name="filter" value="failed">
                                            FAILED CALLS
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="filter" value="all">
                                            ALL
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="filter" value="pending">
                                            PENDING
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            @if(Auth::user()->username == "rcef_sra")
                                <button class="btn btn-button btn-info" id='SRAdownloadFarmers'><i class="fa fa-file-excel-o" aria-hidden="true"></i> SRA Download Verified Farmers</button>
                            @else
                            <button class="btn btn-button btn-success" id='downloadFarmers'><i class="fa fa-file-excel-o" aria-hidden="true"></i> Download Verified Farmers</button>
                            
                            <button class="btn btn-button btn-primary" id='finalizeList'>Push Data</button>
							@endif
                        </div>
                    </div>

                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left" style="padding-bottom: 0;padding-left: 0;">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count">
                            <table class="table table-striped table-bordered" id="farmersTbl">
                                <thead>
                                    <tr>
                                        <th style="width: auto;">ID</th>
                                        <th style="width: auto;">Farmer Name</th>
                                        <!-- <th style="width: auto;">Contact No.</th> -->
                                        <th style="width: auto;">Verified No.</th>
                                        <!-- <th style="width: auto;">Address</th> -->
                                        <!-- <th style="width: auto;">Area</th> -->
                                        <th style="width: auto;">Verified Area</th>
                                        <!-- <th style="width: auto;">Yield (kg/ha)</th> -->
                                        <th style="width: auto;">Sowing Month</th>
                                        <th style="width: auto;">Sowing Week</th>
                                        <th style="width: auto;">Previous E-Binhi benificiary?</th>
                                        <th style="width: auto;">Sex</th>
                                        <th style="width: auto;">Created By</th>
                                        <th style="width: auto;">Action</th>
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
        <div id="checkParti2" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<div class="modal-content">
					<div id="checkPartiContent2"></div>
				</div>
			</div>
		</div>
<script>
var farmersTbl = $('#farmersTbl').DataTable({
    "serverSide": true,
    "ajax": {
        "url": "{!! route('sed.verified.datatable') !!}",
        "type": "POST",
        "data": function(d) {
            d._token = "{{csrf_token()}}";
            d.municode = "{{$municode}}";
            d.filter = $('input[name="filter"]:checked').val();
        }
    },
    columns: [{
            data: 'farmer_id',
            name: 'farmer_id'
        },
        {
            data: 'fullname',
            name: 'fullname'
        },
        // {
        //     data: 'contact_no',
        //     name: 'contact_no'
        // },
        {
            data: 'secondary_contact_no',
            name: 'secondary_contact_no'
        },
        // {
        //     data: 'farm_area',
        //     name: 'farm_area'
        // },
        {
            data: 'committed_area',
            name: 'committed_area'
        },
        {
            data: 'sowing_month',
            name: 'sowing_month'
        },
        {
            data: 'sowing_week',
            name: 'sowing_week'
        },
        // {
        //     data: 'yield',
        //     name: 'yield',
        //     orderable: false,
        //     searchable: false
        // },
        {
            data: 'has_claim',
            name: 'has_claim'
        },
        {
            data: 'ver_sex',
            name: 'ver_sex'
        },
        {
            data: 'createdBy',
            name: 'createdBy'
        },
        {
            data: 'actions',
            name: 'actions',
            orderable: false,
            searchable: false
        }
    ]
});

$('#farmersTbl tbody').on('click', 'tr td button.verifyFarmer', function(e) {
    var farmerid = $(this).data('id');
    $.ajax({
        type: "POST",
        url: "{{url('sed/verified/verification/form/first')}}",
        data: {
            farmerid: farmerid,
            _token: "{{csrf_token()}}"
        },
        success: function(response) {
            if (typeof response.error === 'undefined') {
                $("#checkPartiContent").html(response);
                $('#checkParti').modal({
                    backdrop: 'static',
                    keyboard: false
                });
            } else {
                alert(response.message);
            }
            farmersTbl.ajax.reload(null, false);
        }
    });
});

$('#verifyModal').on('hidden.bs.modal', function(e) {
    farmersTbl.ajax.reload(null, false);
});

$('#checkParti2').on('hidden.bs.modal', function(e) {
    farmersTbl.ajax.reload(null, false);
});

$("#finalizeList").on("click", function() {
    var answer = window.confirm(
        "Once the verified farmers are push you will not be able to edit all verified farmers. Are you sure?"
        );
    if (answer) {
        $.ajax({
            type: "POST",
            url: "{{url('sed/verification/push/verified')}}",
            data: {
                _token: "{{csrf_token()}}",
                municode: "{{$municode}}"
            },
            success: function(response) {
                alert(response.message);
                $('#verifyModal').modal('hide');
                farmersTbl.ajax.reload(null, false);
            }
        });
    }
});

$('input[name="filter"]').on("change", function () {
    farmersTbl.ajax.reload(null);
});

$('#farmersTbl tbody').on('click', 'tr td button.enableEdit', function (e) {
        var id = $(this).data('id');
        var value = $(this).data('value');
        $.ajax({
            type: "POST",
            url: "{{url('sed/enable/edit/view')}}",
            data: {
                id: id,
                value: value,
                _token: "{{csrf_token()}}"
            },
            success: function (response) {
                $("#checkPartiContent2").html(response);
                $('#checkParti2').modal();
                farmersTbl.ajax.reload(null, false);
            }
        });	
    });

$("#downloadFarmers").click(function (e) { 
    e.preventDefault();
    window.open("{{url('sed/excel/verified/')}}" + "/{{$municode}}");
});
$("#SRAdownloadFarmers").click(function (e) { 
    e.preventDefault();
    window.open("{{url('sed/excel/verified/sra')}}" + "/{{$municode}}");
});
</script>
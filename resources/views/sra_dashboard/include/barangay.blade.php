<style>
.active_collapse {
    background-color: #337ab7 !important;
    color: white !important;
}

.panel>.panel-heading2 {
    background-color: #337ab7;
    color: white;
    border-bottom: 0;
    padding: 10px 15px;
    border-top-left-radius: 3px;
    border-top-right-radius: 3px;
}

.scheduling-contrainer {
    background-color: #F5F5F5;
    border: solid 1px #ddd;
    border-top-left-radius: 3px;
    border-top-right-radius: 3px;
    padding: 10px 15px;
}

.scheduling-title {
    font-size: 18px;
    font-weight: bolder;

}
</style>
<link rel="stylesheet" type="text/css" href="{{ asset('public/assets/daterangepicker/daterangepicker.css') }}" />
<div class="container" style="padding: 3rem">
    <div class="row" style="margin-bottom: 30px">
        <div class="col-md-12">
            <div class="scheduling-contrainer">
                <div class="scheduling-title" style="margin-bottom: 30px">SCHEDULE FARMER</div>
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for="dop_select" class="control-label">Drop Off Point </label>
                        <select id="dop_select" name="dop_select" class="js-example-basic-single"
                             style="width: 100% !important" required>
                            <option value="" disabled>Select Drop Off Point</option>
                            @foreach($dops as $d)
                            <option value="{{$d->ebinhi_dopID}}">{{$d->pickup_location}} ( {{$d->coop_name}} )</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-5">
                        <label for="barangay_select" class="control-label">Barangay <strong
                                style="color:red">*</strong></label>
                        <select id="barangay_select" name="barangay_select[]" class="js-example-basic-multiple"
                            multiple="multiple" style="width: 100% !important" required>
                            <option value="" disabled>Select Barangay</option>
                            @foreach($data as $k => $p)
                            <?php 
                                $unsched = intval($p->farmer_count) - intval($p->farmer_scheduled);
                            ?>
                            @if($unsched > 0)
                            <option value="{{$p->barangay_code}}">{{$p->name}} <b>({{$unsched}})</b></option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-sm-2">
                        <label for="variety_select" class="control-label">Seed Variety </label>
                        <select id="variety_select" name="variety_select[]" class="js-example-basic-multiple"
                            multiple="multiple" style="width: 100% !important" required>
                            <option value="" disabled>Select Seed Variety</option>
                            @foreach($variety as $v)
                            <option value="{{$v[0]['seedVariety']}}">{{$v[0]['seedVariety']}}</option>
                            @endforeach
                        </select>
                    </div>
                    

                    <div class="col-md-2">
                        <button name="submit" type="button" class="btn btn-primary pull-right"
                            id="schedFarmer" style="margin-top:20px">Schedule</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">

            <div class="clearfix"></div>
            <div class="panel-group" id="accordion3" role="tablist" aria-multiselectable="true">
                <div class="panel panel-success">
                    <div class="panel-heading" role="tab" id="head_">
                        <div class="panel-title container-fluid">
                            <div class="row">
                                <div class="col-md-5">Barangay</div>
                                <div class="col-md-2">no. of Farmers</div>
                                <div class="col-md-2">Scheduled Farmers</div>
                                <div class="col-md-1 text-center"></div>
                                <div class="col-md-1 text-center"></div>
                                <!-- <div class="col-md-2">Distribution Date</div> -->
                                <div class="col-md-1 text-right">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @foreach($data as $d)
                <div class="panel panel-default" id="brgy_panels">
                    <div class="panel-heading" role="tab" id="head_{{$d->barangay_code}}">
                        <div class="panel-title container-fluid">
                            <div class="row">
                                <div class="col-md-5">{{$d->name}}</b></div>
                                <div class="col-md-2">{{$d->farmer_count}}</div>
                                <div class="col-md-2">{{$d->farmer_scheduled}}</div>
                                <div class="col-md-1 text-center"></div>
                                <div class="col-md-1 text-center"></div>
                                <div class="col-md-1 text-right">
                                    <a role="button" class="collapseBtnBrgy" data-toggle="collapse"
                                        data-parent="#accordion3" href="#{{$d->barangay_code}}" aria-expanded="true"
                                        aria-controls="{{$d->barangay_code}}" data-brgy="{{$d->barangay_code}}">
                                        <i class="fa fa-plus" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="{{$d->barangay_code}}" class="panel-collapse collapse" role="tabpanel"
                        aria-labelledby="head_{{$d->barangay_code}}">
                        <div class="panel-body">
                            <div class="text-center">Loading <i class="fas fa-spinner"></i></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
<div id="schedFarmerModal" class="modal fade" role="dialog">
    <div class="modal-dialog " style="width:90%">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="container" style="padding: 30px">
                <div class="row" style="margin-bottom: 30px">

                    <div class="col-sm-2">
                        <label for="municipality" class="control-label">Schedule Date <strong
                                style="color:red">*</strong></label><br />
                        <input type="text" name="daterange" class="form-control" value="{{$datedefault}}" />
                    </div>
                    <div class="col-sm-2">
                        <label for="scheduletime" class="control-label">Time <strong
                                style="color:red">*</strong></label></label><br />
                        <select id="scheduletime" name="scheduletime" class="form-control" style="width: 100% !important">
                            <option value="AM">AM</option>
                            <option value="PM">PM</option>
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <label for="farmerscount" class="control-label">Farmers Count Limit </label>
                        <input id="farmerscount" name="farmerscount" type="number" class="form-control" required
                            value="">
                    </div>
                    <div class="col-sm-2">
                        <button name="submit" type="submit" class="btn btn-primary pull-right" id="schedFarmersSave"
                            style="margin-top: 20px">Schedule
                            Farmers</button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="x_panel">
                            <div class="x_title">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <h2>Selected Farmers</h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content form-horizontal form-label-left"
                                style="padding-bottom: 0;padding-left: 0;">
                                <div class="row tile_count" style="margin: 0">
                                    <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count">
                                        <table class="table table-striped table-bordered wrap" style="width:100%"
                                            id="selectedFarmer{{$muni}}">
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
                <div class="form-group row" style="margin-top: 20px">

                    <button class="btn btn-light pull-right" id="dismissModal">cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ asset('public/assets/daterangepicker/daterangepicker.js') }}"></script>
<script>
$('input[name="daterange"]').daterangepicker();
$('.js-example-basic-multiple').select2();
$('.js-example-basic-single').select2();
var selectedFarmer = $('#selectedFarmer{{$muni}}').DataTable({
    serverSide: true,
    processing: true,
    language: {
        'loadingRecords': '&nbsp;',
        'processing': '<div class="spinner"></div> Loading...'
    },
    ajax: {
        type: "POST",
        url: "{!! route('sra.paymaya.load.selected.farmers.datatable') !!}",
        "data": function(d) {
            d._token = "{{csrf_token()}}";
            d.brgy = $("#barangay_select").select2("val");
            d.variety = $("#variety_select").select2("val");
            d.month = "{{$month}}";
            d.muni = "{{$muni}}";
            d.farmers_count = $("#farmerscount").val();
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
        {
            data: 'contact_no',
            name: 'contact_no'
        },
        {
            data: 'secondary_contact_no',
            name: 'secondary_contact_no'
        },
        // {data: 'farm_area_ws2021', name: 'farm_area_ws2021'},
        // {data: 'farm_area_ds2021', name: 'farm_area_ds2021'},
        {
            data: 'committed_area',
            name: 'committed_area'
        },
        // {data: 'has_claim', name: 'has_claim'},
        {
            data: 'ver_sex',
            name: 'ver_sex'
        },
        {
            data: 'sowing',
            name: 'sowing'
        },
        {
            data: 'preffered_variety1',
            name: 'preffered_variety1'
        },
        {
            data: 'preffered_variety2',
            name: 'preffered_variety2'
        },
        {
            data: 'actions',
            name: 'actions',
            orderable: false,
            searchable: false
        }
    ]
});

$(".collapseBtnBrgy").on("click", function() {
    var brgy = $(this).data('brgy');
    let id = "#" + brgy + " .panel-body";
    let heading_id = "#head_" + brgy;
    $(id).html('<div class="text-center">Loading <i class="fas fa-spinner"></i></div>');
    $.ajax({
        type: "POST",
        url: "{{url('sra/paymaya/load/farmers')}}",
        data: {
            brgy: brgy,
            month: "{{$month}}",
            muni: "{{$muni}}",
            _token: "{{csrf_token()}}"
        },
        success: function(response) {
            $("#" + brgy + ".panel-collapse .panel-body").empty();
            if (!$(heading_id).hasClass("active_collapse")) {
                $('#brgy_panels>.panel-heading').removeClass("active_collapse");
                $(heading_id).addClass("active_collapse");
            } else {
                $('#brgy_panels>.panel-heading').removeClass("active_collapse");
            }
            $(id).append(response);
        }
    });
});

$("#schedFarmer").click(function(e) {
    e.preventDefault();
    selectedFarmer.ajax.reload(null, false);
    $('#schedFarmerModal').modal({
        backdrop: 'static',
        keyboard: false
    });
});
$("#dismissModal").on("click", function(e) {
    e.preventDefault();
    $('#schedFarmerModal').modal('hide');
});
$("#schedFarmersSave").on("click", function (e) {
        e.preventDefault();
        // $('#verifyModal').modal('hide');
        var answer = window.confirm("Are You Sure?");
        if (answer) {
            $.ajax({
                type: "POST",
                url: "{{url('sra/scheduling/save')}}",
                data: {
                    _token : "{{csrf_token()}}",
                    brgy : $("#barangay_select").select2("val"),
                    variety : $("#variety_select").select2("val"),
                    daterange : $('input[name="daterange"]').val(),
                    schedtime : $('#scheduletime').val(),
                    farmerLimit : $('#farmerscount').val(),
                    dop : $('#dop_select').val(),
                    month : "{{$month}}",
                    muni : "{{$muni}}",
                    
                },
                success: function (response) {
                    
                    if(response.status == 1){
                        alert(response.message);
                        $('#schedFarmerModal').modal('hide');
                    }else{
                        alert(response.message);
                    }
                    
                }
            });
        }
        else {
            //some code
        }  
    });

    $('#selectedFarmer{{$muni}} tbody').on('click', 'tr td button.excludeFarmer', function (e) {
        e.preventDefault();
			var id = $(this).data('id');
			var status = $(this).data('status');
			$.ajax({
				type: "POST",
				url: "{{url('ebinhi/scheduler/exclude')}}",
				data: {
                    id: id,
                    status: status,
					_token: "{{csrf_token()}}"
				},
				success: function (response) {
                    alert(response.message);
					selectedFarmer.ajax.reload( null, false );
				}
			});	
		});
</script>
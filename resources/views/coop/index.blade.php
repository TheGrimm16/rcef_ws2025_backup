@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
  <style>
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button {  
        opacity: 1;
    }

    /* The container */
    .check_box_container {
        display: block;
        position: relative;
        padding-left: 35px;
        margin-bottom: 12px;
        cursor: pointer;
        font-size: 15px;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }

    /* Hide the browser's default radio button */
    .check_box_container input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }

    /* Create a custom radio button */
    .checkmark {
        position: absolute;
        top: 0;
        left: 0;
        height: 25px;
        width: 25px;
        background-color: #bbb;
        border-radius: 50%;
    }

    /* On mouse-over, add a grey background color */
    .check_box_container:hover input ~ .checkmark {
        background-color: #ccc;
    }

    /* When the radio button is checked, add a blue background */
    .check_box_container input:checked ~ .checkmark {
        background-color: #2196F3;
    }

    /* Create the indicator (the dot/circle - hidden when not checked) */
    .checkmark:after {
        content: "";
        position: absolute;
        display: none;
    }

    /* Show the indicator (dot/circle) when checked */
    .check_box_container input:checked ~ .checkmark:after {
        isplay: block;
    }

    /* Style the indicator (dot/circle) */
    .check_box_container .checkmark:after {
        top: 9px;
        left: 9px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: white;
    }
  </style>
@endsection

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>
        <div class="clearfix"></div>

            <div class="col-md-12 col-sm-12 col-xs-12">
                @include('layouts.message')
                
                <!-- delivery details -->
                <div class="x_panel">
                <div class="x_title">
                    <h2>Commited Deliveries per Seed Cooperative</h2>                    
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <br/>
                        <table class="table table-hover table-striped table-bordered" id="coop_tbl">
                            <thead>
                                <th>Cooperative</th>
                                <th>Accreditation Number</th>
                                <th>MOA Number</th>
                                <th>Action</th>
                            </thead>
                        </table>
                </div>
                </div><br>
                <!-- /delivery details -->
            </div>

            <!-- UPDATE MOA NUMBER -->
            <div id="moa_number_modal" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">
                                UPDATE MOA NUMBER
                            </h4>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="coop_id_moa" name="coop_id_moa" value="">
                            <input type="text" id="coop_moa_number" name="coop_moa_number" class="form-control" placeholder="MOA NUMBER Ex: RCEP19-0710" value="">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger"><i class="fa fa-times-circle"></i> Cancel</button>
                            <button type="button" class="btn btn-info" id="update_moa_btn"><i class="fa fa-file"></i> SAVE AS CURRENT MOA NUMBER</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- UPDATE MOA NUMBER -->


             <!-- UPDATE TARGET EFFICIENCY -->
             <div id="target_efficiency_modal" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">
                                COOP TARGET EFFICIENCY
                            </h4>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="coop_id_target" name="coop_id_target" value="">
                            <!-- <input type="text" id="coop_moa_number" name="coop_moa_number" class="form-control" placeholder="MOA NUMBER Ex: RCEP19-0710" value=""> -->
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="target_efficiency" value="100">
                                <label class="form-check-label" for="inlineRadio1">100% = 200bags/ha</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="target_efficiency" value="80">
                                <label class="form-check-label" for="inlineRadio2">80% = 200bags/ha</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger"><i class="fa fa-times-circle" data-dismiss="modal"></i> Cancel</button>
                            <button type="button" class="btn btn-info" id="update_tf_btn"><i class="fa fa-file"></i> SAVE</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- UPDATE TARGET EFFICIENCY -->


            <!-- ADD COMMITMENT -->
            <div id="commitmentModal" class="modal fade" role="dialog">
                <div class="modal-dialog modal-lg">
                    <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="coopName"></h4>
                            </div>
                            <div class="modal-body" style="max-height: 400px;overflow-y: auto;">
                                <div class="row" id="modal_row_id">
                                    <div class="col-md-12" id="add_moa_fld">
                                        <div class="form-group">
                                            <input type="text" class="form-control" name="add_moa" id="add_moa" placeholder="Please enter the current MOA Number" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12" id="add_commit_fld">
                                        <div class="form-group">
                                            <input type="text" class="form-control" name="add_commit_total" id="add_commit_total" value="TOTAL COMMITMENT: 0 bags" disabled readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6" id="seed_variety_fld">
                                        <div class="form-group">
                                            <select name="seed_variety" id="seed_variety" style="width: 100%;" class="form-control">
                                                <option value="ANY">ANY Region</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4" id="delivery_commitment_fld">
                                        <div class="form-group">
                                            <input type="number" class="form-control" name="commitment_value" id="commitment_value" placeholder="Please enter a value (20kg/bag)" data-parsley-required>
                                        </div>
										<div class="form-group">
                                            <select class="form-control" name="tagged_region" id="tagged_region">
												<option>ANY Region</option>
												@foreach($region_list as $region)
													<option value="{{$region->regionName}}">{{$region->regionName}}</option>
												@endforeach
											</select>
                                        </div>
                                        <div class="form-group">
                                            <select class="form-control" name="tagged_category" id="tagged_category">
                                                <option value="">Select category</option>
                                                <option value="RCEF">RCEF</option>
                                                <option value="NRP">NRP</option>
                                                <option value="GQS">GQS</option>
											</select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <button class="btn btn-warning btn-block" id="Add_BTN">Add Commitment</button>
                                        </div>
                                    </div>

                                    <div class="col-md-12" style="display:none" id="row_id_commitment">
                                        <div class="form-group">
                                            <label for="total_commitment"><b>TOTAL COMMITMENT:</b> </label>
                                            <input type="text" class="form-control" name="total_commitment" id="total_commitment" placeholder="Please enter the total commitment of the seed cooperative">
                                        </div>
                                    </div>
                                </div>
                                
                                <input type="hidden" id="coopID" name="coopID" value="">
                                
                                <hr style="border-top:1px solid black;display:none" id="hr_line">

                                <table class="table table-bordered table-striped" id="coop_add_tbl" style="display:none;width:100%;">
                                    <thead>
                                        <th>Variety</th>
                                        <th>Volume (20kg/bag)</th>
										<th>Region</th>
										<th>Category</th>
                                        <th>Date Added</th>
                                        <th>Action</th>
                                    </thead>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" id="deleteAllCommitment"><i class="fa fa-times-circle"></i> Cancel Transaction</button>
                                <button type="button" class="btn btn-success" id="computeTotalCommitment"><i class="fa fa-plus-circle"></i> Save Delivery Commitment</button>
                            </div>
                        </div>
                </div>
            </div>
            <!-- ADD COMMITMENT -->

            
            <!-- EDIT COMMITMENT -->
            <div id="coopDetails" class="modal fade" role="dialog">
                <div class="modal-dialog modal-lg">
                    <!-- Modal content-->                
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="coopDetailsName">
                                    Seed Cooperative Name
                                </h4>
                                <span class="custom-header-span" id="coopDetailsTotal">Total Commitment: </span><br>
                                <span class="custom-header-span" id="coopDetailsNumber">Accreditation Number: </span><br>
                                <span class="custom-header-span" id="coopDetailsMOA">MOA Number: </span>                            
                            </div>
                            <div class="modal-body">
								<table class="table table-bordered table-striped" id="coop_detail_tbl">
									<thead>
										<th>Variety</th>
										<th>Volume (20kg/bag)</th>
										<th>Region</th>
										<th>Category</th>
										<th>Status</th>
										<th>Date Added</th>
										<th>Action</th>
									</thead>
								</table>

								<input type="hidden" id="coopDetailsID" name="coopDetailsID" value="">
								<div class="row" style="display:block" id="add_more_btn_fld">
									<div class="col-md-4">
										<button class="btn btn-success btn-block" id="add_more_btn"><i class="fa fa-plus-circle"></i> ADD MORE</button>
									</div>
								</div>

								<hr style="border-top:1px solid black;display:none" id="hr_line_add">

								<div class="row" style="display:none" id="add_more_variety_fld">
									<div class="col-md-3">
										<select name="add_more_variety" id="add_more_variety" style="width: 100%;" class="form-control">
										</select>
									</div>
                                    <div class="col-md-3">
                                        <select class="form-control form-select" id="additional_region_select">
                                            @foreach($region_list as $region)
                                                <option value="{{$region->regionName}}">{{$region->regionName}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select class="form-control form-select" id="additional_category_select">
                                                <option value="">Select category</option>
                                                <option value="RCEF">RCEF</option>
                                                <option value="NRP">NRP</option>
                                                <option value="GQS">GQS</option>
                                        </select>

                                    </div>
									<div class="col-md-3" style="display:none" id="add_more_value_fld">
										<input type="number" name="add_more_value" id="add_more_value" class="form-control" placeholder="Please enter a value">
									</div>
									<div class="col-md-1" style="display:none" id="add_submit_btn_fld">
										<button class="btn btn-success btn-block" id="add_submit_btn">ADD</button>
									</div>
								</div>
                            </div>
                        </div>
                    
                </div>
            </div>
            <!-- EDIT COMMITMENT -->
			
			<!-- ADJUST COMMITMENT -->
            <div id="commitmentAdjustmentModal" class="modal fade" role="dialog">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="coopAdjustmentsName">
                                Seed Cooperative Name
                            </h4>
                            <span class="custom-header-span" id="coopAdjustmentsNumber">Accreditation Number: </span><br>
							<span class="custom-header-span" id="coopAdjustmentsBalance">Available Volume for Adjustment: </span>
                        </div>
                        <div class="modal-body">
                            <input type="text" value="" id="coopAdjustment_id" style="display: none">
							<input type="text" value="" id="coopAdjustment_balance" style="display: none">
                            <div class="row">
                                <div class="col-md-3" id="col3RegionList"></div>
                                <div id="col9VarietyList">
                                    <div class="col-md-9" style="border-left: 1px solid #cccccc;">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <input type="text" class="form-control" value="NSIC Rc XXX - X,XXX bag(s)" style="height:45px;" disabled readonly>
                                                <span class="input-group-btn">
                                                    <button type="button" class="btn btn-default" style="margin:0;font-size: 21px;" disabled readonly>+</button>
                                                    <button type="button" class="btn btn-default" style="margin:0;font-size: 21px;" disabled readonly>-</button>
                                                </span>
                                            </div>                                    
                                        </div>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <input type="text" class="form-control" value="NSIC Rc XXX - X,XXX bag(s)" style="height:45px;" disabled readonly>
                                                <span class="input-group-btn">
                                                    <button type="button" class="btn btn-default" style="margin:0;font-size: 21px;" disabled readonly>+</button>
                                                    <button type="button" class="btn btn-default" style="margin:0;font-size: 21px;" disabled readonly>-</button>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <input type="text" class="form-control" value="NSIC Rc XXX - X,XXX bag(s)" style="height:45px;" disabled readonly>
                                                <span class="input-group-btn">
                                                    <button type="button" class="btn btn-default" style="margin:0;font-size: 21px;" disabled readonly>+</button>
                                                    <button type="button" class="btn btn-default" style="margin:0;font-size: 21px;" disabled readonly>-</button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ADJUST COMMITMENT -->

    </div>



    <div id="adjust_commitment" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="adjust_coop_name">
                        Seed Cooperative Name
                    </h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="commitment_id">
                    <input type="hidden" id="commitment_region">
                    <input type="hidden" id="commitment_coop">
                    <input type="hidden" id="coop_id">
                    


                    <div class="row">
                        <div class="col-md-5" > <label class="btn btn-success" style="width: 10vw;">  Region Name </label> </div>
                        <div class="col-md-7" id="adjust_region_name">
                            <select class="form-control form-select" id="adjust_region_select">
                                @foreach($region_list as $region)
                                    <option value="{{$region->regionName}}">{{$region->regionName}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-5" class="btn btn-success"> <label class="btn btn-success" style="width: 10vw;"> Seed Variety </label>  </div>
                        <div class="col-md-7" id="adjust_seed_variety">
                            <select class="form-control form-select" id="adjust_seed_variety_select">
                                @foreach($seed_variety as $variety)
                                    <option value="{{$variety->variety}}">{{$variety->variety}}</option>
                                @endforeach
                            </select>
                            
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-5" class="btn btn-success"> <label class="btn btn-success" style="width: 10vw;"> Category</label>  </div>
                        <div class="col-md-7" id="adjust_category">
                            <select class="form-control form-select" id="update_category_select">
                                    <option value="">Select Category</option>
                                    <option value="RCEF">RCEF</option>
                                    <option value="NRP">NRP</option>
                                    <option value="GQS">GQS</option>
                            </select>
                            
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-5" class="btn btn-success"> <label class="btn btn-success" style="width: 10vw;"> Volume  </label> </div>
                        <div class="col-md-7" >
                            <input type="text" class="form-control" id="adjust_volume" >     
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-success" onclick="update_commitment();">Update</button>
                </div>

            </div>
        </div>
    </div>
    <!-- ADJUST COMMITMENT -->

</div>


@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

    <script>
        var temp_coop_id = 0;
           $('#adjust_commitment').on('show.bs.modal', function (e) {
            var coop_name = $(e.relatedTarget).data('coop_name');
            var seed_variety = $(e.relatedTarget).data('seed_variety');
            var volume = $(e.relatedTarget).data('volume');
            var region_name = $(e.relatedTarget).data('region_name');
            var commitment_id = $(e.relatedTarget).data('commitment_id');
            var coop_id = $(e.relatedTarget).data('coop_id');
            var category = $(e.relatedTarget).data('category');

            if(coop_id == 68)
            {
                console.log(region_name);
                $.ajax({
                        type: 'POST',
                        url: "{{ route('coop.getStations') }}",
                        data: {
                            _token: "{{ csrf_token() }}",
                        },
                        success: function(data){
                            $("#adjust_region_select").empty();
                            $.each(data, function(index, region) {
                                $("#adjust_region_select").append(
                                    $('<option>', {
                                        value: region.station,
                                        text: region.station
                                    })
                                );
                            });
                            $("#adjust_region_select").val(region_name).change();

                        }
                    });
            }    

            $("#adjust_coop_name").empty().text(coop_name);
            $("#adjust_region_select").val(region_name).change();
            $("#adjust_seed_variety_select").val(seed_variety).change();
            $("#adjust_volume").val(volume);
            $("#commitment_id").val(commitment_id);
            $("#commitment_region").val(region_name);
            $("#commitment_coop").val(coop_name);
            $("#coop_id").val(coop_id);
            $("#category").val(category);
            $('#update_category_select').val(category);

        });

        $("#seed_variety").select2();
		
        function update_commitment(){
            var yesno = confirm("Adjust Coop Commitment");

            if(yesno){

                var id = $("#commitment_id").val();
                var orig_region = $("#commitment_region").val();
                var commitment_coop = $("#commitment_coop").val();
                var volume = $("#adjust_volume").val();
                var variety = $("#adjust_seed_variety_select").val();
                var new_region = $("#adjust_region_select").val();
                var coop_id =  $("#coop_id").val();
                var category = $("#update_category_select").val();

                $.ajax({
                    type: 'POST',
                    url: "{{ route('coop.commitment.new_update') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id,
                        orig_region: orig_region,
                        commitment_coop: commitment_coop,
                        volume: volume,
                        variety: variety,
                        new_region: new_region,
                        coop_id: coop_id,
                        category: category
                    },
                    success: function(data){
                        alert(data);
                        $('#adjust_commitment').modal("hide");

                        $('#coop_detail_tbl').DataTable().clear();

                        $("#coop_detail_tbl").DataTable({
                            "bDestroy": true,
                            "searchHighlight": true,
                            "processing": true,
                            "serverSide": true,
                            "orderMulti": true,
                            "order": [],
                            "ajax": {
                                "url": "{{ route('coop.details.table') }}",
                                "dataType": "json",
                                "type": "POST",
                                "data":{
                                    "_token": "{{ csrf_token() }}",
                                    "coopDetailsID": coop_id
                                }
                            },
                            "columns":[
                                {"data": "commitment_variety"},
                                {"data": "variety_bags"},
                                {"data": "region"},
                                {"data": "category"},
                                {"data": "status_btn"},
                                {"data": "date_add"},
                                {"data": "action"}
                            ]
                        });

                    }
                });
            }
        }


		
		function number_format (number, decimals, dec_point, thousands_sep) {
			// Strip all characters but numerical ones.
			number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
			var n = !isFinite(+number) ? 0 : +number,
				prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
				sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
				dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
				s = '',
				toFixedFix = function (n, prec) {
					var k = Math.pow(10, prec);
					return '' + Math.round(n * k) / k;
				};
			// Fix for IE parseFloat(0.55).toFixed(0) = 0;
			s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
			if (s[0].length > 3) {
				s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
			}
			if ((s[1] || '').length < prec) {
				s[1] = s[1] || '';
				s[1] += new Array(prec - s[1].length + 1).join('0');
			}
			return s.join(dec);
		}

        $("#add_more_btn").on("click", function(e){

            if(temp_coop_id == 68)
            {
                $.ajax({
                        type: 'POST',
                        url: "{{ route('coop.getStations') }}",
                        data: {
                            _token: "{{ csrf_token() }}",
                        },
                        success: function(data){
                            $("#additional_region_select").empty();
                            $.each(data, function(index, region) {
                                $("#additional_region_select").append(
                                    $('<option>', {
                                        value: region.station,
                                        text: region.station
                                    })
                                );
                            });
                        }
                    });
            }
            $("#add_more_btn_fld").css("display", "none");
            $("#hr_line_add").css("display", "block");
            $("#add_more_variety_fld").css("display", "block");
            $("#add_more_value_fld").css("display", "block");
            $("#add_submit_btn_fld").css("display", "block");
        });
		
		$(':input[type="number"]').on('keypress', function (event) {
            var regex = new RegExp("^[a-zA-Z0-9]+$");
            var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
            if (!regex.test(key)) {
                event.preventDefault();
                return alert('Invalid Input : Special characters are not allowed (+,-,.)');
            }
        });

        

        $("#coop_tbl").DataTable({
            "searchHighlight": true,
            "processing": true,
            "serverSide": true,
            "orderMulti": true,
            "order": [],
            "ajax": {
                "url": "{{ route('coop.commitment.table') }}",
                "dataType": "json",
                "type": "POST",
                "data":{
                    "_token": "{{ csrf_token() }}"
                }
            },
            "columns":[
                {"data": "coopName"},
                {"data": "accreditation_no", 'searchable': false },
                {"data": "current_moa"},
                {"data": "action", 'searchable': false }
            ]
        });

        $('#moa_number_modal').on('show.bs.modal', function (e) {
            var coopID = $(e.relatedTarget).data('id');
            var moa_number = $(e.relatedTarget).data('moa');

            $("#coop_moa_number").val(moa_number);
            $("#coop_id_moa").val(coopID);
        });

        $("#update_moa_btn").on("click", function(e){
            var moa_number = $("#coop_moa_number").val();
            var coop_id = $("#coop_id_moa").val();

            $.ajax({
                type: 'POST',
                url: "{{ route('coop.commitment.moa') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    coop_id: coop_id,
                    moa_number: moa_number
                },
                success: function(data){
                    window.location = data;
                }
            });
        });

        $("#deleteAllCommitment").on("click", function(e){
           var coopID = $("#coopID").val();
           $("#deleteAllCommitment").empty().html("<div class='fa fa-refresh fa-spin'></div> Cancelling Transaction...");
           $("#deleteAllCommitment").attr("disabled", "");

            $.ajax({
                type: 'POST',
                url: "{{ route('coop.commitment.cancel') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    coopID: coopID
                },
                success: function(data){
                    window.location = data;
                }
            });
        }); 

        $('#commitmentModal').on('show.bs.modal', function (e) {
			var coopID = $(e.relatedTarget).data('id');
			var coopName = $(e.relatedTarget).data('name');
            var add_moa = $(e.relatedTarget).data('acn');

			$("#coopID").val(coopID);
			$("#coopName").html(coopName);
            $("#add_moa").val("MOA Number: " + add_moa);
            $("#add_moa").attr("disabled", "");
            $("#add_commit_total").val("TOTAL COMMITMENT: ");


            if(coopID == 68)
            {
                $.ajax({
                        type: 'POST',
                        url: "{{ route('coop.getStations') }}",
                        data: {
                            _token: "{{ csrf_token() }}",
                        },
                        success: function(data){
                            $("#tagged_region").empty();
                            $.each(data, function(index, region) {
                                $("#tagged_region").append(
                                    $('<option>', {
                                        value: region.station,
                                        text: region.station
                                    })
                                );
                            });
                        }
                    });
            }
        

            //get seed varities
            $("#seed_variety").empty().append("<option value='0'>Updating please wait...</option>");
            $.ajax({
                type: 'POST',
                url: "{{ route('coop.varities') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    coopID: coopID
                },
                success: function(data){
                    $("#seed_variety").empty().append("<option value='0'>Please select a variety</option>");
                    $("#seed_variety").append(data);
                }
            });

		});

        function removeRecord(data) {      
            var id = jQuery(data).data('id');
            $.ajax({
                type: 'POST',
                url: "{{ route('coop.varities.delete') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    commitmentID: id
                },
                success: function(coop_id){
                    $('#coop_add_tbl').DataTable().clear();
                    $("#coop_add_tbl").DataTable({
                        "bDestroy": true,
                        "autoWidth": false,
                        "searchHighlight": true,
                        "processing": true,
                        "serverSide": true,
                        "orderMulti": true,
                        "order": [],
                        "ajax": {
                            "url": "{{ route('coop.save.table') }}",
                            "dataType": "json",
                            "type": "POST",
                            "data":{
                                "_token": "{{ csrf_token() }}",
                                "coopID": coop_id
                            }
                        },
                        "columns":[
                            {"data": "commitment_variety"},
                            {"data": "variety_bags"},
                            {"data": "region"},
                            {"data": "category"},
                            {"data": "date_add"},
                            {"data": "action"}
                        ]
                    });

                    //get seed varities
                    $("#seed_variety").empty().append("<option value='0'>Updating please wait...</option>");
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('coop.varities') }}",
                        data: {
                            _token: "{{ csrf_token() }}",
                            coopID: coop_id
                        },
                        success: function(data){
                            $("#seed_variety").empty().append("<option value='0'>Please select a variety</option>");
                            $("#seed_variety").append(data);
                        }
                    });
                }
            });
        }

        //remove data from record
        function removeRecordDetails(data) {    
            var yesno = confirm("Set Inactive?");


            if(yesno){

                var id = jQuery(data).data('id');
            $.ajax({
                type: 'POST',
                url: "{{ route('coop.varities.delete.details') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    commitmentID: id
                },
                success: function(coop_id){
                    if(coop_id == "zero_neg_rem"){
                        alert("Action not allowed.");

                        //AJAX TO GET COOPID
                        $.ajax({
                            type: 'POST',
                            url: "{{ route('coop.id') }}",
                            data: {
                                _token: "{{ csrf_token() }}",
                                commitmentID: id
                            },
                            success: function(coop_id_data){
                               //get seed varities
                                $("#seed_variety").empty().append("<option value='0'>Updating please wait...</option>");
                                $.ajax({
                                    type: 'POST',
                                    url: "{{ route('coop.varities') }}",
                                    data: {
                                        _token: "{{ csrf_token() }}",
                                        coopID: coop_id_data
                                    },
                                    success: function(data){
                                        $("#seed_variety").empty().append("<option value='0'>Please select a variety</option>");
                                        $("#seed_variety").append(data);
                                    }
                                });

                                //update total bags displayed
                                $("#coopDetailsTotal").empty().html("Total Commitment: Computing value please wait...");
                                $.ajax({
                                    type: 'POST',
                                    url: "{{ route('coop.commitment.total') }}",
                                    data: {
                                        _token: "{{ csrf_token() }}",
                                        coopDetailsID: coop_id_data
                                    },
                                    success: function(data){
                                        $("#coopDetailsTotal").empty().html("Total Commitment: " + data + " bags");
                                    }
                                });
                            }
                        });

                    }else{
                        $('#coop_detail_tbl').DataTable().clear();
                        $("#coop_detail_tbl").DataTable({
                            "bDestroy": true,
                            "autoWidth": false,
                            "searchHighlight": true,
                            "processing": true,
                            "serverSide": true,
                            "orderMulti": true,
                            "order": [],
                            "ajax": {
                                "url": "{{ route('coop.details.table') }}",
                                "dataType": "json",
                                "type": "POST",
                                "data":{
                                    "_token": "{{ csrf_token() }}",
                                    "coopDetailsID": coop_id
                                }
                            },
                            "columns":[
                                {"data": "commitment_variety"},
                                {"data": "variety_bags"},
                                {"data": "category"},
                                {"data": "status_btn"},
                                {"data": "date_add"},
                                {"data": "action"}
                            ]
                        });
                    }
                    
                    $("#add_more_btn_fld").css("display", "block");
                    $("#hr_line_add").css("display", "none");
                    $("#add_more_variety_fld").css("display", "none");
                    $("#add_more_value_fld").css("display", "none");
                    $("#add_submit_btn_fld").css("display", "none");
                    $("#add_more_value_fld").val("");

                    $("#add_more_totalCommit_fld").css("display", "none");
                    $("#add_more_totalCommit").css("border", "1px solid black");
                    $("#add_more_totalCommit").val("");

                    //get seed varities
                    $("#seed_variety").empty().append("<option value='0'>Updating please wait...</option>");
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('coop.varities') }}",
                        data: {
                            _token: "{{ csrf_token() }}",
                            coopID: coop_id
                        },
                        success: function(data){
                            $("#seed_variety").empty().append("<option value='0'>Please select a variety</option>");
                            $("#seed_variety").append(data);
                        }
                    });

                    //update total bags displayed
                    $("#coopDetailsTotal").empty().html("Total Commitment: Computing value please wait...");
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('coop.commitment.total') }}",
                        data: {
                            _token: "{{ csrf_token() }}",
                            coopDetailsID: coop_id
                        },
                        success: function(data){
                            $("#coopDetailsTotal").empty().html("Total Commitment: " + data + " bags");
                        }
                    });
                }
            });


            }
            


        }

        $("#Add_BTN").on("click", function(e){
            var coopID = $("#coopID").val();
            var seed_variety = $("#seed_variety").val();
            var commitment_value = $("#commitment_value").val();
			var region = $("#tagged_region").val();
			var category = $("#tagged_category").val();

            $("#Add_BTN").attr("disabled", "");
            
            if(seed_variety == '0'){
                alert("Please specify a seed variety");
                $("#Add_BTN").removeAttr("disabled");
            }else{
                $.ajax({
                    type: 'POST',
                    url: "{{ route('coop.commitment.save') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        coopID: coopID,
                        seed_variety: seed_variety,
                        commitment_value: commitment_value,
						region: region,
                        category: category
                    },
                    success: function(data){
                        $("#Add_BTN").removeAttr("disabled");
                        $("#commitment_value").val("");
                        $("#hr_line").css("display", "block");
                        $('#coop_add_tbl').css("display", "inline-table")
                        $('#coop_add_tbl').DataTable().clear();
                        $("#coop_add_tbl").DataTable({
                            "bDestroy": true,
                            "autoWidth": false,
                            "searchHighlight": true,
                            "processing": true,
                            "serverSide": true,
                            "orderMulti": true,
                            "order": [],
                            "ajax": {
                                "url": "{{ route('coop.save.table') }}",
                                "dataType": "json",
                                "type": "POST",
                                "data":{
                                    "_token": "{{ csrf_token() }}",
                                    "coopID": coopID
                                }
                            },
                            "columns":[
                                {"data": "commitment_variety"},
                                {"data": "variety_bags"},
								{"data": "region"},
								{"data": "category"},
                                {"data": "date_add"},
                                {"data": "action"}
                            ]
                        });

                        //compute sub-total of commmitments
                        $("#add_commit_total").empty().val("Computing data please wait...");
                        $.ajax({
                            type: 'POST',
                            url: "{{ route('coop.commitment.sub_total') }}",
                            data: {
                                _token: "{{ csrf_token() }}",
                                coopID: coopID
                            },
                            success: function(data){
                                $("#add_commit_total").val("REQUIRED COMMITMENT: " + data + " bags");
                            }
                        });

                        //get seed varities
                        $("#seed_variety").empty().append("<option value='0'>Updating please wait...</option>");
                        $.ajax({
                            type: 'POST',
                            url: "{{ route('coop.varities') }}",
                            data: {
                                _token: "{{ csrf_token() }}",
                                coopID: coopID
                            },
                            success: function(data){
                                $("#seed_variety").empty().append("<option value='0'>Please select a variety</option>");
                                $("#seed_variety").append(data);
                            }
                        });
                    }
                });
            }
        });

        $("#computeTotalCommitment").on("click", function(e){
            var coopID = $("#coopID").val();
            var total_commitment = $("#total_commitment").val();

            $.ajax({
                type: 'POST',
                url: "{{ route('coop.commitment.save.total') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    coopID: coopID,
                    total_commitment: total_commitment
                },
                success: function(data){
                    if(data == 'no_commit'){
                        alert("Please add a delivery commitment");
                    }else if(data == 'small_commit'){
                        alert("Please ensure that the total commitment of the cooperative is larger than the sub-total of the previously added seed varities.");
                    }else if(data == "zero_commit"){
                        $("#row_id_commitment").css("display", "block");
                        $("#total_commitment").css("border", "1px solid red");
                        alert("Please input the total commitment for the seed cooperative..");
                    }else{
                        alert('Successfully saved commitment data!');
                        window.location = data;
                    }
                }
            });
        });

        $('#coopDetails').on('show.bs.modal', function (e) {
            $("#add_more_btn_fld").css("display", "block");
            $("#hr_line_add").css("display", "none");
            $("#add_more_variety_fld").css("display", "none");
            $("#add_more_value_fld").css("display", "none");
            $("#add_submit_btn_fld").css("display", "none");

			temp_coop_id = $(e.relatedTarget).data('id');
			var coopDetailsID = $(e.relatedTarget).data('id');
			var coopDetailsName = $(e.relatedTarget).data('name');
            var coopDetailsBags = $(e.relatedTarget).data('bags');

            var accreditation_no = $(e.relatedTarget).data('acn');
            var moa_number = $(e.relatedTarget).data('moa');

			$("#coopDetailsID").val(coopDetailsID);

            $("#coopDetailsName").empty().html(coopDetailsName);
            $("#coopDetailsTotal").empty().html("Required Commitment: " + number_format(coopDetailsBags) + " bags");
            $("#coopDetailsNumber").empty().html("Accreditation Number: " + accreditation_no);
            $("#coopDetailsMOA").empty().html("MOA Number: " + moa_number);
			//$("#coopDetailsName").empty().html(coopDetailsName + " <br><span class='custom-header-span'>Total Commitment: " + coopDetailsBags + " bags (20kg/bag)</span><br><span class='custom-header-span'>Accreditation Number: " + accreditation_no + "</span><br><span class='custom-header-span'>MOA Number: " + moa_number + "</span>");

            $('#coop_detail_tbl').DataTable().clear();

            $("#coop_detail_tbl").DataTable({
                "bDestroy": true,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('coop.details.table') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        "coopDetailsID": coopDetailsID
                    }
                },
                "columns":[
                    {"data": "commitment_variety"},
                    {"data": "variety_bags"},
					{"data": "region"},
					{"data": "category"},
                    {"data": "status_btn"},
                    {"data": "date_add"},
                    {"data": "action"}
                ]
            });

            //generate data for seed varities
            $("#add_more_variety").select2();
            $("#add_more_variety").empty().append("<option value='0'>Updating please wait...</option>");
            $.ajax({
                type: 'POST',
                url: "{{ route('coop.varities.details.add') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    coopDetailsID: coopDetailsID
                },
                success: function(data){
                    $("#add_more_variety").empty().append("<option value='0'>Please select a variety</option>");
                    $("#add_more_variety").append(data);
                }
            });
			
			//load adjustments
            $("#adjustment_timeline_content").empty();
            $.ajax({
                type: 'POST',
                url: "{{ route('coop.adjustment.logs') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    coop_id: coopDetailsID
                },
                success: function(data){
                    var timeline_str = "";
                    if(data != 0){
                        jQuery.each(data, function(index, array_value){
                            timeline_str = timeline_str + "<li>";
                            timeline_str = timeline_str + "<div class='block'>";
                            timeline_str = timeline_str + "<div class='tags'>";
                            timeline_str = timeline_str + "<a href='' class='tag'>";
                            timeline_str = timeline_str + "<span>"+array_value["date_recorded"]+"</span>";
                            timeline_str = timeline_str + "</a>";
                            timeline_str = timeline_str + "</div>";
                            timeline_str = timeline_str + "<div class='block_content'>";
                            timeline_str = timeline_str + "<h2 class='title'>";
                            timeline_str = timeline_str + "<a>Adjustment Details</a>";
                            timeline_str = timeline_str + "</h2>";
                            timeline_str = timeline_str + "<div class='byline'>";
                            timeline_str = timeline_str + "<span>Breakdown of adjustments for each covered region</a>";
                            timeline_str = timeline_str + "</div>";
                            timeline_str = timeline_str + "</div>";
                            timeline_str = timeline_str + "<p class='excerpt'>"+array_value["seed_list"]+"</p>";
                            timeline_str = timeline_str + "</div>";
                            timeline_str = timeline_str + "</div>";
                            timeline_str = timeline_str + "</li>";
                        });

                        $("#adjustment_timeline_content").append(timeline_str);
                        
                    }else{
                        //alert("No adjustments detected for this seed coop's delivery commitment");
                    }
                }
            });
		});

        $("#add_submit_btn").on("click", function(e){
            var coopDetailsID = $("#coopDetailsID").val();
            var seed_variety = $("#add_more_variety").val();
            var seed_value = $("#add_more_value").val();
			var region = $("#additional_region_select").val();
            var category = $('#additional_category_select').val();
            
            if(seed_variety == "0"){
                alert("Please select a seed variety.");
            }else{
                $.ajax({
                    type: 'POST',
                    url: "{{ route('coop.add_more.submit') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        coopDetailsID: coopDetailsID,
                        seed_variety: seed_variety,
                        seed_value: seed_value,
						region: region,
                        category: category
                    },
                    success: function(data){
                        $('#coop_detail_tbl').DataTable().clear();
                        $("#coop_detail_tbl").DataTable({
                            "bDestroy": true,
                            "searchHighlight": true,
                            "processing": true,
                            "serverSide": true,
                            "orderMulti": true,
                            "order": [],
                            "ajax": {
                                "url": "{{ route('coop.details.table') }}",
                                "dataType": "json",
                                "type": "POST",
                                "data":{
                                    "_token": "{{ csrf_token() }}",
                                    "coopDetailsID": coopDetailsID
                                }
                            },
                            "columns":[
                                {"data": "commitment_variety"},
                                {"data": "variety_bags"},
								{"data": "region"},
                                {"data": "status_btn"},
                                {"data": "category"},
                                {"data": "date_add"},
                                {"data": "action"}
                            ]
                        });

                        $("#add_more_btn_fld").css("display", "block");
                        $("#hr_line_add").css("display", "none");
                        $("#add_more_variety_fld").css("display", "none");
                        $("#add_more_value_fld").css("display", "none");
                        $("#add_submit_btn_fld").css("display", "none");
                        $("#add_more_value").val("");

                        //generate data for seed varities
                        $("#add_more_variety").select2();
                        $("#add_more_variety").empty().append("<option value='0'>Updating please wait...</option>");
                        $.ajax({
                            type: 'POST',
                            url: "{{ route('coop.varities.details.add') }}",
                            data: {
                                _token: "{{ csrf_token() }}",
                                coopDetailsID: coopDetailsID
                            },
                            success: function(data){
                                $("#add_more_variety").empty().append("<option value='0'>Please select a variety</option>");
                                $("#add_more_variety").append(data);
                            }
                        });

                        //update total bags displayed
                        $("#coopDetailsTotal").empty().html("Total Commitment: Computing value please wait...");
                        $.ajax({
                            type: 'POST',
                            url: "{{ route('coop.commitment.total') }}",
                            data: {
                                _token: "{{ csrf_token() }}",
                                coopDetailsID: coopDetailsID
                            },
                            success: function(data){
                                $("#coopDetailsTotal").empty().html("Total Commitment: " + data + " bags");
                            }
                        });
                    }
                });
            }	
        });
		
		//01-29-2021
        $('#commitmentAdjustmentModal').on('show.bs.modal', function (e) {
			var coop_id = $(e.relatedTarget).data('id');
			var coop_name = $(e.relatedTarget).data('name');
            var accreditation_no = $(e.relatedTarget).data('acn');
            var moa_number = $(e.relatedTarget).data('moa');
			var available_balance = $(e.relatedTarget).data('balance');

            $("#coopAdjustmentsName").empty().html(coop_name);
            $("#coopAdjustmentsNumber").empty().html("Accreditation Number: " + accreditation_no);
            $("#coopAdjustments MOA").empty().html("MOA Number: " + moa_number);
            $("#coopAdjustment_id").val(coop_id);
			$("#coopAdjustmentsBalance").empty().html("Available Volume for Adjustment: " + number_format(available_balance));
			
			$("#coopAdjustment_balance").val(available_balance);

            //get regions convered by seed cooperative
            $("#col3RegionList").empty();
            var col3RegionList_str = "";
            $.ajax({
                type: 'POST',
                url: "{{ route('coop.adjustment.regions') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    coop_id: coop_id
                },
                success: function(data){
                    if(data != 0){
                        jQuery.each(data, function(index, array_value){
                            col3RegionList_str = col3RegionList_str + "<label class='check_box_container'>"+array_value["region_name"];
                            col3RegionList_str = col3RegionList_str + "<input type='radio' name='radio-regions' value='"+array_value["region_name"]+"' onclick='load_region_varities()'>";
                            col3RegionList_str = col3RegionList_str + "<span class='checkmark'></span>";
                            col3RegionList_str = col3RegionList_str + "</label>";
                        });
                        //col3RegionList_str = col3RegionList_str + "<button id='load_varities_btn'  class='btn btn-success btn-block form-control'><i class='fa fa-database'></i> REQUEST DATA</button>";
                        //col3RegionList_str = col3RegionList_str + "<div style='height:150px;'></div>"                        
                        $("#col3RegionList").append(col3RegionList_str);
                    }else{
                        alert("No regional allocation registered for this seed cooperative!");
                    }
                }
            });        
		});
		
		function load_region_varities(){
            var region = document.querySelector('input[name="radio-regions"]:checked').value;
            var coop_id = $("#coopAdjustment_id").val();

            $("#col9VarietyList").empty().append("Fetching data from the database...");
            var col9VarietyList_str = "";
            $.ajax({
                type: 'POST',
                url: "{{ route('coop.adjustment.varities') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    coop_id: coop_id,
                    region: region
                },
                success: function(data){
                    jQuery.each(data, function(index, array_value){
                        col9VarietyList_str = col9VarietyList_str + "<div class='col-md-9' style='border-left: 1px solid #cccccc;'>";
                        col9VarietyList_str = col9VarietyList_str + "<div class='form-group'>";
                        col9VarietyList_str = col9VarietyList_str + "<div class='input-group'>";
                        col9VarietyList_str = col9VarietyList_str + "<input id='rb_display_"+array_value["seed_allocation_id"]+"' type='text' class='form-control' value='"+array_value["seed_variety"]+" - "+array_value["seed_value"]+" bag(s)' style='height:45px;' disabled readonly>";
                        col9VarietyList_str = col9VarietyList_str + "<span class='input-group-btn'>";
                        

                    if(array_value["is_editor"]==="true"){
                           col9VarietyList_str = col9VarietyList_str + "<button  id='rb_add_btn_"+array_value["seed_allocation_id"]+"' onclick='addallocation("+array_value["seed_allocation_id"]+")' type='button' class='btn btn-default' style='margin:0;font-size: 21px;'>+</button>";
                        col9VarietyList_str = col9VarietyList_str + "<button  id='rb_minus_btn_"+array_value["seed_allocation_id"]+"' onclick='deductAllocation("+array_value["seed_allocation_id"]+")' type='button' class='btn btn-default' style='margin:0;font-size: 21px;'>-</button>"; 
                    }



                        
                        col9VarietyList_str = col9VarietyList_str + "</span>";
                        col9VarietyList_str = col9VarietyList_str + "</div>";
                        col9VarietyList_str = col9VarietyList_str + "</div>";
                        col9VarietyList_str = col9VarietyList_str + "</div>";
                    });

                    $("#col9VarietyList").empty().append(col9VarietyList_str);
                }
            });
        }
		
		function addallocation(regional_allocation_id){
			var available_balance = $("#coopAdjustment_balance").val();
			if(available_balance > 0){
				var input_value = prompt("Please input the volume to add for the selected variety");
				//var input_value = $("#rb_input_"+allocation_id).val();
				
				$.ajax({
					type: 'POST',
					url: "{{ route('coop.adjustment.add') }}",
					dataType: 'json',
					cache: false,
					data: {
						_token: "{{ csrf_token() }}",
						 allocation_id: regional_allocation_id,
						 input_value: input_value
					},
					success: function(data){
						if(data["return_msg"] == "add_success"){                        
							alert("allocation updated");
							load_region_varities();
							$("#coopAdjustmentsBalance").empty().html("Available Volume for Adjustment: " + number_format(data["return_balance"]));

						}else if(data["return_msg"] == "add_error" && parseInt(data["return_value"]) ==0 ){
							alert("allocation error");
						}else{
							alert(data["return_msg"])
						}
					}
				});
			}else{
				alert("There are is no available volume for adjustment");
			}
        }

        function deductAllocation(allocation_id){
            var input_value = prompt("Please input the volume to deduct for the selected variety");
            
            $.ajax({
                type: 'POST',
                url: "{{ route('coop.adjustment.deduct') }}",
                dataType: 'json',
                cache: false,
                data: {
                    _token: "{{ csrf_token() }}",
                    allocation_id: allocation_id,
                    input_value: input_value
                },
                success: function(data){
                    $("#rb_display_"+allocation_id).empty().val("computing new data...");
                    if(data["return_msg"] == "deduct_success"){                        
                        alert("allocation updated");
                        load_region_varities();
						$("#coopAdjustmentsBalance").empty().html("Available Volume for Adjustment: " + number_format(data["return_balance"]));

                    }else if(data["return_msg"] == "deduct_error" && parseInt(data["return_value"]) ==0 ){
                        alert("allocation error");
                    }
                }
            });
        }

        $("#update_tf_btn").on("click", function(e){
            var tar_eff = $("input[name=target_efficiency]:checked").val();
            var coop_id = $("#coop_id_target").val();

            $.ajax({
                type: 'POST',
                url: "{{ route('coop.commitment.efficiency') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    coop_id: coop_id,
                    tar_eff: tar_eff
                },
                success: function(data){
                    window.location = data;
                }
            });
        });

        $('#target_efficiency_modal').on('show.bs.modal', function (e) {
            var coopID = $(e.relatedTarget).data('id');
            var tar_eff = $(e.relatedTarget).data('eff');
           
            $("input[name=target_efficiency][value="+ tar_eff +"]").prop('checked', true);
            $("#coop_id_target").val(coopID);
        });

    </script>
@endpush

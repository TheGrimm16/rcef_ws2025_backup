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
                    <h2>Commited Deliveries per Seed Cooperatives</h2>                    
                    <div class="clearfix"></div>
                    <button type="button" class="btn btn-info" id="btn_rla"><i class="fa fa-file"></i> download RLA</button>
                </div>
                {{-- <div class="x_content form-horizontal form-label-left">
                    <br/>
                        <table class="table table-hover table-striped table-bordered" id="coop_tbl">
                            <thead>
                                <th>Cooperative</th>
                                <th>Accreditation Number</th>
                                <th>MOA Number</th>
                                <th>Action</th>
                            </thead>
                        </table>
                </div> --}}
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
												{{-- @foreach($region_list as $region)
													<option value="{{$region->regionName}}">{{$region->regionName}}</option>
												@endforeach --}}
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
									<div class="col-md-6">
										<select name="add_more_variety" id="add_more_variety" style="width: 100%;" class="form-control">

										</select>
									</div>
									<div class="col-md-4" style="display:none" id="add_more_value_fld">
										<input type="number" name="add_more_value" id="add_more_value" class="form-control" placeholder="Please enter a value">
									</div>
									<div class="col-md-2" style="display:none" id="add_submit_btn_fld">
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
@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

    <script>

        $("#seed_variety").select2();
		

        $("#btn_rla").on("click", function(e){
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

        

    </script>
@endpush

<div id="add_farmer_modal" class="modal fade " role="dialog">
    <div class="modal-dialog modal-lg" style="width:70%;">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-body">
                <div class="col-md-6">
                    <div class="form-horizontal form-label-left">
                        <label style=" margin-top: 15px;"><span style="color: red">*</span> You can check duplicate RSBSA control # here: <strong><a style="color:red" target="_blank" href="checking" style="font:2vw"> Click here</a></strong></label><br>
                        <label><span style="color: red">*</span> Required fields</label>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3"><span style="color: red">*</span> RSBSA Stub / Control # (00-00-00-000-000000)</label>

                            <div class="input-group">
                                <input  value="{{Session::get('prv')}}-" data-mask="00-00-00-000-000000-00" type="text" class="form-control simple-field-data-mask" name="rsbsa_control_no" id="rsbsa_control_no" placeholder="00-00-000-000000">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-primary" id="search_rsbsa_no">Search</button>
                                </span>
                            </div>
                        </div>
                        <!--
                                                <div class="form-group">
                                                    <label class="control-label col-md-3 col-sm-3 col-xs-3">On List?</label>
                                                    <div class="col-md-9 col-sm-9 col-xs-9">
                                                        <div class="checkbox">
                                                            <label><input type="checkbox" name="on_list" id="on_list"></label>
                                                        </div>
                                                    </div>
                                                </div>-->
                        <div class="disabled_forms" style="pointer-events: none;opacity: .6">
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3"><span style="color: red">*</span> Actual farm Area (ha)</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <input  type="number" class="form-control" name="actual_area" id="actual_area" placeholder="Farm Area">
                                </div>
                            </div>
                            <div id="select_variety_input">
                                <div class="form-group">    
                                    <label class="control-label col-md-3        "><span style="color: red">*</span> Select Variety</label>
                                    <div class="col-md-9">
                                        @foreach ($available_seeds as $item)
                                        <div class="radio">
                                            <label style="margin-left: 10px;">
                                                <input type="radio" name="preferred_variety2" value="{{$item->seedVariety}}" class="flat"> {{$item->seedVariety}}
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>

								<?php 
								//if($check_direct_seeded>0){?>
									<div class="form-group">
										<label class="control-label col-md-3 col-sm-3 col-xs-3">Change allocation (bags)?</label>
										<div class="col-md-9 col-sm-9 col-xs-9">
											<div class="checkbox">
												<label><input type="checkbox" name="change_bag" id="change_bag"> Yes <span style="color: red"> *Should not exceed the limit (12 bags or based from actual area)</span></label>
											</div>
										</div>
									</div>
								<?php//}?>
								<div class="form-group change_class" style="display:none;"">
									<label class="control-label col-md-3 col-sm-3 col-xs-3"><span style="color: red">*</span> # of bags</label>
									<div class="col-md-9 col-sm-9 col-xs-9">
										<input  type="number" class="form-control" max="12" maxlength="2" name="bags_distributed" id="bags_distributed" placeholder="# of bags" value="1">
									</div>
								</div>
                                <audio id="qr_audio">
                                    <source src="{{asset('public/sounds/Beep.mp3')}}" type="audio/mpeg">
                                </audio>

                                <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-3"><span style="color: red">*</span> QR Code</label>
                                    <div class="col-md-9 col-sm-9 col-xs-9">
                                        <input type="text" class="form-control" name="qr_code2" id="qr_code2" placeholder="QR Code">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-3"><span style="color: red">*</span> First name</label>
                                    <div class="col-md-9 col-sm-9 col-xs-9">
                                        <input type="text" class="form-control" name="firstname" id="firstname" placeholder="First name">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-3">Middle name</label>
                                    <div class="col-md-9 col-sm-9 col-xs-9">
                                        <input type="text" class="form-control" name="middlename" id="middlename" placeholder="Middle name">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-3"><span style="color: red">*</span> Last name</label>
                                    <div class="col-md-9 col-sm-9 col-xs-9">
                                        <input type="text" class="form-control" name="lastname" id="lastname" placeholder="Last name">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-3">Suffix/Extension</label>
                                    <div class="col-md-9 col-sm-9 col-xs-9">
                                        <input type="text" class="form-control" name="extname" id="extname" placeholder="Suffix/Extension">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-3"><span style="color: red">*</span> Sex</label>
                                    <div class="col-md-9 col-sm-9 col-xs-9">
                                        <select name="farmer_gender" id="farmer_gender" class="form-control" data-parsley-min="1">
                                            <option value="">Please select a gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Femal">Female</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-3"><span style="color: red">*</span> Birth date (yyyy-mm-dd)</label>
                                    <div class="col-md-9 col-sm-9 col-xs-9">
                                        <input  type="text" class="form-control  simple-field-data-mask" name="birthdate" id="birthdate" placeholder="yyyy-mm-dd" data-mask="0000-00-00">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="disabled_forms" style="pointer-events: none;opacity: .6">
                        <div class="form-horizontal form-label-left">
                            <label><span style="color: red">*</span> Required fields</label><br>
                            <label><span style="color: red">*</span> Mother's Maiden name</label>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3"><span style="color: red">*</span> First name</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <input  type="text" class="form-control" name="mfname" id="mfname" placeholder="Firstname">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Middle name</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <input  type="text" class="form-control" name="mmname" id="mmname" placeholder="Middlename">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3"><span style="color: red">*</span> Last name</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <input  type="text" class="form-control" name="mlname" id="mlname" placeholder="Lastname">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Suffix/Extension</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <input  type="text" class="form-control" name="mextname" id="mextname" placeholder="Suffix">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Phone number</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <input  data-mask="0000-000-0000" type="text" class="form-control simple-field-data-mask" name="phone" id="phone" placeholder="0921-000-0000">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Is representative?</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <div class="checkbox">
                                        <label><input type="checkbox" name="with_rep" id="with_rep"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group rep_class" style="display:none;"> 
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Type of ID</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="valid_id" id="valid_id" class="form-control" data-parsley-min="1">
                                        <option value="driver">Driver's License</option>
                                        <option value="ofw">OFW</option>
                                        <option value="philhealth">PhilHealth</option>
                                        <option value="passport">Passport</option>
                                        <option value="postal">Postal</option>
                                        <option value="prc">PRC</option>
                                        <option value="senior">Senior Citizen</option>
                                        <option value="sss">SSS</option>
                                        <option value="tin">TIN Card</option>
                                        <option value="voters">Voter's</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group rep_class" style="display:none;">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Representative Relationship</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="relationship" id="relationship" class="form-control" data-parsley-min="1">
                                        <option value="Spouse">Spouse</option>
                                        <option value="Sibling">Sibling</option>
                                        <option value="SonDaughter">Son/Daughter</option>
                                        <option value="Farmworker">Farm worker</option>
                                        <option value="Friend">Friend</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            
                                <br>
                            <label><span style="color: red">Farmer's Performance</span> </label><br>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Variety used</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="variety_used" id="variety_used" class="form-control select2class" data-parsley-min="1" style="width:100%;">
                                        <option value="">Please select variety</option>
                                        @foreach($seed_varieties as $item)
                                        <option value="{{$item->variety_name}}">{{$item->variety_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3"> Yield</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <input  type="text" class="form-control" name="yields" id="yields" >
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3"> Seed usage</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <input  type="text" class="form-control" name="seed_usage" id="seed_usage" >
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Preferred Variety </label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="preferred_variety" id="preferred_variety" class="form-control select2class" data-parsley-min="1" style="width:100%;">
                                        <option value="">Please select variety</option>
                                        @foreach($seed_varieties as $item)
                                        <option value="{{$item->variety_name}}">{{$item->variety_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3"> Area planted (ha)</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <input  type="number" class="form-control" name="area_planted" id="area_planted" >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 right">
                    <button type="button" class="btn btn-lg btn-success" id="save_farmer" style="float:right"><i class="fa fa-check"></i> Save</button>
                    <button type="button" class="btn btn-danger" id="cancel_transaction" style="float:right">Cancel Transaction</button>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    $('#with_rep').on('click', function () {
        if (document.getElementById('with_rep').checked == true) {
            $(".rep_class").show();
        } else {
            $(".rep_class").hide();
        }
    });

    $('#change_bag').on('click', function () {
        if (document.getElementById('change_bag').checked == true) {
            $(".change_class").show();
        } else {
            $(".change_class").hide();
        }
    });
    $('#search_rsbsa_no').on('click', function () {
        var rsbsa_control_no = $("#add_farmer_modal #rsbsa_control_no").val();
        if (rsbsa_control_no) {
            $.ajax({
                type: 'POST',
                url: "{{ route('releasing.search_rsbsa_no') }}",
                data: {
                    rsbsa_control_no: rsbsa_control_no,
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                success: function (source) {
                    $("#add_farmer_modal .disabled_forms").css("pointer-events", "auto");
                    $("#add_farmer_modal .disabled_forms").css("opacity", "1");
                    if (source != "Not found" && source != "Already served") {
                        alert(source['firstName'] + " " + source['lastName'] + " has found in the database");
                        $("#add_farmer_modal #firstname").val(source['firstName']);
                        $("#add_farmer_modal #middlename").val(source['midName']);
                        $("#add_farmer_modal #lastname").val(source['lastName']);
                        $("#add_farmer_modal #extname").val(source['extName']);
                        $("#add_farmer_modal #farmer_gender").val(source['sex']);
                        $("#add_farmer_modal #birthdate").val(source['birthdate']);
                        $("#add_farmer_modal #mfname").val(source['mother_fname']);
                        $("#add_farmer_modal #mmname").val(source['mother_mname']);
                        $("#add_farmer_modal #mlname").val(source['mother_lname']);
                        $("#add_farmer_modal #phone").val(source['phone']);
                        $("#add_farmer_modal #mextname").val(source['mother_suffix']);

                        if (source['firstName'] != "") {
                            $("#add_farmer_modal #firstname").prop('disabled', true);
                            $("#add_farmer_modal #middlename").prop('disabled', true);
                            $("#add_farmer_modal #lastname").prop('disabled', true);
                        } else {
                            $("#add_farmer_modal #firstname").prop('disabled', false);
                            $("#add_farmer_modal #middlename").prop('disabled', false);
                            $("#add_farmer_modal #lastname").prop('disabled', false);
                        }

                    } else {
                        if (source == "Not found") {
                            alert("RSBSA Control # not found. This will be new entry");
                            $("#add_farmer_modal #firstname").val("");
                            $("#add_farmer_modal #middlename").val("");
                            $("#add_farmer_modal #lastname").val("");
                            $("#add_farmer_modal #firstname").prop('disabled', false);
                            $("#add_farmer_modal #middlename").prop('disabled', false);
                            $("#add_farmer_modal #lastname").prop('disabled', false);
                            $("#add_farmer_modal #phone").prop('disabled', false);
                            $("#add_farmer_modal #extname").val("");
                            $("#add_farmer_modal #farmer_gender").val("");
                            $("#add_farmer_modal #birthdate").val("");
                            $("#add_farmer_modal #mfname").val("");
                            $("#add_farmer_modal #mmname").val("");
                            $("#add_farmer_modal #mlname").val("");
                            $("#add_farmer_modal #mextname").val("");
                        } else {
                            alert("RSBSA Control # already has pending status");
                        }
                    }
                }
            });
        }
    });

    $("#add_farmer_modal #rsbsa_control_no").keydown(function (e) {
        if (e.which == 13) {
            $("#search_rsbsa_no").trigger("click");
        }
    });
    $(".select2class").select2();
</script>
@endpush

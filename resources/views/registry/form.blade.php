<?php $registry_side = "active"; $registry_form="active"?>

@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/parsely.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <style>
    ul.parsley-errors-list {
        list-style: none;
        color: red;
        padding-left: 0;
        display: none !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 26px;
        position: absolute;
        top: 5px;
        right: 1px;
        width: 20px;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #a7acb5;
        color: black;
    }
  </style>
@endsection

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>

        <form action="{{ route('rcef.registry.save') }}" method="POST" id="registryForm" data-parsley-validate="">
        {!! csrf_field() !!}
        <div class="clearfix"></div>

            @include('layouts.message')

            <div class="col-md-6 col-sm-12 col-xs-12">
                <!-- farmer profile -->
                <div class="x_panel">
                <div class="x_title">
                    <h2>1. Farmer Profile</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <br/>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">First Name <span>*</span></label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="first_name" id="first_name" placeholder="First Name" data-parsley-required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Middle Name <span>*</span></label>
                            <div class="col-md-4 col-sm-4 col-xs-4">
                                <input type="text" class="form-control" name="middle_name" id="middle_name" placeholder="Middle Name" data-parsley-required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Last Name <span>*</span></label>
                            <div class="col-md-6 col-sm-6 col-xs-6">
                                <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Last Name" data-parsley-required>
                            </div>
                            <div class="col-md-3 col-sm-3 col-xs-3">
                                <input type="text" class="form-control" name="suffix_name" placeholder="Suffix">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Sex<span>*</span></label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <select name="farmer_gender" id="farmer_gender" class="form-control" data-parsley-min="1">
                                    <option value="0">Please select a gender</option>
                                    <option value="1">Male</option>
                                    <option value="2">Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Contact # <span id="contact_req_con">*</span></label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="contact_number" id="contact_number" data-inputmask="'mask': '9999-999-9999'" placeholder="XXXX-XXX-XXXX" data-parsley-required>
                                <span class="fa fa-phone form-control-feedback right" aria-hidden="true"></span>

                                <div class="checkbox">
                                    <label style="padding-left: 0;">
                                    <input type="checkbox" id="contact_box" class="flat"> Waive contact number requirement
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Date of Birth <span>*</span></label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                            <input type="text" class="form-control" name="birth_date" id="birth_date" data-inputmask="'mask': '99/99/9999'" placeholder="MM/DD/YYYY" data-parsley-required>
                            <span class="fa fa-calendar form-control-feedback right" aria-hidden="true"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">RSBSA Stub / Control #</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="rsbsa_stub_control" id="rsbsa_stub_control" placeholder="RSBSA Stub / Control #">
                            </div>
                        </div>
                </div>
                </div><br>
                <!-- /farmer profile -->

                <!-- farm details -->
                <div class="x_panel">
                <div class="x_title">
                    <h2>2. Farm Details</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <br/>
                        <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-3">Farm Area <span>*</span></label>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <input type="number" class="form-control" name="farm_area" id="farm_area" placeholder="Total Farm Area: including rice & other crops..." data-parsley-required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-3">Rice Area <span>*</span></label>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <input type="number" class="form-control" name="rice_area" id="rice_area" placeholder="Rice Area" data-parsley-required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-3">Farmer Status <span>*</span></label>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <select name="tenurial_status" id="tenurial_status" class="form-control" data-parsley-min="1">
                                <option value="0">Please select a tenurial status</option>
                                <option value="1">Owner</option>
                                <option value="2">Lease</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-3">Tenurial Type <span>*</span></label>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <select name="tenurial_type" id="tenurial_type" class="form-control" data-parsley-min="1">
                                <option value="0">Please select a tenurial type</option>
                                @foreach($registry_farmer_roles as $farmer_roles)
                                    <option value="{{ $farmer_roles->id }}">{{ $farmer_roles->farmer_role_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="ln_solid"></div>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-3">Province <span>*</span></label>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <select name="province" id="province" class="form-control" data-parsley-min="1">
                                <option value="0">Please select a proivince</option>
                                @foreach ($geo_provinces as $province)
                                        <option value="{{ $province->provCode }}">{{ $province->provDesc }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-3">Municipality <span>*</span></label>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <select name="municipality" id="municipality" class="form-control" data-parsley-min="1">
                                <option value="0">Please select a municipality</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-3">Barangay</label>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <input type="text" class="form-control" id="brgy" name="brgy" placeholder="Barangay">
                        </div>
                    </div>
                </div>
                </div>
                <!-- /farm details -->
            </div>


            <div class="col-md-6 col-sm-6 col-xs-6">
                <!-- farm performance -->
                <div class="x_panel">
                    <div class="x_title">
                    <h2>3. Farm Performance</h2>
                    <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                    <br/>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Variety Used <span>*</span></label>
                            <div class="col-md-4 col-sm-4 col-xs-4">
                                <select name="variety_used_prefix" id="variety_used_prefix" class="form-control">
                                    <option value="NSIC Rc">NSIC Rc</option>
                                    <option value="Hybrid">Hybrid</option>
                                    <option value="PSB Rc">PSB Rc</option>
                                    <option value="IR">IR</option>
                                </select>
                            </div>
                            <div class="col-md-5 col-sm-5 col-xs-5">
                                <input type="text" class="form-control" name="variety_used" id="variety_used" placeholder="Variety used last season" data-parsley-required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Seed Usage <span>*</span></label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="seed_usage" id="seed_usage" placeholder="Used seeds last season" data-parsley-required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Yield <span>*</span></label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="yield" id="yield" placeholder="Yield / Harvest" data-parsley-required>
                            </div>
                        </div>

                        <div class="form-group" style="display:none;">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Preferred Variety</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="preferred_variety" id="preferred_variety" placeholder="Preferred Variety Next Season" data-parsley-required>
                            </div>
                        </div>
                    </div>
                    </div><br>
                <!-- /farm performance -->

                <!-- farmer affiliation -->
                <div class="x_panel">
                    <div class="x_title">
                    <h2>4. Affiliation</h2>
                    <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                    <br/>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Type <span>*</span></label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <select name="affiliation_type" id="affiliation_type" class="form-control" style="display:block" data-parsley-min="1">
                                    <option value="0">Please select an affiliation type</option>
                                    <option value="1">IA - Irrigators Assocation</option>
                                    <option value="2">COOP - Farmer's Cooperative</option>
                                    <option value="3">SWISA - Small Water Irrigation Ssystem Association</option>
                                    <option value="4">Others</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group" id="affiliation_type_others_con" style="display:none" >
                            <label class="control-label col-md-3 col-sm-3 col-xs-3"></label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="affiliation_type_others" id="affiliation_type_others" placeholder="Please specify...">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Name <span>*</span></label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="affiliation_name" id="affiliation_name" placeholder="Affiliation Name" data-parsley-required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Accreditation <span>*</span></label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <select name="farm_accreditation" id="farm_accreditation" class="form-control" data-parsley-min="1">
                                    <option value="0">Please select an accreditation type</option>
                                    <option value="1">01 - None</option>
                                    <option value="2">02 - SEC</option>
                                    <option value="3">03 - CDA</option>
                                    <option value="4">04 - DOLE</option>
                                    <option value="5">05 - OTHERS</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group" style="display:none" id="farm_accreditation_others_con">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3"></label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="farm_accreditation_others" id="farm_accreditation_others" placeholder="Please specify...">
                            </div>
                        </div>

                        <div class="ln_solid"></div>

                        <div class="form-group">
                            <div class="col-md-9 col-md-offset-3">
                                <input type="reset" class="btn btn-round btn-danger" value="Reset Fields">
                                <input type="submit" class="btn btn-round btn-success" value="save & Validate">
                            </div>
                        </div>

                    </div>
                </div>
                </div>
                <!-- /farmer affiliation -->
        </div>
        </form>



    </div>
@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>

    <script>
        $("#province").select2();

        $('#contact_box').on('ifChanged', function(event) {
            if(event.target.checked == true && event.target.value == 'on'){
                $("#contact_number").val("");
                $("#contact_req_con").css("display", "none");
                $("#contact_number").attr('disabled', 'true');
                $("#contact_number").removeAttr('data-parsley-required');
            }else{
                $("#contact_number").val("");
                $("#contact_req_con").css("display", "initial");
                $("#contact_number").removeAttr('disabled');
                $("#contact_number").attr('data-parsley-required', '');
            }
        });

        $("#affiliation_type").on("change", function(e){
            if($(this).val() == 4){
                $("#affiliation_type_others_con").css("display", "block");
            }else{
                $("#affiliation_type_others_con").css("display", "none");
            }
        });

        $("#farm_accreditation").on("change", function(e){
            if($(this).val() == 5){
                $("#farm_accreditation_others_con").css("display","block");
            }else{
                $("#farm_accreditation_others_con").css("display","none");
            }
        });

        $("#province").on("change", function(e){
            var province = $(this).val();
            $("#municipality").empty().append("<option value='0'>Loading municipalities please wait...</option>");

            $.ajax({
                type: 'POST',
                url: "{{ route('api.municipality') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    province: province
                },
                success: function(data){
                    $("#municipality").empty().append("<option value='0'>Please select a municipality</option>");
                    $("#municipality").append(data);
                    $("#municipality").select2();
                }
            });
        });

        $("#registryForm").submit(function(){
            //alert($("#contact_box").prop('checked'));\
            if($("#contact_box").prop('checked') == false && $("#contact_number").val() == ''){
                error = 1;
                //error_msg = error_msg + '<li>The `Contact Number` field is required</li>';
                alert('Please remember to specify the `Contact Number`');
            }else{
                error = 0;
            }
        });
    </script>
@endpush

@extends('layouts.index')

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>
        <div class="page-title">
            <div class="title_left">
              <h3>Farmer Registration</h3>
            </div>
        </div>

        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-6">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Farmer Profile</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <form id="farmer_registration_form">
                            <audio id="qr_audio">
                                <source src="{{asset('public/sounds/Beep.mp3')}}" type="audio/mpeg">
                            </audio>

                            <div class="form-group">
                                <label class="control-label col-md-3">Search Farmer</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="search" id="search_farmer_rsbsa" placeholder="RSBSA Stub / Control #" autocomplete="off">
                                </div>
                            </div>

                            <input type="hidden" id="farmer_id">

                            <div class="form-group">
                                <label class="control-label col-md-3">QR Code</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="distribution_id" id="distribution_id" placeholder="QR Code">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3"></label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <video id="preview" style="width: 100%;"></video>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">First Name</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <input type="text" class="form-control" name="first_name" id="first_name" placeholder="First Name" data-parsley-required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Middle Name</label>
                                <div class="col-md-6 col-sm-6 col-xs-6">
                                    <input type="text" class="form-control" name="middle_name" id="middle_name" placeholder="Middle Name" data-parsley-required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Last Name</label>
                                <div class="col-md-6 col-sm-6 col-xs-6">
                                    <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Last Name" data-parsley-required>
                                </div>
                                <div class="col-md-3 col-sm-3 col-xs-3">
                                    <input type="text" class="form-control" name="suffix_name" id="suffix_name" placeholder="Suffix">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Sex</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="farmer_gender" id="farmer_gender" class="form-control" data-parsley-min="1">
                                        <option value="0">Please select a gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Date of Birth</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="birth_date" id="birth_date" data-inputmask="'mask': '99/99/9999'" placeholder="MM/DD/YYYY" data-parsley-required>
                                <span class="fa fa-calendar form-control-feedback right" aria-hidden="true"></span>
                                </div>
                            </div>

                            <input type="hidden" id="region">

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Province</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="province" id="province" class="form-control" data-parsley-min="1">
                                        <option value="0">Please select a province</option>
                                        @foreach ($provinces_list as $province)
                                                <option value="{{ $province->provDesc }}">{{ $province->provDesc }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Municipality</label>
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

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Farm Area (ha)</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <input type="text" class="form-control" id="farm_area" name="farm_area" placeholder="Farm Area" readonly>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Affiliation Type</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="affiliation_type" id="affiliation_type" class="form-control">
                                        <option value="0">Please select an affiliation type</option>
                                        <option value="IA">IA - Irrigators Assocation</option>
                                        <option value="COOP">COOP - Farmer's Cooperative</option>
                                        <option value="SWISA">SWISA - Small Water Irrigation Ssystem Association</option>
                                        <option value="Others">Others</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Affiliation Name</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <input type="text" class="form-control" name="affiliation_name" id="affiliation_name" placeholder="Affiliation Name">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Affiliation Accreditation</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="affiliation_accreditation" id="affiliation_accreditation" class="form-control">
                                        <option value="0">Please select an accreditation type</option>
                                        <option value="None">01 - None</option>
                                        <option value="SEC">02 - SEC</option>
                                        <option value="CDA">03 - CDA</option>
                                        <option value="DOLE">04 - DOLE</option>
                                        <option value="OTHERS">05 - OTHERS</option>
                                    </select>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Farm Performance</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Area Planted (ha)</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <input type="text" class="form-control" name="area_planted" id="area_planted" placeholder="Area Planted">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Variety Used</label>
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <select name="variety_used_prefix" id="variety_used_prefix" class="form-control">
                                        <option value="NSIC Rc">NSIC Rc</option>
                                        <option value="Hybrid">Hybrid</option>
                                        <option value="PSB Rc">PSB Rc</option>
                                        <option value="IR">IR</option>
                                    </select>
                                </div>
                                <div class="col-md-5 col-sm-5 col-xs-5">
                                    <input type="text" class="form-control" name="variety_used" id="variety_used" placeholder="Variety used last season">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Seed Usage</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <input type="text" class="form-control" name="seed_usage" id="seed_usage" placeholder="Used seeds last season">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Yield</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <input type="text" class="form-control" name="yield" id="yield" placeholder="Yield / Harvest">
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="button" name="button" id="submit" class="btn btn-lg btn-success" style="float: right;"><i class="fa fa-check"></i> Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Search farmer using rsbsa control number / stub Code
        $('#search_farmer_rsbsa').autocomplete({
            source: "farmer_registration/search_farmer",
            select: function(event, ui) {
                $('#search_farmer').val(ui.item.value)
                let farmer_id = ui.item.id
                $('#farmer_registration_form #farmer_id').val(ui.item.id)
                $('#farmer_registration_form #farm_area').val(ui.item.area)
            }
        })

        // On change province
        $('#farmer_registration_form #province').on('change', function() {
            let province = $(this).val()
            $('#farmer_registration_form #municipality').empty()

            $.ajax({
                type: 'GET',
                url: 'farmer_registration/search_municipalities/' + province,
                dataType: 'json',
                success: function(source){
                    let municipalities_options3 = "<option value='0'>Please select a municipality</option>"
                    $.each(source.municipalities, function(i, val) {
                        municipalities_options3 += "<option val='"+val.citymunDesc+"'>"+val.citymunDesc+"</option>"
                    })
                    $('#farmer_registration_form #municipality').append(municipalities_options3)
                    $('#farmer_registration_form #region').val(source.region)
                }
            })
        })

        // Submit farmer registration form
        $('#submit').on('click', function() {
            let farmer_id = $('#farmer_registration_form #farmer_id').val()
            let distribution_id = $('#farmer_registration_form #distribution_id').val()
            let first_name = $('#farmer_registration_form #first_name').val()
            let middle_name = $('#farmer_registration_form #middle_name').val()
            let last_name = $('#farmer_registration_form #last_name').val()
            let suffix_name = $('#farmer_registration_form #suffix_name').val()
            let farmer_gender = $('#farmer_registration_form #farmer_gender').val()
            let birth_date = $('#farmer_registration_form #birth_date').val()
            let region = $('#farmer_registration_form #region').val()
            let province = $('#farmer_registration_form #province').val()
            let municipality = $('#farmer_registration_form #municipality').val()
            let barangay = $('#farmer_registration_form #brgy').val()
            let affiliation_type = $('#farmer_registration_form #affiliation_type').val()
            let affiliation_name = $('#farmer_registration_form #affiliation_name').val()
            let affiliation_accreditation = $('#farmer_registration_form #affiliation_accreditation').val()
            let area_planted = $('#area_planted').val()
            let variety_used = $('#variety_used_prefix').val() + ' ' + $('#variety_used').val()
            let seed_usage = $('#seed_usage').val()
            let yield = $('#yield').val()

            $.ajax({
                type: 'POST',
                url: 'farmer_registration/update_farmer',
                dataType: 'json',
                data: {
                    _token: _token,
                    farmer_id: farmer_id,
                    distribution_id: distribution_id,
                    first_name: first_name,
                    middle_name: middle_name,
                    last_name: last_name,
                    suffix_name: suffix_name,
                    farmer_gender: farmer_gender,
                    birth_date: birth_date,
                    region: region,
                    province: province,
                    municipality: municipality,
                    barangay: barangay,
                    affiliation_type: affiliation_type,
                    affiliation_name: affiliation_name,
                    affiliation_accreditation: affiliation_accreditation,
                    area_planted: area_planted,
                    variety_used: variety_used,
                    seed_usage: seed_usage,
                    yield: yield
                },
                success: function(source) {
                    if (source == "success") {
                        alert('Farmer Profile Updated')
                    } else if (source == "failed") {
                        alert('Error')
                    }
                }
            })
        })
    </script>
@endpush

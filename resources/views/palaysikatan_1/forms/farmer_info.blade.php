@extends('layouts.index')

@section('content')
    <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
    <style>
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

        .form-label {
            font-weight: bold;
            padding-top: 30px !important;
        }

        .form-label-2 {
            font-weight: bold;
            padding-top: 7px !important;
        }

        .control-label {
            font-weight: normal;
        }

        .form-label-title {
            text-align: center;
            padding: 10px;
            background-color: #a6a6a6;
            margin-top: 20px;
            margin-bottom: 10px;
            font-weight: normal;
            color: white
        }

        #submitForm {
            margin: 20px;
        }

        .is-invalid {
            border-color: red;
        }

        .fnt {
            color: black;
        }
        .alertSelect
        {   
            border-width: 1px !important;
            border-style: solid !important;
            border-color: #cc0000 !important;
            background-color: #f3d8d8 !important;
            background-image: url(http://goo.gl/GXVcmC) !important;
            background-position: 50% 50% !important;
            background-repeat: repeat !important;
        }

    </style>
    <div>
        <div class="page-title">
            <div class="title_left fnt">
                <h3>New Farmer</h3>
            </div>
        </div>

        <div class="clearfix"></div>

        <div class="row fnt">
            <div class="col-md-12">
                <form class="form-horizontal" id="farmerForm">
               
              
               
                    <div class="form-label-title">Personal Information of Farmer-Partner</div>
                    <button  type="button"  class="btn btn-primary" id="clearData">Clear Data</button>
                    <div class="form-group row ">
                        <div class="col-lg-2 col-xs-12 form-label-2">
                            <span style="color: red;">*</span><span class="">RSBSA # <br> <small>format: 00-00-00-000-00000</small></span>
                        </div>
                        <div class="col-lg-2 col-xs-12">
                            <input id="rsbsa_control_no" name="rsbsa_control_no" placeholder="RSBSA #" type="text"
                                class="form-control">
                            <div class="invalid-feedback d-block"></div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-lg-2 col-xs-12 form-label"><span style="color: red;">*</span><span
                                class="">Name of
                                Farmer</span></div>
                        <div class="col-lg-2 col-xs-12">
                            <label for="f_firstName" class="control-label">First Name</label>
                            <input id="f_firstName" name="f_firstName" placeholder="Farmer First Name" type="text"
                                class="form-control">
                            <div class="invalid-feedback d-block"></div>
                        </div>
                        <div class="col-lg-2 col-xs-12">
                            <label for="f_middleName" class="control-label">Middle Name</label>
                            <input id="f_middleName" name="f_middleName" placeholder="Farmer Middle Name" type="text"
                                class="form-control">
                            <div class="invalid-feedback d-block"></div>
                        </div>
                        <div class="col-lg-2 col-xs-8">
                            <label for="f_lastName" class="control-label">Last Name</label>
                            <input id="f_lastName" name="f_lastName" placeholder="Farmer Last Name" type="text"
                                class="form-control">
                            <div class="invalid-feedback d-block"></div>
                        </div>
                        <div class="col-lg-1 col-xs-4">
                            <label for="f_extName" class="control-label">Name ext.</label>
                            <input id="f_extName" name="f_extName" placeholder="ext." type="text" class="form-control">
                            <div class="invalid-feedback d-block"></div>
                        </div>
                    </div>
                    <div class="form-group row ">
                        <div class="col-lg-2 col-xs-12 form-label">
                            <span style="color: red;">*</span><span class="">Name of Respondent</span>
                            <div>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="isSame" value="true">
                                    Same as farmer
                                </label>
                            </div>
                        </div>
                        <div class="col-lg-2 col-xs-12">
                            <label for="r_firstName" class="control-label">First Name</label>
                            <input id="r_firstName" name="r_firstName" placeholder="Respondent First Name" type="text"
                                class="form-control">
                            <div class="invalid-feedback d-block"></div>
                        </div>
                        <div class="col-lg-2 col-xs-12">
                            <label for="r_middleName" class="control-label">Middle Name</label>
                            <input id="r_middleName" name="r_middleName" placeholder="Respondent Middle Name" type="text"
                                class="form-control">
                            <div class="invalid-feedback d-block"></div>
                        </div>
                        <div class="col-lg-2 col-xs-8">
                            <label for="r_lastName" class="control-label">Last Name</label>
                            <input id="r_lastName" name="r_lastName" placeholder="Respondent Last Name" type="text"
                                class="form-control">
                            <div class="invalid-feedback d-block"></div>
                        </div>
                        <div class="col-lg-1 col-xs-4">
                            <label for="r_extName" class="control-label">Name ext.</label>
                            <input id="r_extName" name="r_extName" placeholder="ext." type="text" class="form-control">
                            <div class="invalid-feedback d-block"></div>
                        </div>

                    </div>

                    <div class="form-group row ">
                        <div class="col-lg-2 col-xs-12 form-label-2">
                            <span class="">Address Type:</span>
                        </div>
                        <div class="col-lg-1 col-xs-12">
                            <input type="radio" id="home" name="add_type" value="1" checked=""> <label
                                for="home">Home</label>
                        </div>
                        <div class="col-lg-1 col-xs-12">
                            <input type="radio" id="farm" name="add_type" value="0"> <label for="farm">Farm</label>
                        </div>
                    </div>







                    <div class="form-group row">
                        <div class="col-lg-2 col-xs-12 form-label">
                            <span style="color: red;">*</span><span class="">Address</span>
                        </div>
                        <div class="col-lg-2 col-xs-12">
                            <label for="add_region" class="control-label">Region</label>
                            <select id="add_region" name="add_region" class="select form-control address">
                                <option value="-"></option>
                                @foreach ($regions as $region)
                                    <option value="{{ $region->regionName }}">{{ $region->regionName }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback d-block"></div>
                        </div>
                        <div class="col-lg-2 col-xs-12">
                            <label for="add_province" class="control-label">Province</label>
                            <select id="add_province" name="add_province" class="select form-control address">
                                <option value="-"></option>
                            </select>
                            <div class="invalid-feedback d-block"></div>
                        </div>
                        <div class="col-lg-2 col-xs-12">
                            <label for="add_municipality" class="control-label">Municipality</label>
                            <select id="add_municipality" name="add_municipality" class="select form-control address">
                                <option value="-"></option>
                            </select>
                            <div class="invalid-feedback d-block"></div>
                        </div>
                        <div class="col-lg-2 col-xs-12">
                            <label for="barangay" class="control-label">Barangay</label>
                            <input id="barangay" name="barangay" placeholder="Barangay" type="text" class="form-control">
                            <div class="invalid-feedback d-block"></div>
                        </div>

                    </div>
                    <div class="form-group row ">
                        <div class="col-lg-2 col-xs-12 form-label-2">
                            <span style="color: red;">*</span><span class="">Contact Number</span>
                        </div>
                        <div class="col-lg-2 col-xs-12">
                            <input id="contact_no" name="contact_no" placeholder="Contact Number" type="text"
                                class="form-control">
                            <div class="invalid-feedback d-block"></div>
                        </div>
                    </div>
                    <div class="form-group row ">
                        <div class="col-lg-2 col-xs-12 form-label-2">
                            <span style="color: red;">*</span><span class="">Age</span>
                        </div>
                        <div class="col-lg-1 col-xs-12">
                            <input id="age" name="age" placeholder="Age" type="number" class="form-control">
                            <div class="invalid-feedback d-block"></div>
                        </div>
                    </div>
                    <div class="form-group row ">
                        <div class="col-lg-2 col-xs-12 form-label-2">
                            <span style="color: red;">*</span><span class="">Sex</span>
                        </div>
                        <div class="col-lg-2 col-xs-12">
                            <label class="radio-inline">
                                <input type="radio" name="sex" value="male" checked="">
                                Male
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="sex" value="female">
                                Female
                            </label>
                            <div class="invalid-feedback d-block"></div>
                        </div>
                    </div>
                    <div class="form-group row ">
                        <div class="col-lg-2 col-xs-12 form-label-2">
                            <span style="color: red;">*</span><span class="">No. of years in rice
                                farming</span>
                        </div>
                        <div class="col-lg-1 col-xs-12">
                            <input id="farming_years" name="farming_years" placeholder="Years" type="number"
                                class="form-control">
                            <div class="invalid-feedback d-block"></div>
                        </div>
                    </div>
                    <div class="form-group row ">
                        <div class="col-lg-2 col-xs-12 form-label-2">
                            <span class="">Highest educational level completed</span>
                        </div>
                        <div class="col-lg-2 col-xs-12">
                            <input id="highest_education" name="highest_education" placeholder="Education" type="text"
                                class="form-control">
                            <div class="invalid-feedback d-block"></div>
                        </div>
                    </div>
                    <div class="form-group row ">
                        <div class="col-lg-2 col-xs-12 form-label">
                            <span style="color: red;">*</span><span class="">Organization membership
                                (rice-related)</span>
                        </div>
                        <div class="col-lg-3 col-xs-12">
                            <label for="" class="control-label">Organization Type</label>
                            <div>
                                <label class="radio-inline">
                                    <input type="radio" name="org_type" value="cooperative" checked="">
                                    Cooperative
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="org_type" value="association">
                                    Association
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="org_type" value="association">
                                    Others
                                </label>
                            </div>
                            <div class="invalid-feedback d-block"></div>
                        </div>
                        <div class="col-lg-4 col-xs-12">
                            <label for="" class="control-label">Organization Name</label>
                            <input id="org_membership" name="org_membership" placeholder="Organization Name" type="text"
                                class="form-control">
                        </div>
                    </div>
                    <div class="form-group row ">
                        <div class="col-lg-2 col-xs-12 form-label">
                            <span class="">Rice-related training/seminar since 2018</span>
                        </div>
                        <div class="col-lg-2 col-xs-12">
                            <label for="" class="control-label">Training/Seminar</label>
                            <div>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="seminar_history_palaycheck" value="PalayCheck">
                                    PalayCheck
                                </label>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="seminar_history_ffs" value="FFS">
                                    FFS
                                </label>
                            </div>
                            <div class="invalid-feedback d-block"></div>
                        </div>
                        <div class="col-lg-4 col-xs-12">
                            <label for="" class="control-label">Others, please specify</label>
                            <input id="seminar_history" name="seminar_history_others" placeholder="" type="text"
                                class="form-control">
                        </div>
                    </div>
                    <div class="form-label-title">Crop Production Information</div>
                    <div class="form-group row ">
                        <div class="col-lg-2 col-xs-12 form-label-2">
                            <span class=""><span style="color: red;">*</span>Techno demo area (ha)</span>
                        </div>
                        <div class="col-lg-1 col-xs-12">
                            <input id="techno_area" name="techno_area" placeholder="Area (ha)" type="text"
                                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                                class="form-control">
                        </div>
                    </div>
                    <div class="form-group row ">
                        <div class="col-lg-2 col-xs-12 form-label-2">
                            <span class="">Tenure status of techno-demo area</span>
                        </div>
                        <div class="col-lg-6 col-xs-12">
                            <div>
                                @foreach ($tenure_status as $ts)
                                    <label class="radio-inline">
                                        @if ($ts->input_code == 'full_paid')
                                            <input type="radio" name="tenure_status" value="{{ $ts->input_code }}"
                                                checked="">
                                        @else
                                            <input type="radio" name="tenure_status" value="{{ $ts->input_code }}">
                                        @endif
                                        {{ $ts->input_desc }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="form-group row ">
                        <div class="col-lg-2 col-xs-12 form-label-2">
                            <span class="">Area harvested (ha)</span>
                        </div>
                        <div class="col-lg-1 col-xs-12">
                            <input id="area_harvested" name="area_harvested" placeholder="Area (ha)" type="text"
                                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                                class="form-control">
                        </div>
                    </div>
                    <div class="form-group row ">
                        <div class="col-lg-2 col-xs-12 form-label-2">
                            <span class="">Crop establishment</span>
                        </div>
                        <div class="col-lg-6 col-xs-12">
                            <div>
                                @foreach ($crop_establishment as $ts)
                                    <label class="radio-inline">

                                        @if ($ts->input_code == 'mechanized_transplanting')
                                            <input type="radio" name="crop_establishment" value="{{ $ts->input_code }}"
                                                checked="" onclick="crop_sub(this.value);">
                                        @else
                                            <input type="radio" name="crop_establishment" value="{{ $ts->input_code }}"
                                                onclick="crop_sub(this.value);">
                                        @endif

                                        {{ $ts->input_desc }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="form-group row ">
                        <div class="col-lg-2 col-xs-12 form-label-2">
                            <span class="">&nbsp;</span>
                        </div>
                        <div class="col-lg-5 col-xs-12">
                            <div>

                                <select name="crop_establishment_sub" id="crop_establishment_sub" class="form-control">
                                    <option value="">Please Select Crop Establishment</option>
                                </select>


                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-lg-2 col-xs-12 form-label"><span class="">Seed Rate</span></div>
                        <div class="col-lg-2 col-xs-12">
                            <label for="seeding_no_bags" class="control-label">No. of Bags</label>
                            <input id="seeding_no_bags" name="seeding_no_bags" placeholder="Bags" type="number"
                                class="form-control">
                        </div>
                        <div class="col-lg-2 col-xs-12">
                            <label for="seeding_weight_bags" class="control-label">Weight per bag (kg)</label>
                            <input id="seeding_weight_bags" name="seeding_weight_bags" placeholder="Weight" type="text"
                                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                                class="form-control">
                        </div>
                    </div>
                    <div class="form-group row ">
                        <div class="col-lg-2 col-xs-12 form-label">
                            <span class="">Price of Seeds</span>
                        </div>
                        <div class="col-lg-1 col-xs-12">
                            <label for="seeding_no_bags" class="control-label">Price / bag</label>
                            <input id="seeds_price" name="seeds_price" placeholder="price" type="text"
                                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                                class="form-control">
                        </div>
                    </div>
                    <div class="form-group row ">
                        <div class="col-lg-2 col-xs-12 form-label-2">
                            <span class="">Varieties Planted</span>
                        </div>
                        <div class="col-lg-3 col-xs-12">
                            <select id="variety_planted_1" name="variety_planted_1" placeholder="" class="form-control variety_data">
                                <option value="">Please Select a Variety</option>                             
                            </select>
                            <label id= "variety_planted_1_required" style="color:red; display: none">This Field Required</label>

                        </div>
                        {{-- 
                        <div class="col-lg-3 col-xs-12">
                            <select id="variety_planted_2" name="variety_planted_2" placeholder="" class="form-control variety_data">
                                <option value="">Please Select a Variety</option>
                                @foreach ($seed_variety as $variety)
                                    <option value="{{$variety->seedVariety}}">{{$variety->seedVariety}}</option>
                                @endforeach
                            </select>


                        </div>
                        <div class="col-lg-3 col-xs-12">
                            <select id="variety_planted_3" name="variety_planted_3" placeholder="" class="form-control variety_data">
                                <option value="">Please Select a Variety</option>
                                @foreach ($seed_variety as $variety)
                                    <option value="{{$variety->seedVariety}}">{{$variety->seedVariety}}</option>
                                @endforeach
                            </select>


                        </div> --}}

                    </div>
                    <div class="form-group row ">
                        <div class="col-lg-2 col-xs-12 form-label-2">
                            <span class="">Seed class</span>
                        </div>
                        <div class="col-lg-6 col-xs-12">
                            <div>
                                @foreach ($seed_class as $ts)
                                    <label class="radio-inline">
                                        @if ($ts->input_code == 'hybrid')
                                            <input type="radio" name="seed_class" value="{{ $ts->input_code }}"
                                                checked="">
                                        @else
                                            <input type="radio" name="seed_class" value="{{ $ts->input_code }}">
                                        @endif


                                        {{ $ts->input_desc }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="form-group row ">
                        <div class="col-lg-2 col-xs-12 form-label-2">
                            <span class="">Irrigation Source</span>
                        </div>
                        <div class="col-lg-6 col-xs-12">
                            <div>
                                @foreach ($irrigation_source as $ts)
                                    <label class="radio-inline">
                                        @if ($ts->input_code == 'nis')
                                            <input type="radio" name="irrigation_source" value="{{ $ts->input_code }}"
                                                id="{{ $ts->input_code }}" checked="">
                                        @else
                                            <input type="radio" name="irrigation_source" value="{{ $ts->input_code }}"
                                                id="{{ $ts->input_code }}">
                                        @endif


                                        {{ $ts->input_desc }}
                                    </label>
                                @endforeach
                                <label class="radio-inline">
                                    <input type="text" name="irrigation_source_input" id="irrigation_source_input"
                                        class="form-control">
                                </label>

                            </div>
                        </div>
                    </div>
                    <div class="form-group row ">
                        <div class="col-lg-2 col-xs-12 form-label-2">
                            <span class="">Seed Source</span>
                        </div>
                        <div class="col-lg-8 col-xs-12">
                            <div>
                                @foreach ($seed_source as $ts)
                                    <label class="radio-inline">
                                        @if ($ts->input_code == 'seed_grower')
                                            <input type="radio" name="seed_source" value="{{ $ts->input_code }}"
                                                id="{{ $ts->input_code }}" checked="">
                                        @else
                                            <input type="radio" name="seed_source" value="{{ $ts->input_code }}"
                                                id="{{ $ts->input_code }}">
                                        @endif

                                        {{ $ts->input_desc }}
                                    </label>
                                @endforeach
                                <label class="radio-inline">
                                    <input type="text" name="seed_source_input" id="seed_source_input"
                                        class="form-control">
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-lg-2 col-xs-12 form-label"><span class="">Total Harvest</span></div>
                        <div class="col-lg-2 col-xs-12">
                            <label for="harvest_no_bags" class="control-label">No. of Bags</label>
                            <input id="harvest_no_bags" name="harvest_no_bags" placeholder="Bags" type="number"
                                class="form-control">
                        </div>
                        <div class="col-lg-2 col-xs-12">
                            <label for="harvest_weight_bags" class="control-label">Weight per bag (kg)</label>
                            <input id="harvest_weight_bags" name="harvest_weight_bags" placeholder="Weight" type="text"
                                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                                class="form-control">
                        </div>
                        <div class="col-lg-2 col-xs-12">
                            <label for="" class="control-label">Significant crop loss: </label>
                            <div>
                                <label class="radio-inline">
                                    <input type="radio" name="crop_loss" value="Yes">
                                    Yes
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="crop_loss" value="No" checked="">
                                    No
                                </label>
                            </div>
                        </div>
                        <div class="col-lg-2 col-xs-12">
                            <label for="crop_loss_input" class="control-label">If yes, estimated percent damage</label>
                            <div class="input-group">
                                <input id="crop_loss_input" name="crop_loss_input" placeholder="Weight" type="number"
                                    class="form-control">
                                <div class="input-group-addon">
                                    <i class="fa fa-percent"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- start add crop cut --}}
                    <div class="form-group row">
                        <div class="col-lg-2 col-xs-12 form-label"><span class="">Crop Cut</span></div>
                        <div class="col-lg-2 col-xs-12">
                            <label for="cropt_cut_sample1" class="control-label">Sample 1</label>
                            <input id="cropt_cut_sample1" step="0.01" name="cropt_cut_sample1" placeholder="0" type="number"
                                class="form-control">
                        </div>
                        <div class="col-lg-2 col-xs-12">
                            <label for="cropt_cut_sample2" class="control-label">Sample 2</label>
                            <input id="cropt_cut_sample2" step="0.01"  name="cropt_cut_sample2" placeholder="0" type="number"
                                class="form-control">
                        </div>
                        <div class="col-lg-2 col-xs-12">
                            <label for="cropt_cut_sample3" class="control-label">Sample 3</label>
                            <input id="cropt_cut_sample3" step="0.01" name="cropt_cut_sample3" placeholder="0" type="number"
                                class="form-control">
                        </div>
                    </div>
                    {{-- end add crop cut --}}


                    <div class="form-group row ">
                        <div class="col-lg-2 col-xs-12 form-label">
                            <span class="">PHL Share</span>
                        </div>
                        <div class="col-lg-1 col-xs-12">
                            <label for="phl_share" class="control-label">No. of bags</label>
                            <input id="phl_share" name="phl_share" placeholder="Share" type="number"
                                class="form-control">
                        </div>
                    </div>
                    <div class="form-group row ">
                        <div class="col-lg-2 col-xs-12 form-label">
                            <span class="">Prevailing land rent/share</span>
                        </div>
                        <div class="col-lg-1 col-xs-12">
                            <label for="prevailing_land_rent" class="control-label">No. of bags</label>
                            <input id="prevailing_land_rent" name="prevailing_land_rent" placeholder="No. of bags"
                                type="number" class="form-control">
                        </div>
                    </div>
                    <div class="form-group row ">
                        <div class="col-lg-2 col-xs-12 form-label-2">
                            <span class="">Sold As</span>
                        </div>
                        <div class="col-lg-6 col-xs-12">
                            <div>
                                <label class="radio-inline">
                                    <input type="radio" name="sold_as" value="Fresh" checked="">
                                    Fresh
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="sold_as" value="Dry">
                                    Dry
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-lg-2 col-xs-12 form-label"><span class="">Palay Price</span></div>
                        <div class="col-lg-2 col-xs-12">
                            <label for="fresh_palay_price" class="control-label">Fresh (P/kg)</label>
                            <input id="fresh_palay_price" name="fresh_palay_price" placeholder="Fresh Price" type="text"
                                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                                class="form-control">
                        </div>
                        <div class="col-lg-2 col-xs-12">
                            <label for="dry_palay_price" class="control-label">Dry (P/kg)</label>
                            <input id="dry_palay_price" name="dry_palay_price" placeholder="Dry Price" type="text"
                                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                                class="form-control">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-xs-12 text-center">
                            <a class="btn btn-danger" style="margin: 20px;" onclick="home_page();">Cancel</a>
                            <button name="submit" type="submit" class="btn btn-primary" id="submitForm">Submit</button>

                        </div>
                    </div>
                </form>
            </div>
        </div>


    </div>
@endsection

@push('scripts')
    <script>
        //select variety serverside


        retrive();
        const idlist = [
            'rsbsa_control_no',
            'f_firstName',
            'f_middleName',
            'f_lastName',
            'f_extName',
            'r_firstName',
            'r_middleName',
            'r_lastName',
            'r_extName',
            'home',
            'farm',
            'add_region',
            'add_province',
            'add_municipality',
            'barangay',
            'contact_no',
            'age',
            'male',
            'female',
            'farming_years',
            'highest_education',
            'cooperative',
            'association',
            'association',
            'seminar_history_palaycheck',
            'seminar_history_ffs',
            'seminar_history',
            'techno_area',
            'area_harvested',
            'crop_establishment_sub',
            'seeding_no_bags',
            'seeding_weight_bags',
            'seeds_price',
            'variety_planted_1',
            'harvest_no_bags',
            'harvest_weight_bags',
            'crop_loss_input',
            'cropt_cut_sample1',
            'cropt_cut_sample2',
            'cropt_cut_sample3',
            'phl_share',
            'prevailing_land_rent',
            'fresh_palay_price',
            'dry_palay_price',
        ];
 

        $('#clearData').click(function () {
            localStorage.removeItem("data");
            alert("Data Clear");
        });


        function retrive(){

        const  farmer = JSON.parse(localStorage.getItem("data"));
        if(farmer!=null){      
                if(farmer.rsbsa_control_no != undefined){
                $('#rsbsa_control_no').val(farmer.rsbsa_control_no);
                }
                if(farmer.f_firstName != undefined){
                $('#f_firstName').val(farmer.f_firstName);
                }
                if(farmer.f_middleName != undefined){
                $('#f_middleName').val(farmer.f_middleName);
                }
                if(farmer.f_lastName != undefined){
                $('#f_lastName').val(farmer.f_lastName);
                }
                if(farmer.f_extName != undefined){
                $('#f_extName').val(farmer.f_extName);
                }
                if(farmer.r_firstName != undefined){
                $('#r_firstName').val(farmer.r_firstName);
                }
                if(farmer.r_middleName != undefined){
                $('#r_middleName').val(farmer.r_middleName);
                }
                if(farmer.r_lastName != undefined){
                $('#r_lastName').val(farmer.r_lastName);
                }
                if(farmer.r_extName != undefined){
                $('#r_extName').val(farmer.r_extName);
                }
                if(farmer.home != undefined){
                $('#home').val(farmer.home);
                }
                if(farmer.farm != undefined){
                $('#farm').val(farmer.farm);
                }
                if(farmer.add_region != undefined){
                $('#add_region').val(farmer.add_region);
                }
                if(farmer.add_province != undefined){
                $('#add_province').val(farmer.add_province);
                }
                if(farmer.add_municipality != undefined){
                $('#add_municipality').val(farmer.add_municipality);
                }
                if(farmer.barangay != undefined){
                $('#barangay').val(farmer.barangay);
                }
                if(farmer.contact_no != undefined){
                $('#contact_no').val(farmer.contact_no);
                }
                if(farmer.age != undefined){
                $('#age').val(farmer.age);
                }
                if(farmer.male != undefined){
                $('#male').val(farmer.male);
                }
                if(farmer.female != undefined){
                $('#female').val(farmer.female);
                }
                if(farmer.farming_years != undefined){
                $('#farming_years').val(farmer.farming_years);
                }
                if(farmer.highest_education != undefined){
                $('#highest_education').val(farmer.highest_education);
                }
                if(farmer.cooperative != undefined){
                $('#cooperative').val(farmer.cooperative);
                }
                if(farmer.association != undefined){
                $('#association').val(farmer.association);
                }
                if(farmer.association != undefined){
                $('#association').val(farmer.association);
                }
                if(farmer.seminar_history_palaycheck != undefined){
                $('#seminar_history_palaycheck').val(farmer.seminar_history_palaycheck);
                }
                if(farmer.seminar_history_ffs != undefined){
                $('#seminar_history_ffs').val(farmer.seminar_history_ffs);
                }
                if(farmer.seminar_history != undefined){
                $('#seminar_history').val(farmer.seminar_history);
                }
                if(farmer.techno_area != undefined){
                $('#techno_area').val(farmer.techno_area);
                }
                if(farmer.area_harvested != undefined){
                $('#area_harvested').val(farmer.area_harvested);
                }
                if(farmer.crop_establishment_sub != undefined){
                $('#crop_establishment_sub').val(farmer.crop_establishment_sub);
                }
                if(farmer.seeding_no_bags != undefined){
                $('#seeding_no_bags').val(farmer.seeding_no_bags);
                }
                if(farmer.seeding_weight_bags != undefined){
                $('#seeding_weight_bags').val(farmer.seeding_weight_bags);
                }
                if(farmer.seeds_price != undefined){
                $('#seeds_price').val(farmer.seeds_price);
                }
                if(farmer.variety_planted_1 != undefined){
                $('#variety_planted_1').val(farmer.variety_planted_1);
                }
                if(farmer.harvest_no_bags != undefined){
                $('#harvest_no_bags').val(farmer.harvest_no_bags);
                }
                if(farmer.harvest_weight_bags != undefined){
                $('#harvest_weight_bags').val(farmer.harvest_weight_bags);
                }
                if(farmer.crop_loss_input != undefined){
                $('#crop_loss_input').val(farmer.crop_loss_input);
                }
                if(farmer.cropt_cut_sample1 != undefined){
                $('#cropt_cut_sample1').val(farmer.cropt_cut_sample1);
                }
                if(farmer.cropt_cut_sample2 != undefined){
                $('#cropt_cut_sample2').val(farmer.cropt_cut_sample2);
                }
                if(farmer.cropt_cut_sample3 != undefined){
                $('#cropt_cut_sample3').val(farmer.cropt_cut_sample3);
                }
                if(farmer.phl_share != undefined){
                $('#phl_share').val(farmer.phl_share);
                }
                if(farmer.prevailing_land_rent != undefined){
                $('#prevailing_land_rent').val(farmer.prevailing_land_rent);
                }
                if(farmer.fresh_palay_price != undefined){
                $('#fresh_palay_price').val(farmer.fresh_palay_price);
                }
                if(farmer.dry_palay_price != undefined){
                $('#dry_palay_price').val(farmer.dry_palay_price);
                }
        }
        }
        function dataSave(){
          
            var data="{";
         
            const idCount = idlist.length; 
            var state = 0;
        for (let index = 0; index < idlist.length; index++) {
            if(idCount-1 > index){
                if($('#'+idlist[index]+'').val() != ""){
                    data += "\""+idlist[index]+"\" :\""+$('#'+idlist[index]+'').val()+"\",";

                }
                state=1;
            }else{
                if($('#'+idlist[index]+'').val() != ""){
                    data += "\""+idlist[index]+"\":\""+$('#'+idlist[index]+'').val()+"\"";   
                    state=0;             
                }

            
            }
            
            
        }
        data += "}";
        if(state==1){        
            let result = data.slice(0,data.length - 2) +  data.slice(data.length - 1);
            data=""
            data =result;
        }
        
       

   

     
        localStorage.setItem('data', data);
       
        }

        $('input[type="text"]').on('change', function() { 
            dataSave();
        });

        $("input[type=text]").keyup(function () {
            $(this).val($(this).val().toUpperCase());
        });
        $(".variety_data").select2({
            ajax: {
                url: '../../palaysikatan/selectVariety',
                type: 'post',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        _token: "{{ csrf_token() }}",
                        search: params.term // search term
                    };
                },
                processResults: function(response) {
                    return {
                        results: response
                    };
                },
                cache: true
            }

        });

        /* $('#variety_planted_1').select2();
        $('#variety_planted_2').select2();
        $('#variety_planted_3').select2(); */
        crop_sub("mechanized_transplanting");


        function crop_sub(value) {
            if (value === "mechanized_transplanting") {
                $("#crop_establishment_sub").empty().append('<option value="">Please Select Crop Establishment</option>');
                $("#crop_establishment_sub").append(
                    '<option value="Walk Behind Transplanter">Walk Behind Transplanter</option>');
                $("#crop_establishment_sub").append(
                    '<option value="Riding Type Transplanter">Riding Type Transplanter</option>');
            } else if (value === "drum_seeding_sp") {
                $("#crop_establishment_sub").empty().append('<option value="">Please Select Crop Establishment</option>');
                $("#crop_establishment_sub").append('<option value="Seed Spreader">Seed Spreader</option>');
                $("#crop_establishment_sub").append('<option value="Drone Spreader">Drone Spreader</option>');
                $("#crop_establishment_sub").append('<option value="Drum Seeder">Drum Seeder</option>');
                $("#crop_establishment_sub").append('<option value="Precision Seeder">Precision Seeder</option>');
            } else {
                $("#crop_establishment_sub").empty().append('<option value="">Please Select Crop Establishment</option>');
            }

        }



        function home_page() {
            var yesNo = confirm("Back to Farmer list?");
            if (yesNo) {
                window.location.replace("{{ route('palaysikatan.farmers') }}");
            }
        }



        $("#add_province").prop("disabled", true);
        $("#add_municipality").prop("disabled", true);
        $("#barangay").prop("disabled", true);
        $("#irrigation_source").prop("disabled", true);
        $("#seed_source").prop("disabled", true);

        $("#add_region").on('change', function() {
            if ($(this).val() == "-") {
                $("#add_province").prop("disabled", true);
                $("#add_municipality").prop("disabled", true);
                $("#barangay").prop("disabled", true);

                $("#add_province").val("-");
                $("#add_municipality").val("-");
                $("#barangay").val("");

            } else {
                $.ajax({
                    type: "POST",
                    url: "{{ url('palaysikatan/province') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        regCode: $(this).val()
                    },
                    success: function(response) {
                        $("#add_province").prop("disabled", false);
                        obj = JSON.parse(response);

                        $('#add_province').empty();
                        $('#add_province').append($('<option>').val("-").text(""));
                        obj.forEach(data => {
                            $('#add_province').append($('<option>').val(data.province).text(data
                                .province));
                        });
                    }
                });

            }

        });

        $("#add_province").on('change', function() {
            if ($(this).val() == "-") {
                $("#add_municipality").prop("disabled", true);
                $("#barangay").prop("disabled", true);

                $("#add_municipality").val("-");
                $("#barangay").val("");
            } else {
                var region = $("#add_region").val();
                $.ajax({
                    type: "POST",
                    url: "{{ url('palaysikatan/municipality') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        provCode: $(this).val(),
                        regCode: region
                    },
                    success: function(response) {
                        $("#add_municipality").prop("disabled", false);
                        obj = JSON.parse(response);

                        $('#add_municipality').empty();
                        $('#add_municipality').append($('<option>').val("-").text(""));
                        obj.forEach(data => {
                            $('#add_municipality').append($('<option>').val(data.municipality)
                                .text(
                                    data.municipality));
                        });
                    }
                });
            }

        });

        $("#add_municipality").on('change', function() {
            if ($(this).val() == "-") {
                $("#barangay").prop("disabled", true);
                $("#barangay").val("");
            } else {
                $("#barangay").prop("disabled", false);
            }

        });

        $("input[name='irrigation_source']").on('change', function(e) {
            e.preventDefault();

            if ($('input[name="irrigation_source"]:checked').val() == "irr_others") {
                $("#irrigation_source").prop("disabled", false);

            } else {
                $("#irrigation_source").prop("disabled", true);
                $("#irrigation_source").val("");
            }
        });

        $("input[name='seed_source']").on('change', function(e) {
            e.preventDefault();

            if ($('input[name="seed_source"]:checked').val() == "source_others") {
                $("#seed_source").prop("disabled", false);

            } else {
                $("#seed_source").prop("disabled", true);
                $("#seed_source").val("");
            }
        });

        $("input[name='isSame']").on("change", function(e) {
            e.preventDefault();
            var fname = "";
            var lname = "";
            var mname = "";
            var ename = "";


            if ($(this).is(":checked")) {
                fname = $("#f_firstName").val();
                lname = $("#f_lastName").val();
                mname = $("#f_middleName").val();
                ename = $("#f_extName").val();
            }
            $("#r_firstName").val(fname);
            $("#r_lastName").val(lname);
            $("#r_middleName").val(mname);
            $("#r_extName").val(ename);
        });

        $("#farmerForm").submit(function(e) {
            HoldOn.open(holdon_options);
            e.preventDefault();
            var data = getFormData($(this));
            $("#variety_planted_1_required").hide();

            data['_token'] = "{{ csrf_token() }}";
            data['season'] = "Dry Season 2022";
            $.ajax({
                type: "POST",
                url: "{{ url('palaysikatan/add/farmer') }}",
                data: data,
                success: function(response) {
                    if (response['status'] == 'error') {
                        for (const [key, value] of Object.entries(response['error'])) {
                           
                            if(key=="variety_planted_1"){                                
                            $("#variety_planted_1_required").show();

                            }else{
                                $("#" + key).addClass('is-invalid');
                            }
                            $("#" + key).siblings('.invalid-feedback').empty().append(value);
                        }
                        alert("All Fields Required");
                    } else if (response['status'] == 'error_store') {
                        alert(response['errors']);
                    }else{
                        localStorage.removeItem("data");
                        alert("Farmer added successfully");
                        window.location.replace("{{ url('palaysikatan/farmers') }}");
                    }
                   
         
                }
            });
            HoldOn.close();
        });

        function getFormData($form) {
            var unindexed_array = $form.serializeArray();
            var indexed_array = {};

            $.map(unindexed_array, function(n, i) {
                indexed_array[n['name']] = n['value'];
            });

            return indexed_array;
        }
    </script>
@endpush

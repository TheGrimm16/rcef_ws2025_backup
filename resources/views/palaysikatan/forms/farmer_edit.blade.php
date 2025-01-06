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

 .fnt{
    color: black;
}



</style>


<div class="fnt">
    <div class="page-title">
        <div class="title_left fnt">
            <h3>Update Farmer Profile</h3>
        </div>
    </div>

    <div class="clearfix"></div>

    <div class="row">
        <div class="col-md-12">
            <form class="form-horizontal" id="farmerForm">
                <div class="form-label-title">Personal Information of Farmer-Partner</div>
               
                <div class="form-group row ">
                    <div class="col-lg-2 col-xs-12 form-label-2">
                        <span class="">Farmer ID:</span>
                    </div>
                    <div class="col-lg-1 col-xs-12">
                        <label id="farmer_id">{{$farmer_info->farmer_id}}</label> 
                    </div>
                </div>

                <div class="form-group row ">
                    <div class="col-lg-2 col-xs-12 form-label-2">
                        <span style="color: red;">*</span> <span class="">RSBSA # <br> <small>format: 00-00-00-000-00000</small></span>
                    </div>
                    <div class="col-lg-2 col-xs-12">
                        <input id="rsbsa_control_no" name="rsbsa_control_no" placeholder="RSBSA #" type="text" value="{{$farmer_info->rsbsa_control_no}}" 
                            class="form-control">
                        <div class="invalid-feedback d-block"></div>
                    </div>
                </div>



                <div class="form-group row">
                    <div class="col-lg-2 col-xs-12 form-label"><span style="color: red;">*</span><span class="">Name of
                            Farmer</span></div>
                    <div class="col-lg-2 col-xs-12">
                        <label for="f_firstName" class="control-label">First Name</label>
                        <input id="f_firstName" name="f_firstName" placeholder="Farmer First Name" type="text" value="{{$farmer_info->f_firstName}}" 
                            class="form-control">
                        <div class="invalid-feedback d-block"></div>
                    </div>
                    <div class="col-lg-2 col-xs-12">
                        <label for="f_middleName" class="control-label">Middle Name</label>
                        <input id="f_middleName" name="f_middleName" placeholder="Farmer Middle Name" type="text" value="{{$farmer_info->f_middleName}}"
                            class="form-control">
                        <div class="invalid-feedback d-block"></div>
                    </div>
                    <div class="col-lg-2 col-xs-8">
                        <label for="f_lastName" class="control-label">Last Name</label>
                        <input id="f_lastName" name="f_lastName" placeholder="Farmer Last Name" type="text" value="{{$farmer_info->f_lastName}}"
                            class="form-control">
                        <div class="invalid-feedback d-block"></div>
                    </div>
                    <div class="col-lg-1 col-xs-4">
                        <label for="f_extName" class="control-label">Name ext.</label>
                        <input id="f_extName" name="f_extName" placeholder="ext." type="text" class="form-control" value="{{$farmer_info->f_extName}}">
                        <div class="invalid-feedback d-block"></div>
                    </div>
                </div>
                <div class="form-group row ">
                    <div class="col-lg-2 col-xs-12 form-label">
                        <span style="color: red;">*</span><span class="">Name of Respondent</span>
                    </div>
                    <div class="col-lg-2 col-xs-12">
                        <label for="r_firstName" class="control-label">First Name</label>
                        <input id="r_firstName" name="r_firstName" placeholder="Respondent First Name" type="text" value="{{$farmer_info->r_firstName}}"
                            class="form-control">
                        <div class="invalid-feedback d-block"></div>
                    </div>
                    <div class="col-lg-2 col-xs-12">
                        <label for="r_middleName" class="control-label">Middle Name</label>
                        <input id="r_middleName" name="r_middleName" placeholder="Respondent Middle Name" type="text" value="{{$farmer_info->r_middleName}}"
                            class="form-control">
                        <div class="invalid-feedback d-block"></div>
                    </div>
                    <div class="col-lg-2 col-xs-8">
                        <label for="r_lastName" class="control-label">Last Name</label>
                        <input id="r_lastName" name="r_lastName" placeholder="Respondent Last Name" type="text" value="{{$farmer_info->r_lastName}}"
                            class="form-control">
                        <div class="invalid-feedback d-block"></div>
                    </div>
                    <div class="col-lg-1 col-xs-4">
                        <label for="r_extName" class="control-label">Name ext.</label>
                        <input id="r_extName" name="r_extName" placeholder="ext." type="text" class="form-control" value="{{$farmer_info->r_extName}}">
                        <div class="invalid-feedback d-block"></div>
                    </div>

                </div>

                <div class="form-group row ">
                    <div class="col-lg-2 col-xs-12 form-label-2">
                        <span class="">Address Type:</span>
                    </div>

                    <?php 
                        if($farmer_info->is_home == 1){
                            $home = "checked=''";
                            $farm = "";
                        }else{
                            $home = "";
                            $farm = "checked=''";
                        }
                    ?>

                    <div class="col-lg-1 col-xs-12">
                        <input type="radio" id="home" name="add_type" value="1" {{$home}}> <label for="home">Home</label> 
                    </div>
                    <div class="col-lg-1 col-xs-12">
                        <input type="radio" id="farm" name="add_type" value="0" {{$farm}}> <label for="farm">Farm</label> 
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
                                @if($region->regionName == $farmer_info->add_region)
                                    <option value="{{ $region->regionName }}" selected="">{{ $region->regionName }}</option>
                                @else
                                    <option value="{{ $region->regionName }}">{{ $region->regionName }}</option>
                                @endif
                            @endforeach
                        </select>
                        <div class="invalid-feedback d-block"></div>
                    </div>
                    <div class="col-lg-2 col-xs-12">
                        <label for="add_province" class="control-label">Province</label>
                        <select id="add_province" name="add_province" class="select form-control address">
                            <option value="{{$farmer_info->add_province}}">{{$farmer_info->add_province}}</option>
                        </select>
                        <div class="invalid-feedback d-block"></div>
                    </div>
                    <div class="col-lg-2 col-xs-12">
                        <label for="add_municipality" class="control-label">Municipality</label>
                        <select id="add_municipality" name="add_municipality" class="select form-control address">
                            <option value="{{$farmer_info->add_municipality}}">{{$farmer_info->add_municipality}}</option>
                        </select>
                        <div class="invalid-feedback d-block"></div>
                    </div>
                    <div class="col-lg-2 col-xs-12">
                        <label for="barangay" class="control-label">Barangay</label>
                        <input id="barangay" name="barangay" placeholder="Barangay" type="text" class="form-control" value="{{$farmer_info->barangay}}">
                        <div class="invalid-feedback d-block"></div>
                    </div>

                </div>
                <div class="form-group row ">
                    <div class="col-lg-2 col-xs-12 form-label-2">
                        <span style="color: red;">*</span><span class="">Contact Number</span>
                    </div>
                    <div class="col-lg-2 col-xs-12">
                        <input id="contact_no" name="contact_no" placeholder="Contact Number" type="text" value="{{$farmer_info->contact_no}}" 
                            class="form-control">
                        <div class="invalid-feedback d-block"></div>
                    </div>
                </div>
                <div class="form-group row ">
                    <div class="col-lg-2 col-xs-12 form-label-2">
                        <span style="color: red;">*</span><span class="">Age</span>
                    </div>
                    <div class="col-lg-1 col-xs-12">
                        <input id="age" name="age" placeholder="Age" type="number" class="form-control" value="{{$farmer_info->age}}">
                        <div class="invalid-feedback d-block"></div>
                    </div>
                </div>
                <div class="form-group row ">
                    <div class="col-lg-2 col-xs-12 form-label-2">
                        <span style="color: red;">*</span><span class="">Sex</span>
                    </div>
                    <div class="col-lg-2 col-xs-12">
                        <?php 
                            if($farmer_info->sex == "male"){
                                $male = "checked=''";
                                $female = "";
                            }else{
                                $male = "";
                                $female = "checked=''";
                            }
                        ?>
                        <label class="radio-inline">
                            <input type="radio" name="sex" value="male" {{$male}}>
                            Male
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="sex" value="female" {{$female}}>
                            Female
                        </label>
                        <div class="invalid-feedback d-block"></div>
                    </div>
                </div>
                <div class="form-group row ">
                    <div class="col-lg-2 col-xs-12 form-label-2">
                        <span style="color: red;">*</span><span class="">No. of years in rice farming</span>
                    </div>
                    <div class="col-lg-1 col-xs-12">
                        <input id="farming_years" name="farming_years" placeholder="Years" type="number" value="{{$farmer_info->farming_years}}"
                            class="form-control">
                        <div class="invalid-feedback d-block"></div>
                    </div>
                </div>
                <div class="form-group row ">
                    <div class="col-lg-2 col-xs-12 form-label-2">
                        <span class="">Highest educational level completed</span>
                    </div>
                    <div class="col-lg-2 col-xs-12">
                        <input id="highest_education" name="highest_education" placeholder="Education" type="text" value="{{$farmer_info->highest_education}}"
                            class="form-control">
                            <div class="invalid-feedback d-block"></div>
                    </div>
                </div>
                <div class="form-group row ">
                    <div class="col-lg-2 col-xs-12 form-label">
                        <span style="color: red;">*</span><span class="">Organization membership (rice-related)</span>
                    </div>
                    <div class="col-lg-3 col-xs-12">

                        <?php 
                            $cooperative = "";
                            $association = "";
                            $other = "";

                            if($farmer_info->org_type == "cooperative"){
                                $cooperative = "checked=''";
                            }elseif($farmer_info->org_type == "association"){
                                $association = "checked=''";
                            }else{
                                $other = "checked=''";
                            }
                        ?>


                        <label for="" class="control-label">Organization Type</label>
                        <div>
                            <label class="radio-inline">
                                <input type="radio" name="org_type" value="cooperative" {{$cooperative}}>
                                Cooperative
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="org_type" value="association" {{$association}}>
                                Association
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="org_type" value="other" {{$other}}>
                                Others
                            </label>
                        </div>
                        <div class="invalid-feedback d-block"></div>
                    </div>
                    <div class="col-lg-4 col-xs-12">
                        <label for="" class="control-label">Organization Name</label>
                        <input id="org_membership" name="org_membership" placeholder="Organization Name" type="text" value="{{$farmer_info->org_membership}}" 
                            class="form-control">
                    </div>
                </div>
                <div class="form-group row ">
                    <div class="col-lg-2 col-xs-12 form-label">
                        <span class="">Rice-related training/seminar since 2018</span>
                    </div>
                    <div class="col-lg-2 col-xs-12">

                        <?php 
                            $seminar_history_palaycheck = "";
                            $seminar_history_ffs = "";

                            if($farmer_info->seminar_history_palaycheck == "PalayCheck"){
                                $seminar_history_palaycheck = "checked=''";
                            }else{
                                $seminar_history_palaycheck = "";
                            }
                        
                            if($farmer_info->seminar_history_ffs == "FFS"){
                                $seminar_history_ffs = "checked=''";
                            }else{
                                $seminar_history_ffs = "";
                            }
                        ?>
                        <label for="" class="control-label">Training/Seminar</label>
                        <div>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="seminar_history_palaycheck" value="PalayCheck" {{$seminar_history_palaycheck}}>
                                PalayCheck
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="seminar_history_ffs" value="FFS" {{$seminar_history_ffs}}>
                                FFS
                            </label>
                        </div>
                        <div class="invalid-feedback d-block"></div>
                    </div>
                    <div class="col-lg-4 col-xs-12">
                        <label for="" class="control-label">Others, please specify</label>
                        <input id="seminar_history" name="seminar_history_others" placeholder="" type="text" value="{{$farmer_info->seminar_history_others}}" 
                            class="form-control">
                    </div>
                </div>
                <div class="form-label-title">Crop Production Information</div>
                <div class="form-group row ">
                    <div class="col-lg-2 col-xs-12 form-label-2">
                        <span class="">Techno demo area (ha)</span>
                    </div>
                    <div class="col-lg-1 col-xs-12">
                        <input id="techno_area" name="techno_area" placeholder="Area (ha)" type="text" value="{{$farmer_info->techno_area}}" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                            class="form-control">
                    </div>
                </div>
                <div class="form-group row ">
                    <div class="col-lg-2 col-xs-12 form-label-2">
                        <span class="">Tenure status of techno-demo area</span>
                    </div>
                    <div class="col-lg-6 col-xs-12">
                        <div>
                            @foreach($tenure_status as $ts)
                            <label class="radio-inline">
                                @if($farmer_info->tenure_status == $ts->input_code)
                                    <input type="radio" name="tenure_status" value="{{ $ts->input_code }}" checked="">
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
                        <input id="area_harvested" name="area_harvested" placeholder="Area (ha)" type="text" value="{{$farmer_info->area_harvested}}" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                            class="form-control">
                    </div>
                </div>
                <div class="form-group row ">
                    <div class="col-lg-2 col-xs-12 form-label-2">
                        <span class="">Crop establishment</span>
                    </div>
                    <div class="col-lg-6 col-xs-12">
                        <div>
                            @foreach($crop_establishment as $ts)
                            <label class="radio-inline">

                                @if($farmer_info->crop_establishment == $ts->input_code)
                                    <input type="radio" name="crop_establishment" value="{{ $ts->input_code }}" checked="" onclick="crop_sub(this.value);">
                                @else
                                    <input type="radio" name="crop_establishment" value="{{ $ts->input_code }}"  onclick="crop_sub(this.value);">
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
                               @if($farmer_info->crop_establishment_sub != "")
                                    <option value="{{$farmer_info->crop_establishment_sub}}" selected="">{{$farmer_info->crop_establishment_sub}}</option>
                               @endif
                            </select>

                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-lg-2 col-xs-12 form-label"><span class="">Seed Rate</span></div>
                    <div class="col-lg-2 col-xs-12">
                        <label for="seeding_no_bags" class="control-label">No. of Bags</label>
                        <input id="seeding_no_bags" name="seeding_no_bags" placeholder="Bags" type="number" value="{{$farmer_info->seeding_no_bags}}"
                            class="form-control">
                    </div>
                    <div class="col-lg-2 col-xs-12">
                        <label for="seeding_weight_bags" class="control-label">Weight per bag (kg)</label>
                        <input id="seeding_weight_bags" name="seeding_weight_bags" placeholder="Weight" type="text" value="{{$farmer_info->seeding_weight_bags}}" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                            class="form-control">
                    </div>
                </div>
                <div class="form-group row ">
                    <div class="col-lg-2 col-xs-12 form-label">
                        <span class="">Price of Seeds</span>
                    </div>
                    <div class="col-lg-1 col-xs-12">
                        <label for="seeding_no_bags" class="control-label">Price / bag</label>
                        <input id="seeds_price" name="seeds_price" placeholder="price" type="text" value="{{$farmer_info->seeds_price}}" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                            class="form-control">
                    </div>
                </div>
                

                <div class="form-group row ">
                    <div class="col-lg-2 col-xs-12 form-label-2">
                        <span class="">Varieties Planted</span>
                    </div>
                    <div class="col-lg-2 col-xs-12">
                        

                        <select id="variety_planted_1" name="variety_planted_1" placeholder="" class="form-control variety_data">
                            <option value="">Please Select a Variety</option>
                                @foreach($seed_variety as $variety)
                                     @if($variety->seedVariety == $variety_1)
                                        <option value="{{$variety->seedVariety}}" selected="">{{$variety->seedVariety}}</option>
                                    @else
                                        <option value="{{$variety->seedVariety}}">{{$variety->seedVariety}}</option>
                                    @endif
                                @endforeach
                        </select>
                        <label id= "variety_planted_1_required" style="color:red; display: none">This Field Required</label>
                        
                    </div>

                     {{-- <div class="col-lg-2 col-xs-12">
                        <select id="variety_planted_2" name="variety_planted_2" placeholder="" class="form-control">
                            <option value="">Please Select a Variety</option>
                                @foreach($seed_variety as $variety)
                                    @if($variety->seedVariety == $variety_2)
                                        <option value="{{$variety->seedVariety}}" selected="">{{$variety->seedVariety}}</option>
                                    @else
                                        <option value="{{$variety->seedVariety}}">{{$variety->seedVariety}}</option>
                                    @endif

                                    
                                @endforeach
                        </select>

                        
                     </div>
                    <div class="col-lg-2 col-xs-12"> 
                        <select id="variety_planted_3" name="variety_planted_3" placeholder="" class="form-control">
                            <option value="">Please Select a Variety</option>
                                @foreach($seed_variety as $variety)
                                     @if($variety->seedVariety == $variety_3)
                                        <option value="{{$variety->seedVariety}}" selected="">{{$variety->seedVariety}}</option>
                                    @else
                                        <option value="{{$variety->seedVariety}}">{{$variety->seedVariety}}</option>
                                    @endif
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
                            @foreach($seed_class as $ts)
                            <label class="radio-inline">
                                @if($farmer_info->seed_class == $ts->input_code)
                                <input type="radio" name="seed_class" value="{{ $ts->input_code }}" checked="">
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
                            <?php $oth_irr = 1; ?>

                            @foreach($irrigation_source as $ts)
                             @if($ts->input_code != "irr_others")
                            <label class="radio-inline">
                               
                                @if($farmer_info->irrigation_source == $ts->input_code)
                                <input type="radio" name="irrigation_source" value="{{ $ts->input_code }}"
                                    id="{{ $ts->input_code }}" checked="">
                                    <?php $oth_irr = 0; ?>
                                @else
                                <input type="radio" name="irrigation_source" value="{{ $ts->input_code }}"
                                    id="{{ $ts->input_code }}">
                                @endif
                                {{ $ts->input_desc }}
                            </label>
                            @endif
                            @endforeach

                            <label class="radio-inline">
                            @if($oth_irr == 1)
                                <input type="radio" name="seed_source" value="source_others" id="source_others" checked="">Others, specify
                                <?php $oth_irr = $farmer_info->irrigation_source; ?>
                            @else
                                <input type="radio" name="seed_source" value="source_others" id="source_others" >Others, specify
                                <?php $oth_irr =""; ?>
                              </label>
                            @endif
                            <label class="radio-inline">


                            <label class="radio-inline">
                                <input type="text" name="irrigation_source_input" id="irrigation_source_input" class="form-control" value="{{$oth_irr}}">
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
                            <?php $oth = 1; ?>
                            @foreach($seed_source as $ts)
                            @if($ts->input_code != "source_others")
                            <label class="radio-inline">
                                     @if($farmer_info->seed_source == $ts->input_code)
                                    <input type="radio" name="seed_source" value="{{ $ts->input_code }}"
                                        id="{{ $ts->input_code }}" checked="">
                                        <?php $oth = 0; ?>
                                    @else
                                    <input type="radio" name="seed_source" value="{{ $ts->input_code }}"
                                        id="{{ $ts->input_code }}">
                                    @endif   
                                    {{ $ts->input_desc }}
                            </label>
                             @endif
                            @endforeach

                             <label class="radio-inline">
                            @if($oth == 1)
                                <input type="radio" name="seed_source" value="source_others" id="source_others" checked="">Others, specify
                                <?php $oth = $farmer_info->seed_source; ?>
                            @else
                                <input type="radio" name="seed_source" value="source_others" id="source_others" >Others, specify
                                <?php $oth =""; ?>
                              </label>
                            @endif
                            <label class="radio-inline">
                              

                                <input type="text" name="seed_source_input" id="seed_source_input" class="form-control" value="{{$oth}}">
                            </label>
                        </div>
                    </div>
                </div>

                 {{-- start add crop cut --}}
                 <div class="form-group row">
                    <div class="col-lg-2 col-xs-12 form-label"><span class="">Crop Cut</span></div>
                    <div class="col-lg-2 col-xs-12">
                        <label for="cropt_cut_sample1" class="control-label">Sample 1</label>
                        <input id="cropt_cut_sample1" step="0.01" name="cropt_cut_sample1" placeholder="0" type="number"  value="{{$farmer_info->sample_1}}"  class="form-control">
                    </div>
                    <div class="col-lg-2 col-xs-12">
                        <label for="cropt_cut_sample2" class="control-label">Sample 2</label>
                        <input id="cropt_cut_sample2" step="0.01" name="cropt_cut_sample2" placeholder="0" type="number" value="{{$farmer_info->sample_2}}"  class="form-control">
                    </div>                   
                    <div class="col-lg-2 col-xs-12">
                        <label for="cropt_cut_sample3" class="control-label">Sample 3</label>
                        <input id="cropt_cut_sample3"  step="0.01" name="cropt_cut_sample3" placeholder="0" type="number" value="{{$farmer_info->sample_3}}" class="form-control">
                    </div>
                </div>
                {{-- end add crop cut --}}

                <div class="form-group row">
                    <div class="col-lg-2 col-xs-12 form-label"><span class="">Total Harvest</span></div>
                    <div class="col-lg-2 col-xs-12">
                        <label for="harvest_no_bags" class="control-label">No. of Bags</label>
                        <input id="harvest_no_bags" name="harvest_no_bags" placeholder="Bags" type="number" value="{{$farmer_info->harvest_no_bags}}" 
                            class="form-control">
                    </div>
                    <div class="col-lg-2 col-xs-12">
                        <label for="harvest_weight_bags" class="control-label">Weight per bag (kg)</label>
                        <input id="harvest_weight_bags" name="harvest_weight_bags" placeholder="Weight" type="number" value="{{$farmer_info->harvest_weight_bags}}" 
                            class="form-control">
                    </div>
                    <?php
                    $yes = "";
                    $no = "";
                    if($farmer_info->crop_loss >0){
                        $losses = $farmer_info->crop_loss;
                        $yes = "checked = ''";
                    }else{
                        $no = "checked = ''";
                        $losses = "";
                    }

                    ?>
                    <div class="col-lg-2 col-xs-12">
                        <label for="" class="control-label">Significant crop loss: </label>
                        <div>
                            <label class="radio-inline">
                                <input type="radio" name="crop_loss" value="Yes" {{$yes}}>
                                Yes
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="crop_loss" value="No" {{$no}}>
                                No
                            </label>
                        </div>
                    </div>
                    <div class="col-lg-2 col-xs-12">
                        <label for="crop_loss_input" class="control-label">If yes, estimated percent damage</label>
                        <div class="input-group">
                            <input id="crop_loss_input" name="crop_loss_input" placeholder="Weight" type="number" value="{{$losses}}" 
                                class="form-control">
                            <div class="input-group-addon">
                                <i class="fa fa-percent"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group row ">
                    <div class="col-lg-2 col-xs-12 form-label">
                        <span class="">PHL Share</span>
                    </div>
                    <div class="col-lg-1 col-xs-12">
                        <label for="phl_share" class="control-label">No. of bags</label>
                        <input id="phl_share" name="phl_share" placeholder="Share" type="number" class="form-control" value="{{$farmer_info->phl_share}}">
                    </div>
                </div>
                <div class="form-group row ">
                    <div class="col-lg-2 col-xs-12 form-label">
                        <span class="">Prevailing land rent/share</span>
                    </div>
                    <div class="col-lg-1 col-xs-12">
                        <label for="prevailing_land_rent" class="control-label">No. of bags</label>
                        <input id="prevailing_land_rent" name="prevailing_land_rent" placeholder="No. of bags" value="{{$farmer_info->prevailing_land_rent}}"
                            type="number" class="form-control">
                    </div>
                </div>

                <?php
                $fresh = "";
                $dry ="";

                    if($farmer_info->sold_as == "Fresh")$fresh="checked=''";elseif($farmer_info->sold_as == "Dry")$dry="checked=''";

                ?>

                <div class="form-group row ">
                    <div class="col-lg-2 col-xs-12 form-label-2">
                        <span class="">Sold As</span>
                    </div>
                    <div class="col-lg-6 col-xs-12">
                        <div>
                            <label class="radio-inline">
                                <input type="radio" name="sold_as" value="Fresh" {{$fresh}}>
                                Fresh
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="sold_as" value="Dry" {{$dry}}>
                                Dry
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-lg-2 col-xs-12 form-label"><span class="">Palay Price</span></div>
                    <div class="col-lg-2 col-xs-12">
                        <label for="fresh_palay_price" class="control-label">Fresh (P/kg)</label>
                        <input id="fresh_palay_price" name="fresh_palay_price" placeholder="Fresh Price" type="text" value="{{$farmer_info->fresh_palay_price}}"  oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                            class="form-control">
                    </div>
                    <div class="col-lg-2 col-xs-12">
                        <label for="dry_palay_price" class="control-label">Dry (P/kg)</label>
                        <input id="dry_palay_price" name="dry_palay_price" placeholder="Dry Price" type="text" value="{{$farmer_info->dry_palay_price}}"  oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                            class="form-control">
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-xs-12 text-center">
                        <a class="btn btn-danger" style="margin: 20px;" onclick="home_page();">Cancel</a>
                        <button name="submit" type="submit" class="btn btn-primary" id="submitForm">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
   $(".variety_data").select2(); 
    function crop_sub(value){
            if(value === "mechanized_transplanting"){
                $("#crop_establishment_sub").empty().append('<option value="">Please Select Crop Establishment</option>');
                $("#crop_establishment_sub").append('<option value="Walk Behind Transplanter">Walk Behind Transplanter</option>');
                $("#crop_establishment_sub").append('<option value="Riding Type Transplanter">Riding Type Transplanter</option>');     
            }else if(value === "drum_seeding_sp"){
                $("#crop_establishment_sub").empty().append('<option value="">Please Select Crop Establishment</option>');
                $("#crop_establishment_sub").append('<option value="Seed Spreader">Seed Spreader</option>');
                $("#crop_establishment_sub").append('<option value="Drone Spreader">Drone Spreader</option>');  
                $("#crop_establishment_sub").append('<option value="Drum Seeder">Drum Seeder</option>');  
                $("#crop_establishment_sub").append('<option value="Precision Seeder">Precision Seeder</option>');  
            }else{
                $("#crop_establishment_sub").empty().append('<option value="">Please Select Crop Establishment</option>');
            }

    }


    function home_page(){
            var yesNo = confirm("Back to Farmer list?");
            if(yesNo){
               window.location.replace("{{route('palaysikatan.farmers')}}"); 
            }
        }


//$("#add_province").prop("disabled", true);
//$("#add_municipality").prop("disabled", true);
//$("#barangay").prop("disabled", true);
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
            url: "{{url('palaysikatan/province')}}",
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
$("input[type=text]").keyup(function () {
            $(this).val($(this).val().toUpperCase());
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
            url: "{{url('palaysikatan/municipality')}}",
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
                    $('#add_municipality').append($('<option>').val(data.municipality).text(
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

    var yesNo = confirm("Update Farmer Information?");
        if(yesNo){
           e.preventDefault();
            var data = getFormData($(this));
            $("#variety_planted_1_required").hide();
            data['_token'] = "{{ csrf_token() }}";
            data['farmer_id'] = "{{$farmer_info->fid}}";
            data['season'] = "Dry Season 2022";
           $.ajax({
                type: "POST",
                url: "{{url('palaysikatan/update/farmer')}}",
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

                    }else if(response['status'] == 'error_store'){
                        alert(response['errors']);
                    }else{
                        alert("Farmer Updated successfully");
                        window.location.replace("{{url('palaysikatan/farmers')}}");
                    }
                }
            });
  
        }

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
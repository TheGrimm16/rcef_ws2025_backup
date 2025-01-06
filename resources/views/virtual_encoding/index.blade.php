@extends('layouts.index')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <h3>Online Encoding V2</h3>
        </div>
    </div>

    <!-- <div>My Role: {{ Auth::user()->roles->first()->roleId }}</div> -->


    <div class="row">
        <div class="col-md-3"> 
            <label for="province_virtual"> (1) Province</label>
            <select name="province_virtual" id="province_virtual" class="form-control form-select">
                <option value="0">Select Province</option>
                @foreach($provinces as $province)
                    <option value="{{$province->province}}">{{$province->province}}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3" id="connect"> 
            <!-- <div class="row"> -->
                <!-- <div class="col-md-6"> -->
                    <label for="connection"> (2) Connection</label> <br>
                    <select name="connection" id="connection" class="form-control form-select">
                        <option value="1" selected default>FFRS List as of December 31, 2023</option>
                        <option value="2">FFRS List as of May 2024</option>
                    </select>
                <!-- </div> -->
            <!-- </div> -->
        </div>

        <div class="col-md-3" id="search_virtual_div"> 
            <!-- <div class="row"> -->
                <!-- <div class="col-md-6" id="connect">
                    <label for="connection"> (2) Connection</label> <br>
                    <select name="connection" id="connection" class="form-control form-select">
                        <option value="1" selected default>FFRS List as of December 31, 2023</option>
                        <option value="2">FFRS List as of May 2024</option>
                    </select>
                </div> -->

                <?php 
                    $num_temp = "";
                    // if(Auth::user()->roles->first()->roleId != 41 && Auth::user()->roles->first()->roleId != 47 && Auth::user()->roles->first()->roleId != 30){
                        if(Auth::user()->roles->first()->roleId != 41 && Auth::user()->roles->first()->roleId != 47){
                        $num_temp = "2";
                    } else {
                        $num_temp = "3";
                    }
                ?>
                <!-- <div class="col-md-6"> -->
                    <label for="search_virtual">({{ $num_temp }}) Search Farmer </label> <br>
                    <button id="search_virtual" name="search_virtual" style="width:100%;" class="btn btn-success btn-md" data-toggle='modal' data-target='#search_farmer_modal'> <i class="fa fa-search" aria-hidden="true"></i> Search </button>
                <!-- </div> -->
            <!-- </div> -->
        </div>
    </div>

    <div class="row" id="placeholder_name">
     
        <div class="col-md-3">
                <div class="card" style="padding:10px;" id="farmer_info_rec">
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Farmer Information</h4>
                        </div>
                        
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label for="virtual_lname">RCEF ID</label>
                            <input type="text" name="virtual_rcef_id" id="virtual_rcef_id" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <label for="virtual_lname">RSBSA CONTROL NO</label>
                            <input type="text" name="virtual_rsbsa_no" id="virtual_rsbsa_no" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="row" id="name">
                        <div class="col-md-12">
                            <label for="virtual_name">Name</label>
                            <input type="text" name="virtual_name" id="virtual_name" class="form-control" readonly>
                            <input type="hidden" name="virtual_db_ref" id="virtual_db_ref" class="form-control" readonly>
                        </div>
                    </div>
              
                    <div class="row">
                        <div class="col-md-12">
                            <label for="virtual_bday">Birthdate</label>
                            <input type="text" name="virtual_bday" id="virtual_bday" class="form-control" readonly>
                        </div>
                    </div>
        
                    <div class="row">
                        <div class="col-md-12">
                            <label for="virtual_sex">Sex</label>
                            <input type="text" name="virtual_sex" id="virtual_sex" class="form-control" readonly>
                        </div>
                    </div>
        
                    <div class="row">
                        <div class="col-md-12">
                            <label for="virtual_home">Home Address</label>
                            <input type="text" name="virtual_home" id="virtual_home" class="form-control" readonly>
                        </div>
                    </div>
    
    
            </div>

            <div class="card" style="padding:10px;" id="farmer_info_new">
                <div class="row">
                    <div class="col-md-12">
                        <h4>Farmer Information</h4>
                    </div>
                    
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <label for="virtual_rcef_id_new">RCEF ID</label>
                        <input type="text" name="virtual_rcef_id_new" id="virtual_rcef_id_new" class="form-control" placeholder="RCEF ID">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label for="virtual_rsbsa_no_new">RSBSA CONTROL NO</label>
                        <input type="text" name="virtual_rsbsa_no_new" id="virtual_rsbsa_no_new" class="form-control" placeholder="RSBSA Control No">
                    </div>
                </div>
                <div class="row" id="name">
                    <div class="col-md-12">
                        <label for="last_name_new">Last Name</label>
                        <input type="text" name="last_name_new" id="last_name_new" class="form-control" placeholder="Last Name">
                    </div>
                </div>

                <div class="row" id="name">
                    <div class="col-md-12">
                        <label for="first_name_new">First Name</label>
                        <input type="text" name="first_name_new" id="first_name_new" class="form-control" placeholder="First Name">
                    </div>
                </div>

                <div class="row" id="name">
                    <div class="col-md-12">
                        <label for="middle_name_new">Middle Name</label>
                        <input type="text" name="middle_name_new" id="middle_name_new" class="form-control" placeholder="Middle Name"> 
                    </div>
                </div>

                <div class="row" id="name">
                    <div class="col-md-12">
                        <label for="ext_name_new">Ext Name</label>
                        <input type="text" name="ext_name_new" id="ext_name_new" class="form-control" placeholder="Ext name">
                    </div>
                </div>
          

                <div class="row">
                    <div class="col-md-12">
                        <label for="new_sex">Sex</label>
                        <select id="new_sex" name="new_sex" class="form-control form-select"> 
                            <option value="MALE">  MALE </option>
                            <option value="FEMALE"> FEMALE </option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <label for="new_province">Home Province</label>
                        <input type="text" name="new_province" id="new_province" class="form-control" readonly>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <label for="new_municipality">Home Municipality</label>
                        <select name="new_municipality" id="new_municipality" class="form-control form-select">
                            <option value="0">Select Municipality</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <label for="new_brgy">Home Barangay</label>
                        <select name="new_brgy" id="new_brgy" class="form-control form-select">
                            <option value="0">Select Barangay</option>
                        </select>
                    </div>
                </div>


        </div>


        </div>
        
        <div class="col-md-9">
            <div class="card" style="padding:10px;">
                <div class="row"> 
                    <div class="col-md-12"><h4> (3) Parcel List <small>(Click Parcel to distribute)</small> </h4>  </div>
                    <textarea style="display:none;" type="text" value="" name="new_parcel_list" id="new_parcel_list"></textarea>
                    <input type="hidden" name="new_parcel_count"  id="new_parcel_count">
                    <div class="col-md-12" id="parcelary_list">

                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12"><h4>Variety Available </h4></div>
                    <div class="col-md-12" id="variety_list">

                    </div>
            </div>
           
            </div>
        </div>

    </div>


    <div class="row" id="distribution_div" >
        <div class="col-md-12" style="margin-bottom:0; padding-left: 20px; text-align:center;">
            <div class="card">
                <h2> (4) Distribution Data</h2>
            </div>
        </div>
    
        <div class="col-md-3">
            <div class="card" style="padding:5px;">
                <div class="row">
                    <div class="col-md-12">
                        
    
                        <div class="form_for_old">
                            <center>
                                <h2>SELECTED DROP OFF POINT</h2>
                            </center>
                            <input type="text" id="dop_selected_name" name="dop_selected_name" value=""
                                placeholder="Drop Off Point" readonly="readonly" class="form-control">
    
                            <input type="hidden" id="dop_selected_vs" name="dop_selected_vs" value=""
                                placeholder="DATA" readonly="readonly" class="form-control">
                            <input type="hidden" id="virtual_float_id" name="virtual_float_id" value=""
                                placeholder="DATA" readonly="readonly" class="form-control">
                            <input type="hidden" id="is_served" name="is_served" value="" placeholder="DATA"
                                readonly="readonly" class="form-control">
                        </div>
    
                        {{-- HIDDEN DATA FOR POSTING --}}
                        <input type="hidden" id="virtual_final_area" name="virtual_final_area" value=""
                            placeholder="DATA" readonly="readonly" class="form-control">
                        <input type="hidden" id="virtual_remaining" name="virtual_remaining" value=""
                            placeholder="DATA" readonly="readonly" class="form-control">
                        <input type="hidden" id="virtual_claiming_prv" name="virtual_claiming_prv" value=""
                            placeholder="DATA" readonly="readonly" class="form-control">
                        <input type="hidden" id="virtual_db_ref_parcellary" name="virtual_db_ref_parcellary"
                            value="" placeholder="DATA" readonly="readonly" class="form-control">
    
                    </div>
                </div>
                <div class="row form_for_old">
                    <div class="col-md-12">
                        <label for="da_intervention_card">DA Intervention Card</label>
                    </div>
                    <div class="col-md-10">
                        <input type="text" class='form-control' id='da_intervention_card' name='da_intervention_card'
                            disabled>
                    </div>
                    <div class="col-md-1">
                        <button id="intervention_scan" name="intervention_scan" class="btn btn-success"><i
                                class="fa fa-qrcode" aria-hidden="true"></i></button>
    
                    </div>
                </div>
    
                <div class="row">
                    <div class="col-md-12">
                        <center>
                            <h2>DISTRIBUTION DETAILS</h2>
                        </center>
                    </div>
                </div>
    
                <div class="row">
                    <div class="col-md-12">
                        <label for="target_area">* Target Area for planting</label>
                        <input class='form-control' onkeyup='compute_bags();' onchange='compute_bags();'
                            id='target_area' name='target_area' type="number" value="0">
                        <label style="color: #888; font-size: 1rem; font-style: italic;" for="bags_computation"
                            id="bags_computation">Equivalent bag(s): 0</label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label for="variety_select">* Variety</label>
                        <select class="form-control form-select" name="variety_select" id="variety_select">
                            <option value="0">Select Variety
                        </select>
                        </select>
    
                        {{-- <button id="add_variety_dist" class="btn btn-success btn-sm form_for_old"
                            style="float: right; margin-top:2px;"><i class="fa fa-plus" aria-hidden="true"></i>
                            ADD</button> --}}
                    </div>
                </div>
                {{-- <div class="row">
                    <div class="col-md-12">
                        <label for="target_area" id="targetAreaLable">* Target Area for planting</label>
                        <input class='form-control' id='target_area' name='target_area' type="number" value="0">
                    </div>
                </div> --}}
                <div class="row form_for_new">
                    <div class="col-md-12">
                        <label for="target_area" id="targetKgLabel">* Total kilograms(kg)</label>
                        <input class='form-control' id='target_kg' name='target_kg' type="number" value="0">
                    </div>
                </div>
                <button id="add_variety_dist" class="btn btn-success btn-sm"
                    style="float: right; margin-top:2px;"><i class="fa fa-plus" aria-hidden="true"></i> ADD</button>
    
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-hover table-striped table-bordered" id="distribution_tbl">
                            <thead>
                                <th>Variety</th>
                                <th>Claim Area</th>
                                <th>Bags</th>
                                <th>Action</th>
                            </thead>
                            <tbody id='parcelary_body'>
    
                            </tbody>
                        </table>
                    </div>
                </div>
    
                <div class="row form_for_new">
                    <div class="col-md-6">
                        <label for="target_area">Lot</label>
                        <input class='form-control' id='lotNumber' name='lotNumber' type="text" placeholder="Lot">
                    </div>
    
                    <div class="col-md-6">
                        <label for="target_area">Series No.</label>
                        <input class='form-control' id='seriesNo' name='seriesNo' type="text"
                            placeholder="Series no.">
                    </div>
                    <button id="add_lot_series" class="btn btn-success btn-sm" style="float: right; margin-top:2px;"><i
                            class="fa fa-plus" aria-hidden="true"></i> ADD</button>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-hover table-striped table-bordered" id="lotseries">
                            <thead>
                                <th>Lot No</th>
                                <th>Lot Series</th>
                                <th>Action</th>
                            </thead>
                            <tbody id='parcelary_body'>
    
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    
        <div class="col-md-3">
            <div class="card" style="padding:5px;">
                <div class="row">
                    <div class="col-md-12">
                        <center>
                            <h2>LAST SEASON DATA</h2>
                        </center>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label for="yield_area">* Yield Data Last Season</label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12" style="padding:0;">
                        <div class="col-md-4">
                            <input type="number" class="form-control" id="yield_area" name="yield_area"
                                placeholder="Harvest Area">
                            <span style="font-size: 1rem; font-style: italic;">Harvested area (ha)</span>
                        </div>
    
                        <div class="col-md-4">
                            <input type="number" class="form-control" id="yield_bags" name="yield_bags"
                                placeholder="No. of Bag(s)">
                            <span style="font-size: 1rem; font-style: italic;">No. of bags</span>
                        </div>
    
                        <div class="col-md-4">
                            <input type="number" class="form-control" id="yield_weight" name="yield_weight"
                                placeholder="Wt. per">
                            <span style="font-size: 1rem; font-style: italic;">Wt per bag (kg)</span>
                        </div>
    
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="yield_variety" name="yield_variety"
                                placeholder="Variety">
                            <span style="font-size: 1rem; font-style: italic;">Variety</span>
                        </div>
    
                        <div class="col-md-4">
                            <select type="number" class="form-control" id="yield_type" name="yield_type">
                                <option value="Hybrid"> Hybrid</option>
                                <option value="Inbred"> Inbred</option>
    
                            </select>
                            <span style="font-size: 1rem; font-style: italic;">Type</span>
                        </div>
    
                        <div class="col-md-4 form_for_old">
                            <select type="number" class="form-control" id="yield_class" name="yield_class">
                                <option value="-">-</option>
                                <option value="Good"> Good</option>
                                <option value="Certified"> Certified</option>
    
                            </select>
                            <span style="font-size: 1rem; font-style: italic;">Category</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12" style="margin-top: 2rem;">
                        <center>
                            <h2>CURRENT SEASON DATA</h2>
                        </center>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mt">
                        <label for="crop_est"><strong>Crop Establishment Current Season</strong></label>
                        <div class="col-md-6">
                            <input type="radio" name="crop_est" id="direct" value="direct"> <label
                                class="" for="direct"><i>Direct</i></label>
                        </div>
                        <div class="col-md-6">
                            <input type="radio" name="crop_est" id="transplanted" value="transplanted"> <label
                                class="" for="transplanted"><i>Transplanted</i></label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mt">
                        <label for="eco_system"><strong>Ecosystem Current Season</strong></label>
                        <br>
                        <div class="col-md-6">
                            <input type="radio" name="eco_system" id="irrigated" value="irrigated"> <label
                                class="" for="irrigated"><i>Irrigated</i></label>
                        </div>
                        <div class="col-md-6">
                            <input type="radio" name="eco_system" id="rainfed" value="rainfed"> <label
                                class="" for="rainfed"><i>Rainfed</i></label>
                        </div>
    
                        <center>
                            <select name="water_source" id="water_source" class="form-control form-select"
                                style="width: 95%;">
                                <option value="0">Select Source</option>
                            </select>
                        </center>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mt" style="margin-bottom:10px;">
                        <label for="planting_month">Planting Week Current Season</label>
                        <div class="col-md-6">
                            <select name="planting_month" id="planting_month" class="form-control form-select">
                                <option value="0">Planting Month</option>
                                <option value="01">January</option>
                                <option value="02">February</option>
                                <option value="03">March</option>
                                <option value="04">April</option>
                                <option value="05">May</option>
                                <option value="06">June</option>
                                <option value="07">July</option>
                                <option value="08">August</option>
                                <option value="09">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <select name="planting_week" id="planting_week" class="form-control form-select">
                                <option value="0">Planting Week</option>
                                <option value="01">First Week</option>
                                <option value="02">Second Week</option>
                                <option value="03">Third Week</option>
                                <option value="04">Fouth Week</option>
    
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card" style="padding:5px;">
                <div class="col-md-12">
                    <center>
                        <h2>OTHER DISTRIBUTION DETAILS</h2>
                    </center>
                </div>
                <div class="form_for_new">
                    <div class="col-md-12">
                        <label for="cluster">* Check if you are a member of cluster (e.g. farmer cooperative,
                            assocoations, group, organizations) </label>
                    </div>
                    <div class="col-md-12">
                        <div class="col-md-6">
                            <input type="radio" name="cluster" id="clusterYes" value="yes"> <label class=""
                                for="Yes">Yes</label>
                        </div>
                        <div class="col-md-6">
                            <input type="radio" name="cluster" id="clusterNo" value="no"> <label class=""
                                for="No">No</label>
                        </div>
                    </div>
        
                    <div class="col-md-12 mt cluster" style="display: none">
                        <label for="ayuda">Complete name of cluster</label>
                        <div class="col-md-12">
                            <input type="text" class="form-control" name="orgName" id="orgName">
                        </div>
                    </div>
                    <br>
                </div>
                
                <div class="form_for_old">
                    <div class="col-md-12">
                        <label for="kp_kit">* Received Knowledge Product Kit (KP-Kit) </label>
                    </div>
                    <div class="col-md-12">
        
                        <div class="col-md-6">
                            <input type="radio" name="kp_kit" id="Yes" value="yes"> <label class=""
                                for="Yes">Yes</label>
                        </div>
                        <div class="col-md-6">
                            <input type="radio" name="kp_kit" id="No" value="no"> <label class=""
                                for="No">No</label>
                        </div>
        
        
                    </div>
        
                    <div class="col-md-12 mt">
                        <label for="ayuda">Lagyan ng tsek and sumusunod kung ang magsasaka ay nakatangap ng ayuda sa
                            pagsasaka</label>
                        <div class="col-md-5">
                            <input type="checkbox" name="fertilizer" id="fertilizer"> <label class=""
                                for="fertilizer">Fertilizer</label>
                        </div>
                        <div class="col-md-6">
                            <input type="checkbox" name="cash_incentive" id="cash_incentive"> <label class=""
                                for="cash_incentive">Cash Incentives</label>
                        </div>
                        <div class="col-md-5">
                            <input type="checkbox" name="credit_loan" id="credit_loan"> <label class=""
                                for="credit_loan">Credit/Loan</label>
                        </div>
                    </div>
        
                    <div class="col-md-12 mt">
                        <input type="checkbox" name="rep" id="rep"> <label for="rep"
                            class="">Representative of Farmer Beneficiary?</label>
                    </div>
        
                    <div class="col-md-12" id="rep_info" style="margin-bottom: 12px;">
                        <label for="rep_name">* Representative Name</label>
                        <input type="text" class="form-control" id='rep_name' name='rep_name'
                            placeholder="Representative Name">
                        <label for="rep_id">* Type of ID</label>
                        <input type="text" class="form-control" id='rep_id' name='rep_id'
                            placeholder="Type of ID">
                        <label for="rep_relationship">* Relationship</label>
                        <input type="text" class="form-control" id='rep_relationship' name='rep_relationship'
                            placeholder="Representative Relationship">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card" style="padding:5px;">
                <div class="row">
                    <div class="col-md-12">
                        <center>
                            <h2>ADDITIONAL INFORMATION</h2>
                        </center>
                    </div>
                </div>
    
                <div class="row">
                    <div class="col-md-12 mt">
                        <label for="">* Mother's Maiden Name</label>
                    </div>
                </div>
    
                <div class="row">
                    <div class="col-md-12 mt">
                            <input type="text" class='form-control' id='mother_last_name' name='mother_last_name'
                                placeholder="Last Name">
                    </div>
                </div>
    
                <div class="row">
                    <div class="col-md-12 mt">
                        <label for="birthdate">* Farmer's Birthdate</label>
                        <input type="text" class='form-control' id='birthdate' name='birthdate'
                            value="{{ date('m/d/Y') }}">
    
                    </div>
                </div>
    
                <div class="row">
                    <div class="col-md-12 mt">
                        <label for="phone_number">* Phone Number</label>
                        <input type="text" class='form-control' id='phone_number' name='phone_number'
                            placeholder="Contact Number">
                    </div>
                </div>
    
                <div class="form_for_old">
                    <div class="row">
                        <div class="col-md-12" style="margin-top:10px;">
                            <input type="checkbox" class="form-check" id="ip" name="ip"> <label
                                for="ip" class="">Indigenous People </label>
        
                        </div>
                    </div>
        
                    <div class="row">
                        <div class="col-md-12" id="ip_name_div" style="margin-bottom: 10px;">
                            <input type="text" class='form-control' id='ip_name' name='ip_name'
                                placeholder="IP Name">
                        </div>
                    </div>
        
                    <div class="row">
                        <div class="col-md-6">
                            <input type="checkbox" class="form-check" id="pwd" name="pwd"> <label
                                for="pwd" class="">PWD</label>
                        </div>
                    </div>
        
                    <div class="row">
                        <div class="col-md-12 mt" style="margin-bottom:10px;">
                            <label for="fca_name">FCA NAME</label>
                            <input type="text" class="form-control" id='fca_name' name='fca_name'
                                placeholder="FCA Name">
                        </div>
                    </div>    
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <label for="claimable"></label>
                        <a style="margin-bottom:10px;" class='btn btn-success form-control' id='save_update'
                            name='save_update'>Save</a>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @include("virtual_encoding.search_modal")
    @include("virtual_encoding.modal_parcelary")
    @include("virtual_encoding.select_dop_modal")
    @include("virtual_encoding.scanner")
    @include("virtual_encoding.add_parcel_modal")
    @include("virtual_encoding.select_dop_modal_new")
    

    
@endsection



@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        
        $("#province_virtual").select2();
        $("#select_dop").select2();

        $("#search_virtual_div").hide("fast");
        $("#connect").hide("fast");
        $("#placeholder_name").hide("fast");
        $("#distribution_div").hide("fast");
        // $("#distribution_div_new").hide("fast");

        $("#ip_name_div").hide("fast");
        $("#rep_info").hide("fast");
        $(".form-select").select2();
        $("#new_farmer").hide("fast");


    $("#new_farmer").on("click", function(){
        $("#parcelary_list").empty();
        $("#variety_list").empty();
        $("#variety_select").empty();
        $("#select_dop").empty(); 
        $("#farmer_info_rec").hide("fast");
        $("#farmer_info_new").show("fast");
        
        $("#new_parcel_list").val();
        $("#new_parcel_count").val("0");
        

        $("#distribution_div").hide("fast");
        $('#search_farmer_modal').modal("hide");
        $("#placeholder_name").show("fast");
        
        
        var province = $("#province_virtual").val();
        $("#new_province").val(province);
        $.ajax({
            type: 'POST',
            url: "{{route('virtual.municpality')}}",
            data: {
                _token: "{{ csrf_token() }}",
                province: province,
            },
            dataType: 'json',
            success: function(result){
                if(result != "false"){
                    $("#new_municipality").empty();
                    $("#new_municipality").append("<option value='0'>Select Municipality</option>");
                    $.each(result, function (i, d) {
                        $("#new_municipality").append("<option value='"+d.municipality+"'>"+d.municipality+"</option>");
                    });

                    // get_parcel_list(db_ref, prv)
                }else{
                    Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Something went wrong!',  });
                }

                
                // HoldOn.close();
            },
            error: function(result){
                HoldOn.close();
            }
            });
        


            var parcel = "<div class='col-md-4'> <a class='btn btn-info btn-lg'  data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#add_parcel_modal' data style='width:100%;' value='new_parcel'  > <i class='fa fa-plus-square' aria-hidden='true'></i> Add Parcel</a> </div>";
            $("#parcelary_list").append(parcel);



        });



        $("#new_municipality").on("change", function(){
          var new_province =   $("#new_province").val();
          var new_municipality =   $("#new_municipality").val();

            $.ajax({
            type: 'POST',
            url: "{{route('virtual.brgy')}}",
            data: {
                _token: "{{ csrf_token() }}",
                new_province: new_province,
                new_municipality: new_municipality
            },
            dataType: 'json',
            success: function(result){
                if(result != "false"){
                    $("#new_brgy").empty();
                    $("#new_brgy").append("<option value='0'>Select Barangay</option>");
                    $.each(result, function (i, d) {
                        $("#new_brgy").append("<option value='"+d.geocode_brgy+"'>"+d.name+"</option>");
                    });

                    // get_parcel_list(db_ref, prv)
                }else{
                    Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Something went wrong!',  });
                }

                
                // HoldOn.close();
            },
            error: function(result){
                HoldOn.close();
            }
            });


        });


        $('#add_parcel_modal').on('show.bs.modal', function (e) {
            $("#parcel_province").val("0").change();
            $("#parcel_final_area").val("0.00");


        });


        $("#parcel_province").on("change", function(){
            HoldOn.open(holdon_options);
            var parcel_province =  $("#parcel_province").val();

                $.ajax({
                type: 'POST',
                url: "{{route('virtual.municpality')}}",
                data: {
                    _token: "{{ csrf_token() }}",
                    province: parcel_province,
                },
                dataType: 'json',
                success: function(result){
                    if(result != "false"){
                        $("#parcel_municipality").empty();
                        $("#parcel_municipality").append("<option value='0'>Select Municipality</option>");

                        $("#parcel_brgy").empty();
                    $("#parcel_brgy").append("<option value='0'>Select Barangay</option>");
                        $.each(result, function (i, d) {
                            $("#parcel_municipality").append("<option value='"+d.municipality+"'>"+d.municipality+"</option>");
                        });

                        // get_parcel_list(db_ref, prv)
                    }else{
                        Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Something went wrong!',  });
                    }

                    
                    HoldOn.close();
                },
                error: function(result){
                    HoldOn.close();
                }
                });
            



        });



        $("#parcel_municipality").on("change", function(){
            HoldOn.open(holdon_options);
          var new_province =   $("#parcel_province").val();
          var new_municipality =   $("#parcel_municipality").val();

            $.ajax({
            type: 'POST',
            url: "{{route('virtual.brgy')}}",
            data: {
                _token: "{{ csrf_token() }}",
                new_province: new_province,
                new_municipality: new_municipality
            },
            dataType: 'json',
            success: function(result){
                if(result != "false"){
                    $("#parcel_brgy").empty();
                    $("#parcel_brgy").append("<option value='0'>Select Barangay</option>");
                    $.each(result, function (i, d) {
                        $("#parcel_brgy").append("<option value='"+d.geocode_brgy+"'>"+d.name+"</option>");
                    });

                    // get_parcel_list(db_ref, prv)
                }else{
                    Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Something went wrong!',  });
                }

                
                HoldOn.close();
            },
            error: function(result){
                HoldOn.close();
            }
            });


        });




        $("#add_parcel_now").on("click", function(){
            HoldOn.open(holdon_options);
            var province = $("#parcel_province").val();
            var municipality = $("#parcel_municipality").val();
            var brgy = $("#parcel_brgy").val();
            var area = $("#parcel_final_area").val();
            
            if(parseFloat(area) < 0.1){
                Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Area Should be greater than or eaual to 0.1',  });
                HoldOn.close();
                return;
            }

            if(province == "0"){
                Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Please Select Province',  });
                HoldOn.close();
                return;
            }

            if(municipality == "0"){
                Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Please Select Municipality',  });
                HoldOn.close();
                return;
            }

            if(brgy == "0"){
                Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Please Select Barangay',  });
                HoldOn.close();
                return;
            }

            var new_parcel_count = $("#new_parcel_count").val();
            new_parcel_count = parseInt(new_parcel_count) + 1;
            $("#new_parcel_count").val(new_parcel_count);

            
            remaining = Math.ceil(area *2);

            var parcel = "<div class='col-md-4'> <a id='new_parcel_"+new_parcel_count+"' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#select_dop_modal_new' data-db_ref='new' data-float_id = '"+new_parcel_count+"' data-final_area = '"+area+"'  data-municipality='"+municipality+"' data-province='"+province+"' data-brgy='"+brgy+"' data-served = 'false' data-remaining='"+remaining+"' class='btn btn-dark btn-lg new_parcel_btn' data-id='new' style='width:100%;'  >"+province+"<br> "+municipality+"<br>"+area+" available out of "+area+" (ha) </a> </div>";
            $("#parcelary_list").append(parcel);

            var new_parcel_list = $("#new_parcel_list").val();
                if(new_parcel_list != ""){
                    new_parcel_list = new_parcel_list + "|";
                }
                new_parcel_list = new_parcel_list + province +";"+municipality;

                $("#new_parcel_list").val(new_parcel_list);

            
            
            $.ajax({
                    type: 'POST',
                    url: "{{route('virtual.variety_balance')}}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        new_parcel_list: new_parcel_list,
                    },
                    dataType: 'json',
                    success: function(result){
                        if(result["status"] == 0){
                            Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Something went wrong!',footer: 'No Parcel'  });
                            return;
                        }
                        $("#select_dop_new").empty();        
                        $.each(result["dop_available"], function (i, d) {
                            $("#select_dop_new").append("<option value='"+i+"' >"+d+"</option>");

                        })
                        $("#variety_list").empty();
                        $("#variety_select").empty();
                        $.each(result["variety_list"], function (i, d) {
                            var variety = "<div class='col-md-4'> <button class='btn btn-primary btn-lg' style='width:100%;' value='"+d.prv_id+";"+d.seedVariety+"' name='parcel_"+d.prv_id+"_"+d.prv_id+"' >"+d.seedVariety+"<br> "+d.balance+" bag(s) <br> <small> ("+d.province+","+d.municipality+") </small> </button> </div>";
                            
                            if(parseInt(d.balance) > 0){
                                $("#variety_select").append("<option value='"+d.seedVariety+"'>"+d.seedVariety+" ("+d.municipality+")"+"</option>");
                            }

                            $("#variety_list").append(variety);
                        });
                    
                        HoldOn.close();
                    },
                    error: function(result){
                        HoldOn.close();
                    }
            });

            $("#add_parcel_modal").modal("hide");

            HoldOn.close();
        });

        $('#select_dop_modal_new').on('show.bs.modal', function (e) {
             var province = $(e.relatedTarget).data('province');
             var municipality = $(e.relatedTarget).data('municipality');
             var brgy = $(e.relatedTarget).data('brgy');

             var db_ref = $(e.relatedTarget).data('db_ref');
             var final_area = $(e.relatedTarget).data('final_area');
             var float_id = $(e.relatedTarget).data('float_id');
             var remaining =  $(e.relatedTarget).data('remaining'); //BAGS
             
             var served =  $(e.relatedTarget).data('served');

              var claiming_prv =  province+";"+municipality+";"+brgy;
             var birthdate =  "{{date('Y-m-d')}}";

             var mother_name = "";
             var is_ip =  "0";
             var tribe_name =  "";
             var is_pwd =  "0";
             var tel_no =  "";
             var fca_name =  "";

             

            $("#virtual_final_area").val(final_area);
            $("#virtual_remaining").val(remaining);
            $("#virtual_claiming_prv").val(claiming_prv);
            $("#virtual_db_ref_parcellary").val(db_ref);
            $("#virtual_float_id").val(float_id);
            $("#is_served").val(served);
            
            
            
            $("#mother_last_name").val(mother_name);
            $("#birthdate").val(birthdate);
            $("#phone_number").val(tel_no);
            $("#fca_name").val(fca_name);
            
            if(is_ip == "1"){
                $('#ip').prop( "checked", true );
                $("#ip_name_div").show("fast");
                $("#ip_name").val(tribe_name);

            }else{
                $('#ip').prop( "checked", false );
            }
            
            if( is_pwd =="1"){
                $('#pwd').prop( "checked", true );
        
                
            }else{
                $('#pwd').prop( "checked", false );
            }
                


            $(".new_parcel_btn").attr("class", "btn btn-dark btn-lg new_parcel_btn");
            $("#new_parcel_"+float_id).attr("class","btn btn-success btn-lg new_parcel_btn");
                        
         
             
        });

        $("#dop_select_new").on("click", function(){
                let connection = $("#connection").val();
                $("#distribution_div").show("fast");
                var select_dop =  $("#select_dop_new").val();
                var dop_selected_name = $('#select_dop_new').find(":selected").text();
               $("#dop_selected_vs").val(select_dop);
               $("#dop_selected_name").val(dop_selected_name);
        });

       






        

    $("#save_update").on("click", function(){
        // TRAPPER
            let connection = $("#connection").val();
            

            if(connection == 1){
                var served =  $("#is_served").val();
                // console.log(connection);
                if(served == "false"){
                        var tbl_data = $('#distribution_tbl >tbody >tr').length;
                            if(tbl_data <= 0){   Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'No Claim Details',  }); return ;  }
                            var arrays = [];
                            $('#distribution_tbl').eq(0).find('tr').each((r,row) => arrays.push($(row).find('td,th').map((c,cell) => $(cell).text()).toArray()))
                        //FARMER INFO
                        var virtual_rcef_id_new = $("#virtual_rcef_id_new").val();
                        var virtual_rsbsa_no_new = $("#virtual_rsbsa_no_new").val();
                        var last_name_new = $("#last_name_new").val();
                        var first_name_new = $("#first_name_new").val();
                        var middle_name_new = $("#middle_name_new").val();
                        var ext_name_new = $("#ext_name_new").val();
                        var new_sex = $("#new_sex").val();
                        var new_province = $("#new_province").val();
                        var new_municipality = $("#new_municipality").val();
                        var new_brgy = $("#new_brgy").val();
                        var float_id = $("#virtual_float_id").val();

                        var dop_selected_vs = $("#dop_selected_vs").val();
                        var virtual_final_area = $("#virtual_final_area").val();
                        var virtual_remaining = $("#virtual_remaining").val();
                        var virtual_claiming_prv = $("#virtual_claiming_prv").val(); //THIS IS PORVINCE MUNICIPALITY
                        var prv = virtual_claiming_prv;
                        var db_ref = $("#virtual_db_ref_parcellary").val();
                        var da_intervention_card = $("#da_intervention_card").val();
                        var rcef_id = $("#virtual_rcef_id_new").val();
                        var rsbsa_control_no = $("#virtual_rsbsa_no_new").val();
                        var yield_area = $("#yield_area").val();
                        var yield_bags = $("#yield_bags").val();
                        var yield_weight = $("#yield_weight").val();
                        var yield_variety = $("#yield_variety").val();
                        var yield_type = $("#yield_type").val();
                        var yield_class = $("#yield_class").val();
                        var crop_est =  $('input[name="crop_est"]:checked').val();
                        var eco_system =  $('input[name="eco_system"]:checked').val();
                        var water_source = $("#water_source").val();
                        var planting_month = $("#planting_month").val();
                        var planting_week = $("#planting_week").val();
                        var mother_last_name = $("#mother_last_name").val();
                        var mother_first_name = $("#mother_first_name").val();
                        var mother_mid_name = $("#mother_mid_name").val();
                        var mother_ext_name = $("#mother_ext_name").val();
                        var birthdate = $("#birthdate").val();
                        var tel_no = $("#phone_number").val();
                        var ip =  $("#ip").is(':checked');
                        if(ip){
                            ip_name = $("#ip_name").val();
                        }else{
                            ip_name = "";
                        }
                        var pwd =  $("#pwd").is(':checked');
                        var fca_name = $("#fca_name").val();
                        var kp_kit =  $('input[name="kp_kit"]:checked').val();
                        var ayuda_fertilizer =  $("#fertilizer").is(':checked');
                        var ayuda_incentives =  $("#cash_incentive").is(':checked');
                        var ayuda_credit =  $("#credit_loan").is(':checked');
                        var rep = $("#rep").is(':checked');
                            if(rep){
                                var rep_name = $("#rep_name").val();
                                var rep_id = $("#rep_id").val();
                                var rep_relationship = $("#rep_relationship").val();
                            }else{
                                var rep_name = "";
                                var rep_id = "";
                                var rep_relationship = "";
                            }

                        if(last_name_new == "" || first_name_new == "" || middle_name_new == ""  ){ 
                            Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Please Complete Name Information',  });
                            return;
                        }

                        if(new_brgy == "0" || new_municipality == "0"  ){ 
                            Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Please Complete Home Address Information',  });
                            return;
                        }

                        if(yield_area == "" || yield_bags == "" || yield_weight == "" || yield_variety == "" || yield_type == "" ){ 
                            Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Please Complete Last Season Yield Data',  });
                            return;
                        }    
                    
                        if(mother_last_name == ""){
                            Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Please Input Mother Last Name',  });
                            return ; }
                        // if(mother_first_name == ""){ 
                        //     Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Please Input Mother First Name',  });
                        //     return ; }
                        if(birthdate == ""){ 
                            Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Please Input Farmer Birthdate',  });
                        return ; }

                        
                        if(eco_system == undefined){
                        Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Please Choose Eco System',  });
                        return;}

                        if(crop_est == undefined){
                        Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Please Choose Eco System',  });
                        return;}

                        if(kp_kit == undefined){
                        Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Please select if farmer received KP Product',  });
                        return;}

                        Swal.fire({
                            title: "This farmer will not post immidiately, this will be subject for approval",
                            text: "** FARMER INSERTED WILL BE SUBJECT FOR APPROVAL **",
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonText: "Yes, Wait for Approval",
                        }).then(function(result) {
                        
                            if (result.value) {
                            //    AJAX AND SAVE HERE
                            HoldOn.open(holdon_options);
                            $.ajax({
                                type: 'POST',
                                url: "{{route('virtual.insert.distribution')}}",
                                data: {
                                    connection: connection,
                                    _token: "{{ csrf_token() }}",
                                    category: "INBRED",
                                    float_id: float_id,
                                    served: served,
                                    virtual_rcef_id_new: virtual_rcef_id_new,
                                    virtual_rsbsa_no_new: virtual_rsbsa_no_new,
                                    last_name_new: last_name_new,
                                    first_name_new: first_name_new,
                                    middle_name_new: middle_name_new,
                                    ext_name_new: ext_name_new,
                                    new_sex: new_sex,
                                    new_province: new_province,
                                    new_municipality: new_municipality,
                                    new_brgy: new_brgy,
                                    dop_selected_vs: dop_selected_vs,
                                    virtual_final_area: virtual_final_area,
                                    virtual_remaining: virtual_remaining,
                                    virtual_claiming_prv: virtual_claiming_prv,
                                    db_ref: db_ref,
                                    da_intervention_card: da_intervention_card,
                                    rcef_id: rcef_id,
                                    rsbsa_control_no: rsbsa_control_no,
                                    yield_area: yield_area,
                                    yield_bags: yield_bags,
                                    yield_weight: yield_weight,
                                    yield_variety: yield_variety,
                                    yield_type: yield_type,
                                    yield_class: yield_class,
                                    crop_est: crop_est,
                                    eco_system: eco_system,
                                    water_source: water_source,
                                    planting_month: planting_month,
                                    planting_week: planting_week,
                                    mother_last_name: mother_last_name,
                                    mother_first_name: mother_first_name,
                                    mother_mid_name: mother_mid_name,
                                    mother_ext_name: mother_ext_name,
                                    birthdate: birthdate,
                                    tel_no: tel_no,
                                    ip: ip,
                                    ip_name: ip_name,
                                    pwd: pwd,
                                    fca_name: fca_name,
                                    kp_kit: kp_kit,
                                    ayuda_fertilizer: ayuda_fertilizer,
                                    ayuda_incentives: ayuda_incentives,
                                    ayuda_credit: ayuda_credit,
                                    rep: rep,
                                    rep_name: rep_name,
                                    rep_id: rep_id,
                                    rep_relationship: rep_relationship,
                                    distribution: arrays
                                },
                                dataType: 'json',
                                success: function(result){
                                    console.log(result);

                                    if(result["status"] != "1"){
                                        Swal.fire({   icon: 'error',    title: 'Oops...',  text: result["msg"],  });
                                    }else{
                                        Swal.fire(
                                            "SAVED!",
                                            "Your Distribution Have been Saved.",
                                            "success"
                                        );
                                        
                                        var d = result["msg"];
                                        
                                        $("#virtual_rcef_id_new").attr("readonly", "readonly");
                                        $("#virtual_rcef_id_new").val(result["msg"].rcef_id);

                                        $("#virtual_rsbsa_no_new").attr("readonly", "readonly");
                                        $("#virtual_rsbsa_no_new").val(result["msg"].rsbsa_control_no);

                                        $("#last_name_new").attr("readonly", "readonly");
                                        $("#last_name_new").val(result["msg"].lastName);

                                        $("#first_name_new").attr("readonly", "readonly");
                                        $("#first_name_new").val(result["msg"].firstName);

                                        $("#middle_name_new").attr("readonly", "readonly");
                                        $("#middle_name_new").val(result["msg"].midName);

                                        $("#ext_name_new").attr("readonly", "readonly");
                                        $("#ext_name_new").val(result["msg"].extName);

                                        $("#new_sex").attr("readonly", "readonly");
                                        $("#new_sex").val(result["msg"].sex).change();

                                        $("#new_province").attr("readonly", "readonly");
                                        $("#new_province").val(result["msg"].province);

                                        $("#new_municipality").attr("readonly", "readonly");
                                        $("#new_municipality").val(result["msg"].municipality);

                                        $("#new_brgy").attr("readonly", "readonly");
                                        
                                        var float_id =  result["msg"].float_id;
                                        var total_claimed_area =  result["msg"].total_claimed_area;
                                        var final_area =  result["msg"].final_area;

                                        var remaining_new = parseFloat(final_area) - parseFloat(total_claimed_area);

                                        var remaining_bags = parseInt(d.total_claimable) - parseInt(d.total_claimed);

                                        if(remaining_new > 0 ){
                                            $("#new_parcel_"+d.float_id).data("data-db_ref", d.db_ref);
                                            $("#new_parcel_"+d.float_id).data("data-mother_name", d.mother_name);
                                            $("#new_parcel_"+d.float_id).data("data-is_ip", d.is_ip);
                                            $("#new_parcel_"+d.float_id).data("data-tribe_name", d.tribe_name);
                                            $("#new_parcel_"+d.float_id).data("data-is_pwd", d.is_pwd);
                                            $("#new_parcel_"+d.float_id).data("data-birthdate", d.birthdate);
                                            $("#new_parcel_"+d.float_id).data("data-claiming_prv", d.claiming_prv);
                                            $("#new_parcel_"+d.float_id).data("data-tel_no", d.tel_no);
                                            $("#new_parcel_"+d.float_id).data("data-fca_name", d.fca_name);
                                            $("#new_parcel_"+d.float_id).data("data-served", "true");
                                            $("#new_parcel_"+d.float_id).data("data-remaining", remaining_bags);
                                            $("#new_parcel_"+d.float_id).data("data-final_area", d.final_area);
                                            $("#new_parcel_"+d.float_id).data("data-prv", d.prv);
                                            $("#new_parcel_"+d.float_id).data("data-municipality", d.municipality);
                                            $("#new_parcel_"+d.float_id).data("data-province", d.province);
                                            $("#new_parcel_"+d.float_id).data("class", "btn btn-dark btn-lg parcel_btn");
                                            
                                            $("#new_parcel_"+d.float_id).attr("disabled", "true");
                                            $("#new_parcel_"+d.float_id).empty().append(d.province+"<br> "+d.municipality+"<br>"+remaining_new+" available out of "+d.final_area+" (ha)");

                                        }else{
                                            $("#new_parcel_"+d.float_id).data("data-served", "true");
                                            $("#new_parcel_"+d.float_id).attr("class", "btn btn-warning btn-lg parcel_btn");
                                            $("#new_parcel_"+d.float_id).attr("disabled", "true");
                                            
                                            $("#new_parcel_"+d.float_id).empty().append(d.province+"<br> "+d.municipality+"<br>"+remaining_new+" available out of "+d.final_area+" (ha)");

                                        }
                                    

                                        $("#distribution_div").hide("fast");
                                        clear_distri_form_new();

                                    }

                                
                                    // downloadData();
                                    HoldOn.close();
                                },
                                error: function(result){
                                    Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Server Unreachable',  });
                                    // downloadData();
                                    HoldOn.close();
                                }
                                });
                        }
                        });






                }else{
                    var tbl_data = $('#distribution_tbl >tbody >tr').length;
                        if(tbl_data <= 0){
                            Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'No Claim Details',  });
                            return ;
                        }
                        var arrays = [];
                        $('#distribution_tbl').eq(0).find('tr').each((r,row) => arrays.push($(row).find('td,th').map((c,cell) => $(cell).text()).toArray()))

                        var dop_selected_vs = $("#dop_selected_vs").val();
                        var virtual_final_area = $("#virtual_final_area").val();
                        var virtual_remaining = $("#virtual_remaining").val();
                        var virtual_claiming_prv = $("#virtual_claiming_prv").val();

                        var prv = virtual_claiming_prv.substring(0,5);
                        prv = prv.replace("-", "");
                        var db_ref = $("#virtual_db_ref_parcellary").val();
                        var da_intervention_card = $("#da_intervention_card").val();
                        var rcef_id = $("#virtual_rcef_id").val();
                        var rsbsa_control_no = $("#virtual_rsbsa_no").val();
                        var yield_area = $("#yield_area").val();
                        var yield_bags = $("#yield_bags").val();
                        var yield_weight = $("#yield_weight").val();
                        var yield_variety = $("#yield_variety").val();
                        var yield_type = $("#yield_type").val();
                        var yield_class = $("#yield_class").val();
                        var crop_est =  $('input[name="crop_est"]:checked').val();
                        var eco_system =  $('input[name="eco_system"]:checked').val();
                        var water_source = $("#water_source").val();
                        var planting_month = $("#planting_month").val();
                        var planting_week = $("#planting_week").val();
                        var mother_last_name = $("#mother_last_name").val();
                        var mother_first_name = $("#mother_first_name").val();
                        var mother_mid_name = $("#mother_mid_name").val();
                        var mother_ext_name = $("#mother_ext_name").val();
                        var birthdate = $("#birthdate").val();
                        var tel_no = $("#phone_number").val();
                        var ip =  $("#ip").is(':checked');
                        if(ip){
                            ip_name = $("#ip_name").val();
                        }else{
                            ip_name = "";
                        }
                        var pwd =  $("#pwd").is(':checked');
                        var fca_name = $("#fca_name").val();
                        var kp_kit =  $('input[name="kp_kit"]:checked').val();
                        var ayuda_fertilizer =  $("#fertilizer").is(':checked');
                        var ayuda_incentives =  $("#cash_incentive").is(':checked');
                        var ayuda_credit =  $("#credit_loan").is(':checked');
                        var rep = $("#rep").is(':checked');
                            if(rep){
                                var rep_name = $("#rep_name").val();
                                var rep_id = $("#rep_id").val();
                                var rep_relationship = $("#rep_relationship").val();
                            }else{
                                var rep_name = "";
                                var rep_id = "";
                                var rep_relationship = "";
                            }
                    
                            

                        
                        if(yield_area == "" || yield_bags == "" || yield_weight == "" || yield_variety == "" || yield_type == "" ){ 
                            Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Please Complete Last Season Yield Data',  });
                            return;
                        }    
                    
                        if(mother_last_name == ""){
                            Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Please Input Mother Last Name',  });
                            return ; }
                        // if(mother_first_name == ""){ 
                        //     Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Please Input Mother First Name',  });
                        //     return ; }
                        if(birthdate == ""){ 
                            Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Please Input Farmer Birthdate',  });
                        return ; }

                        
                        if(eco_system == undefined){
                        Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Please Choose Eco System',  });
                        return;}

                        if(crop_est == undefined){
                        Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Please Choose Eco System',  });
                        return;}

                        if(kp_kit == undefined){
                        Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Please select if farmer received KP Product',  });
                        return;}

                    
                        Swal.fire({
                            title: "Save Distribution?",
                            text: "You won't be able to revert this!",
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonText: "Yes, Save It!"
                        }).then(function(result) {
                            if (result.value) {
                            //    AJAX AND SAVE HERE
                            HoldOn.open(holdon_options);
                            $.ajax({
                                type: 'POST',
                                url: "{{route('virtual.save.distribution')}}",
                                data: {
                                    connection: connection,
                                    _token: "{{ csrf_token() }}",
                                    category: "INBRED",
                                    dop_selected_vs: dop_selected_vs,
                                    virtual_final_area: virtual_final_area,
                                    virtual_remaining: virtual_remaining,
                                    virtual_claiming_prv: virtual_claiming_prv,
                                    db_ref: db_ref,
                                    da_intervention_card: da_intervention_card,
                                    rcef_id: rcef_id,
                                    rsbsa_control_no: rsbsa_control_no,
                                    yield_area: yield_area,
                                    yield_bags: yield_bags,
                                    yield_weight: yield_weight,
                                    yield_variety: yield_variety,
                                    yield_type: yield_type,
                                    yield_class: yield_class,
                                    crop_est: crop_est,
                                    eco_system: eco_system,
                                    water_source: water_source,
                                    planting_month: planting_month,
                                    planting_week: planting_week,
                                    mother_last_name: mother_last_name,
                                    mother_first_name: mother_first_name,
                                    mother_mid_name: mother_mid_name,
                                    mother_ext_name: mother_ext_name,
                                    birthdate: birthdate,
                                    tel_no: tel_no,
                                    ip: ip,
                                    ip_name: ip_name,
                                    pwd: pwd,
                                    fca_name: fca_name,
                                    kp_kit: kp_kit,
                                    ayuda_fertilizer: ayuda_fertilizer,
                                    ayuda_incentives: ayuda_incentives,
                                    ayuda_credit: ayuda_credit,
                                    rep: rep,
                                    rep_name: rep_name,
                                    rep_id: rep_id,
                                    rep_relationship: rep_relationship,
                                    distribution: arrays
                                },
                                dataType: 'json',
                                success: function(result){
                                    if(result != "true"){
                                    
                                        Swal.fire({   icon: 'error',    title: 'Oops...',  text: result,  });
                                    }else{

                                        Swal.fire(
                                            "SAVED!",
                                            "Your Distribution Have been Saved.",
                                            "success"
                                        );
                                        
                                        select_farmer(db_ref, prv);
                                    }

                                


                                



                                    // downloadData();
                                    HoldOn.close();
                                },
                                error: function(result){
                                    Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Server Unreachable',  });
                                    // downloadData();
                                    HoldOn.close();
                                }
                                });
                        }
                        });
                }
            } else {
                if(served == "false"){}
                else{
                    var tbl_data = $('#distribution_tbl >tbody >tr').length;
                        if(tbl_data <= 0){
                            Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'No Claim Details',  });
                            return ;
                        }
                        var arrays = [];
                        $('#distribution_tbl').eq(0).find('tr').each((r,row) => arrays.push($(row).find('td,th').map((c,cell) => $(cell).text()).toArray()))
    
                        var arrays2 = [];
                        $('#lotseries').eq(0).find('tr').each((r,row) => arrays2.push($(row).find('td,th').map((c,cell) => $(cell).text()).toArray()))
    
    
                        var dop_selected_vs = $("#dop_selected_vs").val();
                        var virtual_final_area = $("#virtual_final_area").val();
                        var virtual_remaining = $("#virtual_remaining").val();
                        var virtual_claiming_prv = $("#virtual_claiming_prv").val();
    
                        var prv = virtual_claiming_prv.substring(0,5);
                        prv = prv.replace("-", "");
                        var db_ref = $("#virtual_db_ref_parcellary").val();
                        var da_intervention_card = $("#da_intervention_card").val();
                        var rcef_id = $("#virtual_rcef_id").val();
                        var rsbsa_control_no = $("#virtual_rsbsa_no").val();
                        var yield_area = $("#yield_area").val();
                        var yield_bags = $("#yield_bags").val();
                        var yield_weight = $("#yield_weight").val();
                        var yield_variety = $("#yield_variety").val();
                        var yield_type = $("#yield_type").val();
                        var yield_class = $("#yield_class").val();
                        var crop_est =  $('input[name="crop_est"]:checked').val();
                        var eco_system =  $('input[name="eco_system"]:checked').val();
                        var water_source = $("#water_source").val();
                        var planting_month = $("#planting_month").val();
                        var planting_week = $("#planting_week").val();
                        var mother_last_name = $("#mother_last_name").val();
                        var mother_first_name = $("#mother_first_name").val();
                        var mother_mid_name = $("#mother_mid_name").val();
                        var mother_ext_name = $("#mother_ext_name").val();
                        var birthdate = $("#birthdate").val();
                        var tel_no = $("#phone_number").val();
                        var ip =  $("#ip").is(':checked');
                        if(ip){
                            ip_name = $("#ip_name").val();
                        }else{
                            ip_name = "";
                        }
                        var pwd =  $("#pwd").is(':checked');
                        var fca_name = $("#orgName").val();
                        var kp_kit =  $('input[name="kp_kit"]:checked').val();
                        var ayuda_fertilizer =  $("#fertilizer").is(':checked');
                        var ayuda_incentives =  $("#cash_incentive").is(':checked');
                        var ayuda_credit =  $("#credit_loan").is(':checked');
                        var rep = $("#rep").is(':checked');
                            if(rep){
                                var rep_name = $("#rep_name").val();
                                var rep_id = $("#rep_id").val();
                                var rep_relationship = $("#rep_relationship").val();
                            }else{
                                var rep_name = "";
                                var rep_id = "";
                                var rep_relationship = "";
                            }
                    
                            
    
                        
                        if(yield_area == "" || yield_bags == "" || yield_weight == "" || yield_variety == "" || yield_type == "" ){ 
                            Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Please Complete Last Season Yield Data',  });
                            return;
                        }    
                     
                        if(mother_last_name == ""){
                            Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Please Input Mother Last Name',  });
                             return ; }
                        // if(mother_first_name == ""){ 
                        //     Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Please Input Mother First Name',  });
                        //     return ; }
                        if(birthdate == ""){ 
                            Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Please Input Farmer Birthdate',  });
                        return ; }                
    
                      
                        Swal.fire({
                            title: "Save Distribution?",
                            text: "You won't be able to revert this!",
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonText: "Yes, Save It!"
                        }).then(function(result) {
                            if (result.value) {
                            //    AJAX AND SAVE HERE
                            HoldOn.open(holdon_options);
                            $.ajax({
                                type: 'POST',
                                url: "{{route('virtual.save.distribution')}}",
                                data: {
                                    connection: connection,
                                    _token: "{{ csrf_token() }}",
                                    category: "HYBRID",
                                    dop_selected_vs: dop_selected_vs,
                                    virtual_final_area: virtual_final_area,
                                    virtual_remaining: virtual_remaining,
                                    virtual_claiming_prv: virtual_claiming_prv,
                                    db_ref: db_ref,
                                    da_intervention_card: da_intervention_card,
                                    rcef_id: rcef_id,
                                    rsbsa_control_no: rsbsa_control_no,
                                    yield_area: yield_area,
                                    yield_bags: yield_bags,
                                    yield_weight: yield_weight,
                                    yield_variety: yield_variety,
                                    yield_type: yield_type,
                                    yield_class: yield_class,
                                    crop_est: crop_est,
                                    eco_system: eco_system,
                                    water_source: water_source,
                                    planting_month: planting_month,
                                    planting_week: planting_week,
                                    mother_last_name: mother_last_name,
                                    mother_first_name: mother_first_name,
                                    mother_mid_name: mother_mid_name,
                                    mother_ext_name: mother_ext_name,
                                    birthdate: birthdate,
                                    tel_no: tel_no,
                                    fca_name: fca_name,
                                    kp_kit: kp_kit,
                                    ayuda_fertilizer: ayuda_fertilizer,
                                    ayuda_incentives: ayuda_incentives,
                                    ayuda_credit: ayuda_credit,
                                    rep: rep,
                                    rep_name: rep_name,
                                    rep_id: rep_id,
                                    rep_relationship: rep_relationship,
                                    distribution: arrays,
                                    lots: arrays2
                                },
                                dataType: 'json',
                                success: function(result){
                                    if(result != "true"){
                                    
                                        Swal.fire({   icon: 'error',    title: 'Oops...',  text: result,  });
                                    }else{
    
                                        Swal.fire(
                                            "SAVED!",
                                            "Your Distribution Have been Saved.",
                                            "success"
                                        );
                                        
                                        select_farmer(db_ref, prv);
                                        $("#placeholder_name").hide("fast");
                                        $("#distribution_div").hide("fast");
                                        /* $("#farmer_info_rec").hide("fast"); */
                                    }
    
                                
    
    
                                
    
    
    
                                    // downloadData();
                                    HoldOn.close();
                                },
                                error: function(result){
                                    Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Server Unreachable',  });
                                    // downloadData();
                                    HoldOn.close();
                                }
                            });
                         }
                        });
                }
            }
    });

    

    function clear_distri_form_new(){

                $("#virtual_float_id").val("");
                $("#virtual_final_area").val("");
                $("#dop_selected_name").val("");
                $("#dop_selected_vs").val("");
                $("#virtual_remaining").val("");
                $("#virtual_claiming_prv").val("0");
                $("#virtual_db_ref_parcellary").val("");
                $("#da_intervention_card").val("");
                $("#target_area").val("0");
                

                $("#yield_area").val("0");
                $("#yield_bags").val("0");
                $("#yield_weight").val("0");
                $("#yield_variety").val("0");
                $("#yield_class").val("-").change();
                $('input[name="crop_est"]').prop( "checked", false );
                $('input[name="eco_system"]').prop( "checked", false );
                $("#distribution_tbl >tbody").empty();
                $("#water_source").empty().append("<option value='0'> Select Water Source </option> ");
                $("#planting_month").val("0").change();
                $("#planting_week").val("0").change();


                
                $("#rep_name").val("");
                $("#rep_id").val("");
                $("#rep_relationship").val("");
                $('#pwd').prop( "checked", false );
                $("#fca_name").val("");
                $('input[name="kp_kit"]').prop( "checked", false );
                $('#fertilizer').prop( "checked", false );
                $('#cash_incentive').prop( "checked", false );
                $('#credit_loan').prop( "checked", false );
                $('#rep').prop( "checked", false );

                $('#ip').prop( "checked", false );
                $("#ip_name").val("");
    
    }

    function clear_distri_form(){
                
                $("#virtual_final_area").val("");
                $("#dop_selected_name").val("");
                $("#dop_selected_vs").val("");
                $("#virtual_remaining").val("");
                $("#virtual_claiming_prv").val("0");
                $("#virtual_db_ref_parcellary").val("");
                $("#da_intervention_card").val("");
                $("#target_area").val("0");
                

                $("#yield_area").val("0");
                $("#yield_bags").val("0");
                $("#yield_weight").val("0");
                $("#yield_variety").val("0");
                $("#yield_class").val("-").change();
                $('input[name="crop_est"]').prop( "checked", false );
                $('input[name="eco_system"]').prop( "checked", false );
                $("#distribution_tbl >tbody").empty();
                $("#water_source").empty().append("<option value='0'> Select Water Source </option> ");
                $("#planting_month").val("0").change();
                $("#planting_week").val("0").change();


                $("#mother_last_name").val("");
                $("#mother_first_name").val("");
                $("#mother_mid_name").val("");
                $("#mother_ext_name").val("");
                
                
                $("#birthdate").val("{{date('m/d/Y')}}");
                $("#phone_number").val("");
                
                $("#rep_name").val("");
                $("#rep_id").val("");
                $("#rep_relationship").val("");
                $('#pwd').prop( "checked", false );
                $("#fca_name").val("");
                $('input[name="kp_kit"]').prop( "checked", false );
                $('#fertilizer').prop( "checked", false );
                $('#cash_incentive').prop( "checked", false );
                $('#credit_loan').prop( "checked", false );
                $('#rep').prop( "checked", false );

                $('#ip').prop( "checked", false );
                $("#ip_name").val("");
    }


    
    $("#ip").on("click", function(){
       var ip_check =  $("#ip").is(':checked')
        if(ip_check){
            $("#ip_name_div").show("fast");
        }else{
            $("#ip_name_div").hide("fast");
        }
    });


    $("#rep").on("click", function(){
       var rep_check =  $("#rep").is(':checked')
        if(rep_check){
            $("#rep_info").show("fast");
        }else{
            $("#rep_info").hide("fast");
        }
    });

    $("#rainfed").on("click", function(){
        
        $("#water_source").empty();
        $("#water_source").append("<option value='Upland'> Upland</option>");
        $("#water_source").append("<option value='Lowland'> Lowland</option>");
    });

    $("#irrigated").on("click", function(){
        
        $("#water_source").empty();
        $("#water_source").append("<option value='NIS/NIA'> NIS/NIA</option>");
        $("#water_source").append("<option value='CIS(Communal)'> CIS(Communal)</option>");
        $("#water_source").append("<option value='STW(Shallow Tube Well)'>STW(Shallow Tube Well) </option>");
        $("#water_source").append("<option value='SWIP(Small water impounding pond)'>SWIP(Small water impounding pond) </option>");
        $("#water_source").append("<option value='River/Stream Pumping'>River/Stream Pumping </option>");
    });



        function compute_bags(){
            var area =  $("#target_area").val();
            var bags = Math.ceil(area *2);

            $("#bags_computation").empty().text("Equivalent bag(s): "+bags);
        }



        $("#province_virtual").on("change", function(){
            let role =  "{{Auth::user()->roles->first()->roleId}}";
            let user =  "{{Auth::user()->username}}";
            // if(role == 41 || role == 47 || role == 30){
            if(role == 41 || role == 47 || user == 'kavin04'){
                $("#connect").show("fast");
                $("#search_virtual_div").show("fast");
            }else{
                $("#connect").hide("fast");
                $("#search_virtual_div").show("fast");
            }
            // if($(this).val() != "0"){
            //     $("#search_virtual_div").show("fast");
            // }else{
            //     $("#search_virtual_div").hide("fast");
            //     $("#placeholder_name").hide("fast");
            // }
        });


        $("#dop_select").on("click", function(){
            let conn = $("#connection").val();
                if(conn == 1){
                    $(".form_for_new").hide();
                    $(".form_for_old").show();
                } else {
                    $(".form_for_new").show();
                    $(".form_for_old").hide();
                }
                $("#distribution_div").show("fast");
                var select_dop =  $("#select_dop").val();
                var dop_selected_name = $('#select_dop').find(":selected").text();
                console.log(select_dop,dop_selected_name);
                $("#dop_selected_vs").val(select_dop);
                $("#dop_selected_name").val(dop_selected_name);
        });

        $('#select_dop_modal').on('show.bs.modal', function (e) {
             var province = $(e.relatedTarget).data('province');
             var municipality = $(e.relatedTarget).data('municipality');
             var db_ref = $(e.relatedTarget).data('db_ref');
             var final_area = $(e.relatedTarget).data('final_area');
             var remaining =  $(e.relatedTarget).data('remaining');

             var claiming_prv =  $(e.relatedTarget).data('claiming_prv');
             var remaining =  $(e.relatedTarget).data('remaining');
 
             var birthdate =  $(e.relatedTarget).data('birthdate');
             var mother_name =  $(e.relatedTarget).data('mother_name');
             var is_ip =  $(e.relatedTarget).data('is_ip');
             var tribe_name =  $(e.relatedTarget).data('tribe_name');
             var is_pwd =  $(e.relatedTarget).data('is_pwd');
             var tel_no =  $(e.relatedTarget).data('tel_no');
             var fca_name =  $(e.relatedTarget).data('fca_name');
             var served =  $(e.relatedTarget).data('served');
             

            $("#virtual_final_area").val(final_area);
            $("#virtual_remaining").val(remaining);
            $("#virtual_claiming_prv").val(claiming_prv);
            $("#virtual_db_ref_parcellary").val(db_ref);

            $("#is_served").val(served);
            
            $("#mother_last_name").val(mother_name);
            $("#birthdate").val(birthdate);
            $("#phone_number").val(tel_no);
            $("#fca_name").val(fca_name);
            
            if(is_ip == "1"){
                $('#ip').prop( "checked", true );
                $("#ip_name_div").show("fast");
                $("#ip_name").val(tribe_name);

            }else{
                $('#ip').prop( "checked", false );
            }
            
            if( is_pwd =="1"){
                $('#pwd').prop( "checked", true );
        
                
            }else{
                $('#pwd').prop( "checked", false );
            }
                




            $(".parcel_btn").attr("class", "btn btn-dark btn-lg parcel_btn");
            $("#parcel_"+db_ref).attr("class","btn btn-success btn-lg parcel_btn");
                        
         
             
        });


        function select_farmer(db_ref, prv){

            let connection = $("#connection").val();
            HoldOn.open(holdon_options);
            clear_distri_form(); 
            $("#farmer_info_new").hide("fast");
            $("#farmer_info_rec").show("fast");
            
            $.ajax({
            type: 'POST',
            url: "{{route('virtual.select_farmer')}}",
            data: {
                _token: "{{ csrf_token() }}",
                prv: prv,
                db_ref: db_ref,
                connection: connection
            },
            dataType: 'json',
            success: function(result){
                
                if(result != "false"){

                    $("#distribution_div").hide("fast");

                    $('#search_farmer_modal').modal("hide");
                    $("#placeholder_name").show("fast");
                    
                    $("#virtual_rcef_id").val(result.rcef_id);
                    $("#virtual_rsbsa_no").val(result.rsbsa_control_no);
                    
                    $("#virtual_name").val(result.lastName+", "+result.firstName+" "+result.midName+" "+result.extName);
                    $("#virtual_db_ref").val(result.db_ref);
                    
                    $("#virtual_bday").val(result.birthdate);
                    $("#virtual_sex").val(result.sex);
                    $("#virtual_home").val(result.home);
                    
                    
                    get_parcel_list(db_ref, prv)
                }else{
                    Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Something went wrong!',  });
                }

                
                // HoldOn.close();
            },
            error: function(result){
                HoldOn.close();
            }
            });

        }

        function get_parcel_list(db_ref, prv){
            
            let connection = $("#connection").val();
            $.ajax({
            type: 'POST',
            url: "{{route('virtual.get.parcel')}}",
            data: {
                _token: "{{ csrf_token() }}",
                prv: prv,
                db_ref: db_ref,
                connection: connection
            },
            dataType: 'json',
            success: function(result){
                
                
                if(result["status"] == 0){
                    Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Something went wrong!',footer: 'No Parcel'  });
                    return;
                }
                
               
                $("#select_dop").empty();        
                $.each(result["dop_available"], function (i, d) {
                  
                    // console.log(i,d);
                     $("#select_dop").append("<option value='"+d.prv_dropoff_id+"' >"+d.dropOffPoint+"</option>");

                })

                 
                
                $("#parcelary_list").empty();
                console.log(result["parcel_list"]);
                $.each(result["parcel_list"], function (i, d) {
                


                    if(d.remaining > 0 ){
                        var parcel = "<div class='col-md-4'> <a id='parcel_"+d.id+"' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#select_dop_modal' data-db_ref='"+d.id+"' data-mother_name = '"+d.mother_name+"' data-is_ip = '"+d.is_ip+"' data-tribe_name = '"+d.tribe_name+"' data-is_pwd   = '"+d.is_pwd+"' data-birthdate  = '"+d.birthdate+"' data-claiming_prv = '"+d.claiming_prv+"' data-tel_no = '"+d.tel_no+"' data-fca_name = '"+d.fca_name+"' data-served = 'true' data-remaining='"+d.remaining+"' data-final_area = '"+d.final_area+"' data-prv='"+d.prv+"' data-municipality='"+d.municipality+"' data-province='"+d.province+"' class='btn btn-dark btn-lg parcel_btn' style='width:100%;'  value='"+d.prv+";"+d.id+"'  >"+d.province+"<br> "+d.municipality+"<br>"+d.remaining_area+" available out of "+d.final_area+" (ha) </a> </div>";
                    }else{
                        var parcel = "<div class='col-md-4'> <a class='btn btn-warning btn-lg parcel_btn' data-served = 'true' style='width:100%;' value='"+d.prv+";"+d.id+"'  >"+d.province+"<br> "+d.municipality+"<br>"+d.remaining_area+" available out of "+d.final_area+" (ha) </a> </div>";
                    }
                  
                    
                    $("#parcelary_list").append(parcel);
                });

                $("#variety_list").empty();
                $("#variety_select").empty();
                $.each(result["variety_list"], function (i, d) {
                    var variety = "<div class='col-md-4'> <button class='btn btn-primary btn-lg' style='width:100%;' value='"+d.prv_id+";"+d.seedVariety+"' name='parcel_"+d.prv_id+"_"+d.prv_id+"' >"+d.seedVariety+"<br> "+d.balance+" bag(s) <br> <small> ("+d.province+","+d.municipality+") </small> </button> </div>";
                    
                    if(parseInt(d.balance) > 0){
                        $("#variety_select").append("<option value='"+d.seedVariety+"'>"+d.seedVariety+" ("+d.municipality+")"+"</option>");
                    }

                    $("#variety_list").append(variety);
                });
               
                HoldOn.close();
            },
            error: function(result){
                HoldOn.close();
            }
            });

        }

        $('#select_dop_modal').on('show.bs.modal', function (e) {
             var province = $(e.relatedTarget).data('province');
             var municipality = $(e.relatedTarget).data('municipality');
             var db_ref = $(e.relatedTarget).data('db_ref');
             var final_area = $(e.relatedTarget).data('final_area');
             var remaining =  $(e.relatedTarget).data('remaining');

             var claiming_prv =  $(e.relatedTarget).data('claiming_prv');
             var remaining =  $(e.relatedTarget).data('remaining');
 
             var birthdate =  $(e.relatedTarget).data('birthdate');
             var mother_name =  $(e.relatedTarget).data('mother_name');
             var is_ip =  $(e.relatedTarget).data('is_ip');
             var tribe_name =  $(e.relatedTarget).data('tribe_name');
             var is_pwd =  $(e.relatedTarget).data('is_pwd');
             var tel_no =  $(e.relatedTarget).data('tel_no');
             var fca_name =  $(e.relatedTarget).data('fca_name');

             

            $("#virtual_final_area").val(final_area);
            $("#virtual_remaining").val(remaining);
            $("#virtual_claiming_prv").val(claiming_prv);
            $("#virtual_db_ref_parcellary").val(db_ref);
            
            $("#mother_last_name").val(mother_name);
            $("#birthdate").val(birthdate);
            $("#phone_number").val(tel_no);
            $("#fca_name").val(fca_name);
            
            if(is_ip == "1"){
                $('#ip').prop( "checked", true );
                $("#ip_name_div").show("fast");
                $("#ip_name").val(tribe_name);

            }else{
                $('#ip').prop( "checked", false );
            }
            
            if( is_pwd =="1"){
                $('#pwd').prop( "checked", true );
        
                
            }else{
                $('#pwd').prop( "checked", false );
            }
                




            $(".parcel_btn").attr("class", "btn btn-dark btn-lg parcel_btn");
            $("#parcel_"+db_ref).attr("class","btn btn-success btn-lg parcel_btn");
                        
         
             
        });


        $("#add_variety_dist").on("click", function(){
            var target_area = $("#target_area").val();
            var variety_select = $("#variety_select").val();
            var remaining_val = $("#virtual_remaining").val();
            var bags = Math.ceil(target_area *2);

            if(target_area <= 0){
                Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'No Claim Area!',  });
                return ;
            }

            if(remaining_val < bags){
                Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Claimable Exceeds!',  });
                return ;
            }

        

            remaining_val = parseInt(remaining_val) - parseInt(bags);
            $("#virtual_remaining").val(remaining_val);
            
            
            var rowCount = $('#distribution_tbl >tbody >tr').length + 1;
        
            $("#parcelary_body").append("<tr id='row_"+rowCount+"'> <td id='variety_"+rowCount+"'>"+variety_select+"</td>  <td id='area_claimed_"+rowCount+"'>"+target_area+"</td>  <td id='bags_claimed_"+rowCount+"'>"+bags+"</td>  <td> <button class='btn btn-danger btn-sm' id='DeleteButton' value='"+rowCount+"'><i class='fa fa-trash-o' aria-hidden='true'></i></button> </td> </tr>");


        });


        $("#distribution_tbl").on("click", "#DeleteButton", function() {
                var id = $(this).val();
                var bag_count = $("#bags_claimed_"+id).text();
                // alert(bag_count);
                var remaining_val = $("#virtual_remaining").val();
                remaining_val = parseInt(remaining_val) + parseInt(bag_count);
                $("#virtual_remaining").val(remaining_val);


                $(this).closest("tr").remove();
                });



        $('#search_farmer_modal').on('show.bs.modal', function() {
                search();
               
            });
    

        $('#parcelary_modal').on('show.bs.modal', function() {
                
                $("#search_farmer_modal").css('opacity', '0.2');
            });
    

            $('#parcelary_modal').on('hide.bs.modal', function() {
                $("#search_farmer_modal").css('opacity', '1');
            });
    

        function search(){
            var province = $('#province_virtual').val();
            var search_bar = $("#search_bar").val();
            var connection = $("#connection").val();
            $('#dataTBL').DataTable().clear();
                $('#dataTBL').DataTable({
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "searching": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "pageLength": 25,
                    "ajax": {
                        "url": "{{route('virtual_search')}}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            "province": province,
                            "search_bar": search_bar,
                            "connection": connection,
                        }
                    },
                    "columns":[
                        {"data": "rsbsa","width":"20%"  },
                        {"data": "name","width":"40%"  },
                        // {"data": "final_area","className": "text-left","width":"10%" },
                        {"data": "sex","className": "text-center", "width":"5%"},
                        {"data": "birthdate","className": "text-center","width":"10%"},
                        {"data": "action" }
                    ],
                    "fnDrawCallback": function() {
                        // Call the function after the columns have returned
                        if ($('#dataTBL').DataTable().rows().count() === 0) {
                        // Call the function
                                $("#new_farmer").show("fast");
                          } else{
                                $("#new_farmer").hide("fast");
                          }
                    }
                });
    
    

                // // Check if the datatable is empty
                //     if ($('#dataTBL').DataTable().rows().count() === 0) {
                //         // Call the function
                //         alert("Empty Rows");
                //     }
    
            }
            
       
        function show_parcelary(prv,db_ref,tbl){
            

            $.ajax({
                type: 'POST',
                url: "{{route('vs.online.view.parcel')}}",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: db_ref,
                    prv_number: prv,
                    tbl: tbl
                },
                dataType: 'json',
                success: function(result){
                    var farmer_info_vs = result.rsbsa_control_no;
                        farmer_info_vs =farmer_info_vs +" - <strong>"+ result.lastName + ", "+ result.firstName + " "+ result.midName + " "+result.extName + "</strong>";
                        farmer_info_vs =farmer_info_vs +" <br> Home: <small>"+ result.home + "</small>";
                        farmer_info_vs =farmer_info_vs +" <br> Total Crop:"+ result.total_size+" (ha)";
                        
                    $("#farmer_info_vs").empty().append(farmer_info_vs);
                    get_parcelary(prv,db_ref,tbl);
                },
                error: function(result){
            
                }
                });



            $("#parcelary_modal").modal("show");

        }
    function get_parcelary(prv,db_ref,tbl){
        $('#parcelary_tbl').DataTable().clear();
            $('#parcelary_tbl').DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "searching": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "pageLength": 25,
                "ajax": {
                    "url": "{{route('vs.get.all.parcel')}}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        "prv": prv,
                        "db_ref": db_ref,
                        "tbl": tbl
                    }
                },
                "columns":[
                    {"data": "province" },
                    {"data": "municipality"},
                    {"data": "final_area"},
                    {"data": "action" }
                ]
            });
    
    }

    var intervention_scanner = new Instascan.Scanner({ video: document.getElementById('intervention_id_preview'), scanPeriod: 5, mirror: false });
        intervention_scanner.addListener('scan',function(content){  
            if(content !="" &&content != null && content != undefined )      
            $("#da_intervention_card").val(content);

            $('#intervention_scanner_modal').modal('hide');
            //getFarmerData(content);
        }); 


        
$("#intervention_scan").on("click", function(){
    var farmer_name = $("#farmer_name").val();
        if(farmer_name == ''){
            alert("Please load a farmer profile first");
            return;
        }



    Instascan.Camera.getCameras().then(function (cameras){
        if(cameras.length>0){
            intervention_scanner.start(cameras[0]);
            $('[name="options"]').on('change',function(){
                if($(this).val()==1){
                    if(cameras[0]!=""){
                        intervention_scanner.start(cameras[0]);
                    }else{
                        alert('No Front camera found!');
                    }
                }else if($(this).val()==2){
                    if(cameras[1]!=""){
                        intervention_scanner.start(cameras[1]);
                    }else{
                        alert('No Back camera found!');
                    }
                }
            });
        }else{
            console.error('No cameras found.');
            alert('No cameras found.');
        }
        }).catch(function(e){
            console.error(e);
            alert(e);
        });
    $('#intervention_scanner_modal').modal('show');
});

$(document).on('hide.bs.modal','#intervention_scanner_modal', function () {
        Instascan.Camera.getCameras().then(function (cameras){
        if(cameras.length>0){
            intervention_scanner.stop(cameras[0]);           
        }else{
            console.error('No cameras found.');
            alert('No cameras found.');
        }
        }).catch(function(e){
            console.error(e);
            alert(e);
        });
    });


    </script>
@endpush
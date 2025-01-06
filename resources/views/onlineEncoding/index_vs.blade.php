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

        .mt{
            margin-top: 1rem;
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

        .preview_qr_id{
           width:570px;
           height: auto;
           margin:0px auto;
        }

        .dis_tab{
            padding: 0;
        }
        
    </style>


	<div>
		<div class="page-title">
            <div class="title_left tbl">
              <h3>Online Encoding</h3>
            </div>
        </div>

        <div class="clearfix">
            <div class="x_panel">
                <div class="row">
                    
                    
                    <div class="col-md-3">                       
                        <div class="x_panel"> 

                            <div class="row">
                                <div class="col-md-12">
                                    <Label for="province_select">Select Province</Label>                            
                                        <select class="form-control"  style="border-radius: 30px" name="" id="province_select">
                                            <option value="0">Select Province</option>
                                            @foreach ($provinces as $value)
                                            <option value="{{$value->province}}">{{$value->province}}</option>
                                            @endforeach
                                        </select>                               
                                </div>                            
                                <div class="col-md-12">
                                    <Label for="municipality_select">Select Municipality</Label>                            
                                        <select class="form-control"  style="border-radius: 30px" name="" id="municipality_select">
                                            <option value="0">Select Municipality</option>
                                        </select>                               
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <Label for="dop_select">Select Dropoff Point</Label>                            
                                        <select class="form-control"  style="border-radius: 30px" name="" id="dop_select">
                                            <option value="0">Select Dropoff Point</option>
                                        </select>                               
                                </div>
                                <br><br> <br><br>
                                <div class="col-md-12">
                                    <button onclick="downloadData()" class="btn btn-success form-control"><i class="fa fa-cloud-download" aria-hidden="true"></i> Download Data</button>                             
                                </div>

                            </div>
                          
                            

                     
                        </div>
                    </div>
                    {{-- col 1  end--}}
                    {{-- col 2 start --}}
                    
                        <div class="col-md-6">
                            <div id="variety_available">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div id="grandTotal">
                            </div>
                        </div>
                 



                                  
                </div>


                <div class="row">
                    
                        <div class="" id="farmer_search_div">
                           
                            <div class="alert alert-warning alert-dismissible fade in" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                                <strong><i class="fa fa-info-circle"></i> Notice!</strong> Search in any of the requested parameters below
                    
                            </div>

                            <div class="row p-4">
                                <div class="col-md-2">
                                    <label for="rsbsa_rcef_id">RCEF ID / RSBSA Number</label>
                                    <input type="text" name="rsbsa_rcef_id" id="rsbsa_rcef_id" placeholder="RCEF ID / RSBSA Number" class="form-control">
                                </div>
                                <div class="col-md-2">
                                    <label for="fname_vs">First Name</label>
                                    <input type="text" name="fname_vs" id="fname_vs" placeholder="First Name" class="form-control">
                                </div>
    
                                <div class="col-md-2">
                                    <label for="lname_vs">Last Name</label>
                                    <input type="text" name="lname_vs" id="lname_vs" placeholder="Last Name" class="form-control">
                                </div>

                                <div class="col-md-2">
                                    <label for="lname_vs">Middle Name</label>
                                    <input type="text" name="mname_vs" id="mname_vs" placeholder="Middle Name" class="form-control">
                                </div>
                                <div class="col-md-2">
                                    <label for="ename_vs">Extension Name</label>
                                    <input type="text" name="ename_vs" id="ename_vs" placeholder="Ext Name" class="form-control">
                                </div>
    
                               
                                <div class="col-md-2 align-bottom">
                                
                                    <button class="btn btn-success btn-lg"  id="search_farmer_vs" name="search_farmer_vs">SEARCH</button>
                                    <button class="btn btn-success btn-lg"  id="new_farmer_vs" name="new_farmer_vs" disabled>NEW</button>
                                    
                                </div>

                            </div>
                            

                        </div>
                    
                </div>

                <br>
                
                <div class="row">
                    <div class="" id="farmer_search_tbl">
                        <table class="table table-hover table-striped table-bordered" id="farmer_tbl">
                            <thead>
                                <th>RSBSA NO</th>
                                <th>RCEF ID</th>
                                <th>Last Name</th>
                                <th>First Name</th>
                                <th>Middle Name</th>
                                <th>Ext Name</th>
                                {{-- <th>Home Address</th> --}}
                                <th>Birthdate</th>
                                <th>Contact Number</th>
                                <th>Mother's Name</th>
                                <th>List Version</th>
                                
                                <th>Action</th>
                                
                            </thead>
                            <tbody id='databody'>
                                
                            </tbody>
                        </table>


                    </div>
                </div>


                

















                <div class="row" >
                        <div class="col-md-3 dis_tab">
                            <div class="col-md-12" id="farmer_profile_tab">
                                <div class="col-md-12" style="display: flex!important; justify-content: center; align-items: center;">
                                  
                                        <div class="card" style="padding: 2rem 1rem">
                                            <div class="col-md-12">
                                              <center>  <h2>FARMER PROFILE</h2> </center>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="col-md-6">
                                                    <label for="search_farmer">Search Farmer</label>
                                                    <button class='btn btn-success form-control' data-toggle='modal' data-target='#search_farmer_modal' title='Search Farmer' id='search_farmer' name='search_farmer'>
                                                        <i class="fa fa-search" aria-hidden="true"> Search</i></button>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <label for="scan_id">Scan RCEF ID</label>
                                                    <button class='btn btn-success form-control' title='Scan RCEF ID' id='scan_id' name='scan_id'>
                                                        <i class="fa fa-qrcode" aria-hidden="true"> Scan</i></button>
            
                                                </div>
                                       
                                            </div>
                                            <div class="col-md-12">
                                                <label for="rcef_id">* RCEF ID</label>
                                                <input  class='form-control' id='rcef_id' name='rcef_id' disabled>
                                            </div>
            
                                            <div class="col-md-12">
                                                <label for="rsbsa_number">* RSBSA Stub / Control #</label>
                                                <input type="text" class='form-control' id='rsbsa_number' name='rsbsa_number' disabled>
                                            </div>
                                            <div class="col-md-12">
                                                <label for="farmer_name">Farmer Name</label>
                                                <input  type="text" class='form-control' id='farmer_name' name='farmer_name' disabled>
                                                
                                            </div>
            
                                            <div class="col-md-12">
                                                
                                                <label for="da_intervention_card">DA Intervention Card</label>
                                                <div class="col-md-10" style="margin:0;padding:0;">
                                                
                                                    <input  type="text" class='form-control' id='da_intervention_card' name='da_intervention_card' disabled>
                                                    
                                                </div>
                                                <div class="col-md-1">
                                                    <button id="intervention_scan" name="intervention_scan" class="btn btn-success"><i class="fa fa-qrcode" aria-hidden="true"></i></button>

                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <label for="enrolled_area">* Enrolled Area</label>
                                                <a  class='btn btn-warning form-control' id='enrolled_area' name='enrolled_area'></a>
                                            </div>
            
                                            <div class="col-md-12">
                                                <label for="claimable">* Claimable Bags</label>
                                                <a  class='btn btn-warning form-control' id='claimable' name='claimable'></a>
                                            </div>
            
                                           
            
                                        </div>
                                    
                                </div>
                            </div>

                        </div>
                        
                        <div class="col-md-3 dis_tab" style="transform: translateY(-12rem)">
                            <div class="col-md-12" id="distribution_tab">
                                <div class="col-md-12">
                              
                                        <div class="card" style="padding: 2rem 1rem">
                                            <div class="col-md-12">
                                              <center>  <h2>DISTRIBUTION DETAILS</h2> </center>
                                            </div>
                                            <div class="col-md-12">
                                                <label for="target_area">* Target Area for planting</label>
                                                <input  class='form-control' onkeyup='compute_bags();' onchange='compute_bags();' id='target_area' name='target_area' type="number" value="0">
                                                <label style="color: #888; font-size: 1rem; font-style: italic;" for="bags_computation" id="bags_computation">Equivalent bag(s): 0</label>
                                            </div>
            
                                            <div class="col-md-12">
                                                <label for="variety_select">* Variety</label>
                                                <select class="form-control form-select" name="variety_select" id="variety_select">
                                                    <option value="0">Select Variety</select>
                                                </select>


                                            </div>

                                           
                                            <div class="col-md-12" style="margin-top: 2rem;">
                                                <center>  <h2>LAST SEASON DATA</h2> </center>
                                            </div>

                                              <div class="col-md-12">
                                                <label for="yield_area">* Yield Data Last Season</label>
                                              </div>


                                            <div class="col-md-12" style="padding:0;">
                                                    <div class="col-md-4">              
                                                        <input type="number" class="form-control" id="yield_area" name="yield_area" placeholder="Harvest Area"> 
                                                        <span style="font-size: 1rem; font-style: italic;">Harvested area (ha)</span>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <input type="number" class="form-control"  id="yield_bags" name="yield_bags" placeholder="No. of Bag(s)"> 
                                                        <span style="font-size: 1rem; font-style: italic;">No. of bags</span>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <input type="number" class="form-control"  id="yield_weight" name="yield_weight" placeholder="Wt. per"> 
                                                        <span style="font-size: 1rem; font-style: italic;">Wt per bag (kg)</span>
                                                    </div>
                                            </div>
            

                                            <div class="col-md-12" style="margin-top: 2rem;">
                                                <center>  <h2>CURRENT SEASON DATA</h2> </center>
                                            </div>

                                              <div class="col-md-12 mt">
                                                <label for="crop_est"><strong>Crop Establishment Current Season</strong></label>
                                                    <div class="col-md-6">
                                                        <input type="radio" name="crop_est" id="direct" value="direct"> <label class="" for="direct"><i>Direct</i></label>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="radio" name="crop_est" id="transplanted" value="transplanted"> <label class="" for="transplanted"><i>Transplanted</i></label>
                                                    </div>
                                              
                                                </div>

                                                <div class="col-md-12 mt">
                                                    <label for="eco_system"><strong>Ecosystem Current Season</strong></label>
                                                    <br>
                                                        <div class="col-md-6">
                                                            <input type="radio" name="eco_system" id="irrigated" value="irrigated"> <label class="" for="irrigated"><i>Irrigated</i></label>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <input type="radio" name="eco_system" id="rainfed" value="rainfed"> <label class="" for="rainfed"><i>Rainfed</i></label>
                                                        </div>
                                                        
                                                        <center>
                                                            <select name="water_source" id="water_source" class="form-control form-select" style="width: 95%;">
                                                                <option value="0">Select Source</option>
                                                            </select>
                                                        </center>
                                                </div>

                                                <div class="col-md-12 mt" style="margin-bottom:10px;">
                                                    <label for="planting_month">Planting Week Current Season</label>
                                                        <div class="col-md-6">
                                                            <select name="planting_month" id="planting_month" class="form-control form-select" >
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
                                                            <select name="planting_week" id="planting_week" class="form-control form-select" >
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


                        </div>
                            
                        <div class="col-md-3 dis_tab" style="transform: translateY(-12rem)">
                            <div class="col-md-12" id="additional_tab">
                                <div class="col-md-12">
                              
                                        <div class="card" style="padding: 2rem 1rem">
                                            <div class="col-md-12">
                                              <center>  <h2>ADDITIONAL INFORMATION</h2> </center>
                                            </div>
                                          
                                            <div class="col-md-12 mt">
                                                <label for="">* Mother's Maiden Name</label>
                                            </div>
            
                                            <div class="col-md-12 mt">
                                                <div class="col-md-6">
                                                    <label for="mother_last_name">* Last Name</label>
                                                    <input type="text" class='form-control' id='mother_last_name' name='mother_last_name' placeholder="Last Name">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="mother_first_name">* First Name</label>
                                                    <input type="text" class='form-control' id='mother_first_name' name='mother_first_name' placeholder="First Name">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="mother_mid_name">Middle Name</label>
                                                    <input type="text" class='form-control' id='mother_mid_name' name='mother_mid_name' placeholder="Middle Name">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="mother_ext_name">Extension Name</label>
                                                    <input type="text" class='form-control' id='mother_ext_name' name='mother_ext_name' placeholder="Extension Name">
                                                </div>
                                            </div>
                                            
                                            <div class="_c" style="padding: 1rem;">
                                                <div class="col-md-12 mt">
                                                    <label for="birthdate">* Farmer's Birthdate</label>
                                                    <input type="text" class='form-control' id='birthdate' name='birthdate' value="{{date("m/d/Y")}}" >
                                                  
                                                </div>
    
                                                <div class="col-md-12 mt">
                                                    <label for="phone_number">* Phone Number</label>
                                                    <input type="text" class='form-control' id='phone_number' name='phone_number' placeholder="Contact Number">
                                                </div>
                                                <div class="col-md-12" style="margin-top:10px;">
                                                    <input type="checkbox" class="form-check" id="ip" name="ip"> <label for="ip" class="">Indigenous People </label>
                                                  
                                                </div>
                                                <div class="col-md-12" id="ip_name_div" style="margin-bottom: 10px;">
                                                    <input type="text" class='form-control' id='ip_name' name='ip_name' placeholder="IP Name">
                                                </div>
                                                <br>
                                                <div class="col-md-6">
                                                    <input type="checkbox" class="form-check" id="pwd" name="pwd"> <label for="pwd" class="">PWD</label>
                                                </div>
                
                                                <div class="col-md-12 mt" style="margin-bottom:10px;">
                                                    <label for="fca_name">FCA NAME</label>
                                                    <input type="text" class="form-control" id='fca_name' name='fca_name' placeholder="FCA Name">
                                                </div>
                                            </div>
            
                                        
                                          
            
                                        </div>
                                   
                                </div>
                            </div>


                        </div>

                        <div class="col-md-3 dis_tab" style="transform: translateY(-12rem);">
                            <div class="col-md-12" id="oth_dist_tab">
                                <div class="col-md-12">
                              
                                        <div class="card" style="padding: 2rem 1rem">
                                            <div class="col-md-12">
                                              <center>  <h2>OTHER DISTRIBUTION DETAILS</h2> </center>
                                            </div>
                                          
                                            <div class="col-md-12">
                                                <label for="kp_kit">* Received Knowledge Product Kit (KP-Kit) </label>
                                            </div>
                                          
                                        
                                            <div class="col-md-12">
                                             
                                                <div class="col-md-6">
                                                    <input type="radio" name="kp_kit" id="Yes" value="yes"> <label class="" for="Yes">Yes</label>
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="radio" name="kp_kit" id="No" value="no"> <label class="" for="No">No</label>
                                                </div>


                                            </div>
            
                                            <div class="col-md-12 mt">
                                                <label for="ayuda">Lagyan ng tsek and sumusunod kung ang magsasaka ay nakatangap ng ayuda sa pagsasaka</label>
                                                <div class="col-md-5">
                                                    <input type="checkbox" name="fertilizer" id="fertilizer"> <label class="" for="fertilizer">Fertilizer</label>
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="checkbox" name="cash_incentive" id="cash_incentive"> <label class="" for="cash_incentive">Cash Incentives</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="checkbox" name="credit_loan" id="credit_loan"> <label class="" for="credit_loan">Credit/Loan</label>
                                                </div>
                                            </div>

                                            <div class="col-md-12 mt">
                                                    <input type="checkbox" name="rep" id="rep">  <label for="rep" class="">Representative of Farmer Beneficiary?</label>
                                            </div>
                                         
                                            <div class="col-md-12" id="rep_info" style="margin-bottom: 12px;">
                                                <label for="rep_name">* Representative Name</label>
                                                <input type="text" class="form-control" id='rep_name' name='rep_name' placeholder="Representative Name">
                                                <label for="rep_id">* Type of ID</label>
                                                <input type="text" class="form-control" id='rep_id' name='rep_id' placeholder="Type of ID">
                                                <label for="rep_relationship">* Relationship</label>
                                                <input type="text" class="form-control" id='rep_relationship' name='rep_relationship' placeholder="Representative Relationship">
                                            </div>
                                            <div class="col-md-12">
                                                <label for="claimable"></label>
                                                <a style="margin-bottom:10px;" class='btn btn-success form-control' id='save_update' name='save_update' disabled>Save/Update</a>
                                            </div>



                                        </div>
                                   
                                </div>
                            </div>


                        </div>
    
                    
                </div>
                
                
                
           





                

  <div id="dialog" title="Prompt"></div>
{{-- MODAL --}}
<div class="modal fade" id="search_farmer_modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" >        
    <div class="modal-dialog">
      <div class="modal-content" style="width:150%;" >
        <div class="modal-header">
          
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <h2>Search Farmer</h2>
        </div>
        <div class="modal-body"  >
                <div class="row">
                    <div class="col-md-12">
                        <label for="search_bar">Search Farmer</label>
                        <input type="text" class="form-control" id="search_bar" name="search_bar" onkeyup="search();" placeholder="Search Here"> 
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="x_content form-horizontal form-label-left">
                        <table class="table table-hover table-striped table-bordered" id="dataTBL">
                            <thead>
                                <th>RSBSA #</th>
                                <th>Farmer Name</th>
                  
                                <th style="width: 15%;">Area</th>
                                <th>Sex</th>
                                <th>Birthdate</th>
                                <th>Action</th>
                            </thead>
                            <tbody id='databody'>
                                
                            </tbody>
                        </table>
                     </div>      
                    </div>
                </div>
               
               
        </div>
      </div>
    </div>
  </div>


  @include('onlineEncoding.modal_parcelary')

  

  {{-- SCANNER RCEF ID --}}
  <div class="modal fade" id="rcef_id_modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">        
    <div class="modal-dialog">
      <div class="modal-content" >
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            RCEF ID SCAN

        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <center>
                    <video style="width: 50%;" id="rcef_id_preview" class="rcef_id_preview"></video> 
                    </center>
                </div>
            </div>
                   
        </div>
      </div>
    </div>
  </div>


    {{-- SCANNER INTERVENTION --}}
  <div class="modal fade" id="intervention_scanner_modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">        
    <div class="modal-dialog">
      <div class="modal-content" >
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            Scan Intervention ID

        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <center>
                    <video style="width: 50%;" id="intervention_id_preview" class="intervention_id_preview"></video> 
                    </center>
                </div>
            </div>
                   
        </div>
      </div>
    </div>
  </div>

@endsection

@push('scripts')

<script>

$("#farmer_tbl").DataTable({
            "order": [],
            "pageLength": 25
        });

$("#new_farmer_vs").on("click", function(){


});


$("#search_farmer_vs").on("click", function(){
    var rsbsa_rcef_id = $("#rsbsa_rcef_id").val();
    var fname_vs = $("#fname_vs").val();
    var lname_vs = $("#lname_vs").val();
    var mname_vs = $("#mname_vs").val();
    var ename_vs = $("#ename_vs").val();
    var province_select = $("#province_select").val();
   
    if(province_select == "0"){
        dialog_prompt("Please Select Province First"); return ;
    }


            $('#farmer_tbl').DataTable().clear();
            $('#farmer_tbl').DataTable({
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
                    "url": "{{route('vs.online.select.farmer')}}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        rsbsa_rcef_id: rsbsa_rcef_id,
                        fname_vs: fname_vs,
                        lname_vs: lname_vs,
                        mname_vs: mname_vs,
                        ename_vs: ename_vs,
                        province_select: province_select,
                    }
                },
                "columns":[
                    {"data": "rsbsa_no", 'orderable': false},
                    {"data": "rcef_id", 'searchable': true, 'orderable': false},
                    {"data": "lastName", 'searchable': true, 'orderable': true},
                    {"data": "firstName", 'searchable': true, 'orderable': true},
                    {"data": "midName", 'searchable': true, 'orderable': true},
                    {"data": "extName", 'searchable': true, 'orderable': true},
                    // {"data": "home_add", 'searchable': false, 'orderable': false},
                    {"data": "birthdate", 'searchable': false, 'orderable': false},
                    {"data": "contact_number", 'searchable': false, 'orderable': false},
                    {"data": "mother", 'searchable': true, 'orderable': true},
                    {"data": "list_version", 'searchable': true, 'orderable': true},
                    {"data": "action", 'searchable': false , 'orderable': false}
                  

                   

                ]
            });

})

      




</script>

<script type="text/javascript">
    // INITIAL VARIABLE
    var farmer_claimable = 0;
    var prv_code ="";
    var mode = "";
    var farmer_id_address = 0;


$("#birthdate").datepicker();

    function compute_bags(){
        var area =  $("#target_area").val();
        var bags = Math.ceil(area *2);

        $("#bags_computation").empty().text("Equivalent bag(s): "+bags);
    }

     $("#farmer_profile_tab").hide("fast");
    $("#distribution_tab").hide("fast");
    $("#additional_tab").hide("fast");
    $("#oth_dist_tab").hide("fast");

    $("#ip_name_div").hide("fast");
    $("#rep_info").hide("fast");

    $('#province_select').select2();
    $('#municipality_select').select2();
    $('#dop_select').select2();
    $('#variety_select').select2();
    $('#water_source').select2();
    $('#planting_month').select2();
    $('#planting_week').select2();



    
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


            $('#search_farmer_modal').on('show.bs.modal', function (e) {
               $("search_bar").val("");
                search();
            });


$("#scan_id").on("click", function(){
    Instascan.Camera.getCameras().then(function (cameras){
        if(cameras.length>0){
            rcef_id_scanner.start(cameras[0]);
            $('[name="options"]').on('change',function(){
                if($(this).val()==1){
                    if(cameras[0]!=""){
                        rcef_id_scanner.start(cameras[0]);
                    }else{
                        alert('No Front camera found!');
                    }
                }else if($(this).val()==2){
                    if(cameras[1]!=""){
                        rcef_id_scanner.start(cameras[1]);
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
    $('#rcef_id_modal').modal('show');
});

$(document).on('hide.bs.modal','#rcef_id_modal', function () {
        Instascan.Camera.getCameras().then(function (cameras){
        if(cameras.length>0){
            rcef_id_scanner.stop(cameras[0]);           
        }else{
            console.error('No cameras found.');
            alert('No cameras found.');
        }
        }).catch(function(e){
            console.error(e);
            alert(e);
        });
    });

// QR CODE FOR RCEF ID
var rcef_id_scanner = new Instascan.Scanner({ video: document.getElementById('rcef_id_preview'), scanPeriod: 5, mirror: false });
rcef_id_scanner.addListener('scan',function(content){  
    if(content !="" &&content != null && content != undefined )      
        select_farmer(content);
        mode = "scan";
    $('#rcef_id_modal').modal('hide');
    //getFarmerData(content);
}); 

/* 
Instascan.Camera.getCameras().then(function (cameras){
        if(cameras.length>0){
            rcef_id_scanner.stop(cameras[0]);           
        }else{
            console.error('No cameras found.');
            alert('No cameras found.');
        }
        }).catch(function(e){
            console.error(e);
            alert(e);
        }); */


//FOR INTERVENTION CARDS  -------------------------------------------------------------------------------------------
// QR CODE FOR RCEF ID
var intervention_scanner = new Instascan.Scanner({ video: document.getElementById('intervention_id_preview'), scanPeriod: 5, mirror: false });
intervention_scanner.addListener('scan',function(content){  
    if(content !="" &&content != null && content != undefined )      
    $("#da_intervention_card").val(content);

    $('#intervention_scanner_modal').modal('hide');
    //getFarmerData(content);
}); 


/* Instascan.Camera.getCameras().then(function (cameras){
        if(cameras.length>0){
            intervention_scanner.stop(cameras[0]);           
        }else{
            console.error('No cameras found.');
            alert('No cameras found.');
        }
        }).catch(function(e){
            console.error(e);
            alert(e);
        }); */


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



    function dialog_prompt(msg){
        $("#dialog").empty().append(msg);
                $( "#dialog" ).dialog("open").effect("shake");

                window.setTimeout(function(){
                    $( "#dialog" ).dialog("close");
                }, 1000);

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
                    farmer_info_vs =farmer_info_vs +" <br> Home: <small>"+ result.home + "</small>"
                    farmer_info_vs =farmer_info_vs +" <br> Overall Crop: <small>"+ result.total_size + "</small>"
                    
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


    $("#save_update").on("click", function(){
 
        
        var da_intervention_card = $("#da_intervention_card").val();
        var province = $("#province_select").val();
        var municipality = $("#municipality_select").val();
        var dop = $("#dop_select").val();
        var rcef_id = $("#rcef_id").val();
        var rsbsa_control_no = $("#rsbsa_number").val();
        var claimed_area = $("#target_area").val();
        var variety = $("#variety_select").val();
        var yield_area = $("#yield_area").val();
        var yield_bags = $("#yield_bags").val();
        var yield_weight = $("#yield_weight").val();
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
        var enrolled_area = $("#enrolled_area").text();
       
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
     


        // TRAPPER

        if(parseFloat(claimed_area) <= 0){
            dialog_prompt("No Claim Area");
            return ;
        }else{
           var check_bags =  farmer_claimable - Math.ceil(claimed_area *2);
            if(check_bags < 0){
                dialog_prompt("Claiming Exceeds on Claimable");
                return;
            }

           enrolled_area = parseFloat(enrolled_area)
            if(parseFloat(claimed_area) > enrolled_area){
                dialog_prompt("Claiming Area is higher than the Enrolled Area");
                    return;
            }

        }

        if(variety == ""){ dialog_prompt("No Variety");return ; }
        if(yield_area == "" || yield_bags == "" || yield_weight == ""){ dialog_prompt("Please Complete Last Season Yield Data"); return ;}    
        // if(crop_est == undefined){ dialog_prompt("Please Select Crop Establishment");return ;  }
        // if(eco_system == undefined){dialog_prompt("Please Select Eco System");return ;}
        // if(water_source == "0"){ dialog_prompt("Please Water source"); return ; }
        // if(planting_month == "0"){ dialog_prompt("Please Select Planting Month"); return ; }
        // if(planting_week == "0"){ dialog_prompt("Please Select Planting Week"); return ; }
        if(mother_last_name == ""){ dialog_prompt("Please Input Mother Last Name"); return ; }
        if(mother_first_name == ""){ dialog_prompt("Please Input Mother First Name"); return ; }
        if(birthdate == ""){ dialog_prompt("Please Input Farmer Birth Date"); return ; }
        if(kp_kit == undefined){dialog_prompt("Please select if farmer received KP Product"); return;}

        if(prv_code == ""){dialog_prompt("PRV CODE EMPTY"); return;}


        var yesNo = confirm("Save Distribution Data?");
            if(yesNo){
                HoldOn.open(holdon_options);
                    $.ajax({
                        type: 'POST',
                        url: "{{route('mod.online.save.distribution')}}",
                        data: {
                            _token: "{{ csrf_token() }}",
                            da_intervention_card: da_intervention_card,
                            prv_code: prv_code,
                            dop: dop,
                            province: province,
                            municipality:municipality,
                            rcef_id: rcef_id, 
                            rsbsa_control_no: rsbsa_control_no, 
                            claimed_area: claimed_area, 
                            variety: variety, 
                            yield_area: yield_area, 
                            yield_bags: yield_bags, 
                            yield_weight: yield_weight, 
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
                            mode: mode, 
                            farmer_id_address: farmer_id_address
                        },
                        dataType: 'json',
                        success: function(result){
                            alert(result);
                            if(result == "Distribution Success"){
                                $("#da_intervention_card").val("");
                                $("#rcef_id").val("");
                                $("#farmer_name").val("");
                                $("#rsbsa_number").val("");
                                $("#target_area").val("0");
                                $("#yield_area").val("");
                                $("#yield_bags").val("");
                                $("#yield_weight").val("");
                                $('input[name="crop_est"]').prop( "checked", false );
                                $('input[name="eco_system"]').prop( "checked", false );
                                $("#water_source").empty();
                                $("#planting_month").val("0").change();
                                $("#planting_week").val("0").change();
                                $("#mother_last_name").val("");
                                $("#mother_first_name").val("");
                                $("#mother_mid_name").val("");
                                $("#mother_ext_name").val("");
                                $("#birthdate").val("{{date('m/d/Y')}}");
                                $("#phone_number").val("");
                                $("#enrolled_area").text("0");
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

                                mode = "";
                                farmer_claimable = 0;
                                farmer_id_address = 0;
                                $("#distribution_tab").hide("fast");
                                $("#additional_tab").hide("fast");
                                $("#oth_dist_tab").hide("fast");

                            }
                           

                           
                            


                            downloadData();
                            HoldOn.close();
                        },
                        error: function(result){
                        
                            downloadData();
                            HoldOn.close();
                        }
                        });

            }



    });



    function search(){
        var province = $('#province_select').val();
        var municipality = $("#municipality_select").val();
        var search_bar = $("#search_bar").val();
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
                    "url": "{{route('mod.online.search.farmer')}}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        "province": province,
                        "municipality": municipality,
                        "search_bar": search_bar
                    }
                },
                "columns":[
                    {"data": "rsbsa","width":"20%"  },
                    {"data": "name","width":"40%"  },
                    {"data": "final_area","className": "text-left","width":"10%" },
                    {"data": "sex","className": "text-center", "width":"5%"},
                    {"data": "birthdate","className": "text-center","width":"10%"},
                    {"data": "action" }
                ]
            });
    
    
    
            }
   
   


    $( "#dialog" ).dialog({
                autoOpen: false,
                show: {
                    effect: "blind",
                    duration: 400
                },
                hide: {
                    effect: "explode",
                    duration: 400
                }
                });




    function downloadData(){
        
                $("#farmer_name").val("");
                $("#rsbsa_number").val("");
                $("#da_intervention_card").val("");
                
                $("#enrolled_area").empty();
                $("#claimable").empty();


   

        var province = $("#province_select").val();
        var municipality = $("#municipality_select").val();
        var dop = $("#dop_select").val();
        
        if(dop == "0"){
            $("#dialog").empty().append("Please select Location");
            $( "#dialog" ).dialog("open");

            $("#farmer_profile_tab").hide("fast");
            $("#distribution_tab").hide("fast");
                        $("#additional_tab").hide("fast");
                        $("#oth_dist_tab").hide("fast");
            $("#variety_available").empty();
            $("#grandTotal").empty();
        }else{
            $( "#dialog" ).dialog("close");
            // variety_available
            // grandTotal
            $("#variety_available").empty();
            $("#grandTotal").empty();
            var grand_total = 0;
            HoldOn.open(holdon_options);
                    $.ajax({
                        type: 'POST',
                        url: "{{route('mod.online.stocks')}}",
                        data: {
                            _token: "{{ csrf_token() }}",
                            province: province,
                            municipality:municipality,
                            dop: dop
                        },
                        dataType: 'json',
                        success: function(result){
                            $("#variety_select").empty();
                            $.each(result, function (i, d) {
                                if(i % 2 == 0 ){
                                    $("#variety_available").append("<div class='row'>");
                                    $("#variety_available").append("<div class='col-md-12'>"); 
                                }
                                     

                                if(d['remaining'] > 0){
                                    $("#variety_available").append("<div style='font-size:20px; color:#2a3f54; background-image: linear-gradient(to right,#26b99a, white);' class='btn btn-dark col-md-5'>"+d['seed_variety']+" ("+d['remaining']+")</div>");

                                    $("#variety_select").append("<option value='"+d['seed_variety']+"'>"+d['seed_variety']+"</option>");
                                }else{
                                    $("#variety_available").append("<div style='font-size:20px; color:white; background-image: linear-gradient(to right,black, white);' class='btn btn-dark col-md-5' >"+d['seed_variety']+" ("+d['remaining']+")</div>");
                                }

                               grand_total = grand_total + d['remaining'];
                                
                                if(i % 2 == 0){
                                    $("#variety_available").append('</div>');
                                    $("#variety_available").append('</div>');
                                }
                               
                                }); 
                                $("#grandTotal").append('<div class="btn btn-success col-md-12" >'+'<label style="font-size:15px;font-weight:bold;">GRAND TOTAL</label> <br> <label style="font-size:20px;font-weight:bold;">'+grand_total.toFixed(0)+' bag(s) </label><br> <label style="font-style: italic;font-size:15px;font-weight:bold;">(available for distribution)</label> </div>');
                               
                               
                                // $("#farmer_profile_tab").show("fast");
                                // $("#distribution_tab").hide("fast");
                                // $("#additional_tab").hide("fast");
                                // $("#oth_dist_tab").hide("fast");


                                // if(grand_total > 0 ){
                                //     $("#farmer_profile_tab").show("fast");
                                //     $("#distribution_tab").hide("fast");
                                //     $("#additional_tab").hide("fast");
                                //     $("#oth_dist_tab").hide("fast");
                                // }else{
                                //     $("#farmer_profile_tab").hide("fast");
                                //     $("#distribution_tab").hide("fast");
                                //     $("#additional_tab").hide("fast");
                                //     $("#oth_dist_tab").hide("fast");

                                    
                                // }


                            HoldOn.close();
                        },
                        error: function(result){
                           
                         
                            HoldOn.close();
                        }
                        });



        }



    }

    function moding(){
        mode = "search";
    }


    function select_farmer(rcef_id){
        $("#search_farmer_modal").modal("hide");
        $("#rcef_id").val(rcef_id);

        var province = $("#province_select").val();
        var municipality = $("#municipality_select").val();

        $.ajax({
            type: 'POST',
            url: "{{route('mod.online.select.farmer')}}",
            data: {
                _token: "{{ csrf_token() }}",
                province: province,
                municipality:municipality,
                rcef_id: rcef_id
            },
            dataType: 'json',
            success: function(result){
                if(result["msg"] != ""){
                    alert(result["msg"]);
                    $("#save_update").attr("disabled", true);
                        mode = "";
                        farmer_id_address = 0;
                        farmer_claimable = 0;
                        prv_code = "";
                        $("#distribution_tab").hide("fast");
                        $("#additional_tab").hide("fast");
                        $("#oth_dist_tab").hide("fast");
                }else{
                       
                }
                    $("#farmer_name").val(result["farmer_name"]);
                    $("#rsbsa_number").val(result["rsbsa_number"]);
                    $("#enrolled_area").empty().append(result["enrolled_area"]);
                    $("#claimable").empty().append(result["claimable"]);
                    $("#da_intervention_card").val(result['da_intervention_card']);
                    prv_code = result['prv_code'];
                    farmer_id_address = result["fif_id"];
                   

                    $("#mother_first_name").val(result["mother_first_name"]);
                    $("#mother_last_name").val(result["mother_last_name"]);
                    $("#mother_mid_name").val(result["mother_mid_name"]);
                    $("#mother_ext_name").val(result["mother_ext_name"]);
                    
                    $("#birthdate").val(result["birthdate"]);
                    $("#phone_number").val(result["tel_no"]);
                    
                    $("#fca_name").val(result["fca_name"]);
                    
                    if(result["ip"]=="1"){
                        $('#ip').prop( "checked", true );
                        $("#ip_name_div").show("fast");
                        $("#ip_name").val(result["ip_name"]);

                    }else{
                        $('#ip').prop( "checked", false );
                    }
                    
                    if(result["pwd"]=="1"){
                        $('#pwd').prop( "checked", true );
                
                        
                    }else{
                        $('#pwd').prop( "checked", false );
                    }
                      





                    if(result["remaining"] > 0){
                        farmer_claimable = result['remaining'];
                        
                        $("#distribution_tab").show("fast");
                        $("#additional_tab").show("fast");
                        $("#oth_dist_tab").show("fast");

                        $("#save_update").removeAttr("disabled");

                    }else{
                        mode = "";
                        farmer_claimable = 0;
                        farmer_id_address = 0;
                        $("#distribution_tab").hide("fast");
                        $("#additional_tab").hide("fast");
                        $("#oth_dist_tab").hide("fast");
                    }

                HoldOn.close();
            },
            error: function(result){
                
                
                HoldOn.close();
            }
            });


    }


</script>




<script>     
   
    $('#province_select').change(function(){
        var province = $('#province_select').val();
        $("#farmer_profile_tab").hide("fast");
        $("#distribution_tab").hide("fast");
        $("#additional_tab").hide("fast");
        $("#oth_dist_tab").hide("fast");

        $("#variety_available").empty();
        $("#grandTotal").empty();
        $("#municipality_select").empty().append("<option value='0'>Select Municipality</option>");
        $("#dop_select").empty().append("<option value='0'>Select DropOff Point</option>");
        HoldOn.open(holdon_options);
                    $.ajax({
                        type: 'POST',
                        url: "{{route('mod.online.municipal')}}",
                        data: {
                            _token: "{{ csrf_token() }}",
                            province: province
                        },
                        dataType: 'json',
                        success: function(result){
                            $.each(result, function (i, d) {
                                    $('select[id="municipality_select"]').append('<option value="' + d.municipality + '">' + d.municipality + '</option>');
                                }); 
                            HoldOn.close();
                        },
                        error: function(result){
                           
                         
                            HoldOn.close();
                        }
                        });

                       
    });

    

    $('#municipality_select').change(function(){
        var province = $('#province_select').val();
        var municipality = $("#municipality_select").val();

        $("#dop_select").empty().append("<option value='0'>Select DropOff Point</option>");
        $("#farmer_profile_tab").hide("fast");
        $("#distribution_tab").hide("fast");
        $("#additional_tab").hide("fast");
        $("#oth_dist_tab").hide("fast");

        $("#variety_available").empty();
        $("#grandTotal").empty();
        HoldOn.open(holdon_options);
            $.ajax({
                type: 'POST',
                url: "{{route('mod.online.dop')}}",
                data: {
                    _token: "{{ csrf_token() }}",
                    province: province,
                    municipality: municipality
                },
                dataType: 'json',
                success: function(result){
                    $.each(result, function (i, d) {
                            $('select[id="dop_select"]').append('<option value="' + d.prv_dropoff_id + '">' + d.dropOffPoint + '</option>');
                        }); 
                    HoldOn.close();
                },
                error: function(result){
                   
                 
                    HoldOn.close();
                }
                });
    
           
            });
        $('#dop_select').on("change", function(){
            $("#farmer_profile_tab").hide("fast");
            $("#distribution_tab").hide("fast");
            $("#additional_tab").hide("fast");
            $("#oth_dist_tab").hide("fast");

            $("#variety_available").empty();
            $("#grandTotal").empty();

        });

           
	</script>
@endpush

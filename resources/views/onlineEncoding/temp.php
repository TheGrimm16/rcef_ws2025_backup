
                <form  id="SaveData">
                    <div class="row" style="display:  none" id="FarmerProfile">
                        <div class="col-md-3">                       
                            <div class="x_panel"> 
                              <CENTER> <p><strong>FARMER PROFILE</strong></p></CENTER>
                                <div class="row">
                                    <div class="col-md-12">                                    
                                       <input type="text" class="form-control" placeholder="Farmer Name Search" id="famer_name">          
                                       <CENTER>  <a class="QrcodeBtn" href="#">Click to if have QR Code</a>  </CENTER>                                                  
                                    </div>     
    
                                    <div class="col-md-12">
                                        <label for="farmer_rsbsa">* RSBSA Stub / Control #</label>
                                        <input type="text" class="form-control" placeholder="RSBSA" id="farmer_rsbsa" name="farmer_rsbsa">        
                                                           
                                     </div> 
    
                                     <div class="col-md-12">
                                        <label for="area">Enrolled RSBSA Area (ha)</label>
                                        <input type="number" class="form-control" placeholder="0" id="area" name="area">                                                               
                                     </div> 
    
                                     <div class="col-md-12">
                                        <label for="bags">Claimed bags (20kg/bag)</label>
                                        <input type="number" class="form-control" placeholder="0" id="bags" name="bags">                                                               
                                     </div>                                                                                                  
                                </div>
                                <br>
                                <div class="row" id="SaveBtn_div" style="display: none">
                                    <div class="col-md-12">
                                        <button type="submit" class="form-control btn btn-success" id="SaveBtn">Save / Update</button>                                                              
                                      </div> 
                                </div>
                            </div>
                        </div>
    
                      
                            <div id="nextSection" style="display: none">
    
                                <div class="col-md-3">                       
                                    <div class="x_panel"> 
                                      <CENTER> <p><strong>Farmer Distribution Profile</strong></p></CENTER>
                                        <div class="row">
                                            <div class="col-md-12">
                                            <label for="da_id">DA Issued ID</label>  
                                                <div class="input-group">                                                                   
                                                    <input  name="da_id" id="da_id" type="text" class="form-control"  placeholder="#####">
                                                    <span class="input-group-addon"><a id="da_id_btn"><i style="font-size: 20px" class="fa fa-qrcode fa-6"></i></a></span>
                                                </div>
                                            </div>
            
            
                                             <div class="col-md-6">
                                                <Label for="variety">Select Variety</Label>                            
                                                    <select class="form-control variety"   name="variety" id="variety">
                                                
                                                    </select>                                                               
                                             </div>   
                                             
                                             <div class="col-md-6">
                                                <Label for="province_select">*Area to Claim</Label>                            
                                                    <input type="number" class="form-control" id="area_claim"  name="area_claim">                                                            
                                             </div>  
                                             
                                            <div class="col-md-12">
                                                <label for="Qrcode">*QR Code</label>  
                                                    <div class="input-group">                                                                   
                                                        <input id="Qrcode" type="text" class="form-control" name="Qrcode" placeholder="#####">
                                                        <span class="input-group-addon QrcodeBtn"><a id="QrcodeBtn"><i style="font-size: 20px" class="fa fa-qrcode fa-6"></i></a></span>
                                                    </div>
                                            </div>
            
                                            <div class="col-md-12">
                                                <input type="checkbox" class="" id="indigenous" name="indigenous" value="">
                                                <label for="indigenous">Check box if member of indigenous people</label><br>
                                                <input type="checkbox" class="" id="pwd" name="pwd" value="">
                                                <label for="pwd">Check box if person with disability (PWD)</label><br>
                                            </div>
            
            
                                            <div class="col-md-12">
                                                <label for="brgy">* Barangay</label>
                                                <input type="text" class="form-control" placeholder="Brgy." id="brgy" name="brgy">        
                                                                   
                                             </div> 
            
            
                                             <div class="col-md-6">
                                                <label for="fname">* First Name</label>
                                                <input type="text" class="form-control" placeholder="First Name" id="fname" name="fname">        
                                                                   
                                             </div> 
            
                                             <div class="col-md-6">
                                                <label for="ext">* Extension/Suffix</label>
                                                <input type="text" class="form-control" placeholder="Ext." id="ext" name="ext">        
                                                                   
                                             </div> 
            
                                             <div class="col-md-12">
                                                <label for="mname">* Middle Namae</label>
                                                <input type="text" class="form-control" placeholder="Middle Name" id="mname" name="mname">                                                               
                                             </div> 
                                             <div class="col-md-12">
                                                <label for="lname">* Last Name</label>
                                                <input type="text" class="form-control" placeholder="Last Name" id="lname" name="lname">                                                               
                                             </div> 
            
                                             <div class="col-md-12">
                                                <label for="birthday">* Birthdate</label>
                                                <input type="text" class="form-control" placeholder="00/00/0000" id="birthday" name="birthday">                                                               
                                             </div> 
            
                                           
            
            
            
                                        </div>
                                    </div>
                                </div>
            
            
                                <div class="col-md-3">                       
                                    <div class="x_panel"> 
                                      <CENTER> <p><strong>Mother's Maiden Name</strong></p></CENTER>
                                        <div class="row">
                                             <div class="col-md-6">
                                                <label for="MFname">* First Name</label>
                                                <input type="text" class="form-control" placeholder="First Name" id="MFname" name="MFname">        
                                                                   
                                             </div> 
            
                                             <div class="col-md-6">
                                                <label for="MExt">* Extension/Suffix</label>
                                                <input type="text" class="form-control" placeholder="Ext." id="MExt"  name="MExt">        
                                                                   
                                             </div> 
            
                                             <div class="col-md-12">
                                                <label for="MMname">* Middle Namae</label>
                                                <input type="text" class="form-control" placeholder="Middle Name" id="MMname" name="MMname">                                                               
                                             </div> 
                                             <div class="col-md-12">
                                                <label for="Mlname">* Last Name</label>
                                                <input type="text" class="form-control" placeholder="Lat Name" id="Mlname" name="Mlname">                                                               
                                             </div> 
                                        </div>
                                    </div>
            
                                    <div class="x_panel"> 
                                        <CENTER> <p><strong>Other Info</strong></p></CENTER>
                                          <div class="row">
                                               <div class="col-md-12">
                                                  <label for="brgy">Phone Number</label>
                                                  <input type="text" class="form-control" placeholder="09xxxxxxxxx" id="phone_number" name="phone_number">        
                                                                     
                                               </div> 
              
                                               <div class="col-md-6">
                                                  <label for="brgy">* Harvest Data</label>
                                                  <input type="text" class="form-control" placeholder="Season" id="season" name="season">                                                                 
                                               </div> 
                                               <div class="col-md-6">
                                                <label for="brgy">* Year</label>
                                                <input type="text" class="form-control" placeholder="Year" id="year" name="year">                                                                 
                                             </div>
                                             
                                             
                                             <div class="col-md-6">
                                                <label for="totalProduction">Total Production(# bags)</label>
                                                <input type="number" class="form-control" placeholder="0" id="totalProduction" name="totalProduction">                                                                 
                                             </div> 
                                             <div class="col-md-6">
                                              <label for="brgy">Ave. weight per bag(kg)</label>
                                              <input type="number" class="form-control" placeholder="0" id="weightPerKg" name="weightPerKg">                                                                 
                                           </div>
            
                                            <div class="col-md-12">
                                            <input type="checkbox" class="" id="ifRepresentative" name="ifRepresentative" value="">
                                            <label for="ifRepresentative">Check box if representative</label><br>                                
                                            </div>
            
                                            <div id="ifRepresentative_view" style="display: none">
                                                <div class="col-md-12">
                                                    <label for="brgy">*Representative Name</label>
                                                    <input type="text" class="form-control" placeholder="Juan" id="repName" name="repName">                                                                 
                                                 </div>
            
                                                 <div class="col-md-12">
                                                    <label for="typeOfId">*Type of ID Presented</label>                                        
                                                    <select id="typeOfId" name="typeOfId" class="form-control">
                                                        <option value="">Select Type of ID</option>
                                                        <option value="Barangay Certificate">Barangay Certificate</option>
                                                       <option value="OFW">OFW</option>
                                                       <option value="Passport">Passport</option>
                                                       <option value="PhilHealth">PhilHealth</option>
                                                       <option value="Postal">Postal</option>
                                                       <option value="PRC">PRC</option>
                                                       <option value="Senior">Senior Citizen</option>
                                                       <option value="SSS">SSS</option>
                                                       <option value="TIN">TIN Card</option>
                                                       <option value="Voter">Voter</option>
                                                       <option value="Other">Other</option>
                                                    </select>                                                               
                                                 </div>
            
                                                 <div class="col-md-12 otherID" style="display: none">
                                                    <label for="otherID">*Other ID</label>                                        
                                                    <input type="text" class="form-control" placeholder="" id="otherID" name="otherID">                                                    
                                                 </div>
            
                                                 <div class="col-md-12">
                                                    <label for="relationship">*Nature of Relationship</label>                                        
                                                    <select  id="relationship" class="form-control">
                                                        <option value="">Select Nature of Relationship</option>
                                                        <option value="Spouse">Spouse</option>
                                                        <option value="Sibling">Sibling</option>
                                                        <option value="Son/Daughter">Son/Daughter</option>
                                                        <option value="Farm worker">Farm worker</option>
                                                        <option value="Friend">Friend</option>
                                                        <option value="Other">Other</option>
                                                     </select>                                                               
                                                 </div>
            
                                                 <div class="col-md-12 otherRelationShip" style="display: none">
                                                    <label for="otherOther">*Other Relationship</label>                                        
                                                    <input type="text" class="form-control" placeholder="" id="otherRelationShip" name="otherRelationShip">                                                    
                                                 </div>
            
                                            </div>                               
                                          </div>
                                      </div>    
                                </div>
        
                                
                                <div class="col-md-3">                       
                                      
                                    <div class="x_panel"> 
                                        <CENTER> <p><strong>Crop Details</strong></p></CENTER>
                                        <label for="brgy">Crop Establishment</label>
                                          <div class="row">
                                          
        
                                               <div class="col-md-6">
                                                   <input type="checkbox"  name="dseeding" id="dseeding" name="dseeding" value="">
                                                    <label for="dseeding">Direct Seeding</label><br>                                                                                                          
                                               </div> 
                                               <div class="col-md-6">                                        
                                                    <input type="checkbox"  name="trans" id="trans" name="trans" value="">
                                                    <label for="trans">Transplanting</label>                                                                     
                                              </div> 
        
                                              <div class="col-md-12 display_SeedlingAge" style="display: none">
                                                <label for="SeedlingAge">Seedling Age(# of days)</label>
                                                <input type="number" class="form-control" placeholder="0." id="SeedlingAge" name="SeedlingAge">                                                               
                                             </div>  
                                          </div>
        
        
                                          <label for="brgy">Crop Ecosystem</label>
                                            <div class="row">
                                            
          
                                                 <div class="col-md-6">
                                                     <input type="checkbox"  name="irrigated" id="irrigated"   name="irrigated" value="">
                                                      <label for="irrigated">Irrigated</label><br>                                                                                                          
                                                 </div> 
                                                 <div class="col-md-6">                                        
                                                      <input type="checkbox"  name="trans" id="rainfed" name="rainfed"  value="">
                                                      <label for="rainfed">Rainfed</label>                                                                     
                                                </div> 
          
                                                <div class="col-md-12 ecosystemDiv" style="display: none">
                                                    <div class="col-md-6">
                                                                                                                                                               
                                                      </div> 
                                                      <div class="col-md-6">                                                   
                                                            <div class="col-md-12">
                                                                <input type="checkbox"  name="low_rainfed" id="low_rainfed" name="low_rainfed" value="">
                                                                <label for="low_rainfed">Rainfed - Lowland</label>  
                                                            </div>
                                                            <div class="col-md-12">
                                                                <input type="checkbox"  name="Upland_irrigated" id="Upland_irrigated" name="Upland_irrigated" value="">
                                                           <label for="Upland_irrigated">Rainfed - Upland</label>  
                                                            </div>                                 
                                                                                                                        
                                                     </div> 
                                                                      
                                               </div>  
        
        
                                               <div class="col-md-12">                                                                                
                                                 <label for="rainfed">Date of Sowing (WS2022)</label>                                                                     
                                                <select class="form-control" id="month_Sowing" name="month_Sowing">
                                                    <option value="">Select Month</option>
                                                    <option value="January">January</option>
                                                    <option value="February">February</option>
                                                    <option value="March">March</option>
                                                    <option value="April">April</option>
                                                    <option value="May">May</option>
                                                    <option value="June">June</option>
                                                    <option value="July">July</option>
                                                    <option value="August">August</option>
                                                    <option value="September">September</option>
                                                    <option value="October">October</option>
                                                    <option value="November">November</option>
                                                    <option value="December">December</option>
                                                    </select>
                                                    <br>
                                                    <select class="form-control" id="week_Sowing" name="week_Sowing">
                                                        <option value="">Select Week</option>  
                                                        <option value="1st Week">1st Week</option>
                                                        <option value="2nd Week">2nd Week</option>
                                                        <option value="3rd Week">3rd Week</option>
                                                        <option value="4th Week">4th Week</option>
                                                      
                                                    </select>
                                                </div> 
                                            </div>
                                      </div>    
                                </div>
        
                                
                                <div class="col-md-3">                       
                                      
                                    <div class="x_panel"> 
                                        <CENTER> <p><strong>Other Details</strong></p></CENTER>
                                        <p>Lagyan ng tsek ang mga sumusunod kung ang magsasaka ay nakatanggap ng information, Education and Communication(IEC) Materials :</p>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label for="kitsReceive">KP Kits</label>                                        
                                                <input type="text" class="form-control" placeholder="# of kits received" id="kitsReceive" name="kitsReceive">                                                    
                                             </div>
        
                                        </div>    
        
                                        <p>Lagyan ng tsek ang sumusunod kung ang magsasaka ay nakatanggap ng ayuda sa pag sasaka</p>
        
                                        <div class="row">
                                            <div class="col-md-6">
                                                  <input type="checkbox"  name="fertilizer" id="fertilizer"  name="fertilizer" value="">
                                                    <label for="fertilizer">Fertilizer</label><br>                                                                                                          
                                              </div> 
                                              <div class="col-md-6">                                        
                                                    <input type="checkbox"  name="cIncentive" id="cIncentive" name="cIncentive" value="">
                                                    <label for="cIncentive">Cash Incentive</label>                                                                     
                                             </div>
                                             <div class="col-md-12">                                        
                                                 <input type="checkbox"  name="cloan" id="cloan" name="cloan" value="">
                                                 <label for="cloan">Creadit / Loan</label>                                                                     
                                            </div> 
        
                                        </div>    
        
                                </div>
        
                            </div>
        
                        </div>   
    
                        <input type="hidden" id="farmerID"  name="farmerID">		
                        <input type="hidden" id="rsbsa" name="rsbsa">	
                        </form>
                                                    
                </div>
            </div>
    
            <div class="row">
                
            </div>
        
        </div>
    









        

    {{-- modal --}}
    <div class="modal fade bd-example-modal-lg" id="ModalForFarmer" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">        
        <div class="modal-dialog modal-lg">
            
          <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-striped table-bordered tbl" id="farmersTbl">
                    <thead>
                        <tr>
                            <th style="width: auto;">RSBSA</th>
                            <th style="width: auto;">Last Name</th>
                            <th style="width: auto;">First Name</th>
                            <th style="width: auto;">Middle Name</th>
                            <th style="width: auto;">Ext</th>
                            <th style="width: auto;">Sex</th>
{{--                             <th style="width: auto;">Mothers name</th> --}}
                            <th style="width: auto;">Area</th>                       
                            <th style="width: auto;">Action</th>      
                        </tr>
                    </thead>
                </table>
            </div>
          </div>
        </div>
      </div>

 {{-- modal --}}
 <div class="modal fade" id="ModalForQRCodScanner" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">        
    <div class="modal-dialog">
        
      <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <video id="preview" class="preview_qr_id"></video>    
                </div>
            </div>
             <h4>QR Code Scanner</h4>           
        </div>
      </div>
    </div>
  </div>

   {{-- modal --}}
 <div class="modal fade" id="ModalForDARSBSAScanner" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">        
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <video id="preview_id" class="preview_qr_id"></video>    
                </div>
            </div>
             <h4>DA Issued ID</h4>           
        </div>
      </div>
    </div>
  </div>



  
<script type="text/javascript">
/*for qr */
var scanner = new Instascan.Scanner({ video: document.getElementById('preview'), scanPeriod: 5, mirror: false });
scanner.addListener('scan',function(content){  
    if(content !="" &&content != null && content != undefined )        
    $('#Qrcode').val(content);
    $('#ModalForQRCodScanner').modal('toggle');
    getFarmerData(content);
});  
    
/* for id */
var scanner_id = new Instascan.Scanner({ video: document.getElementById('preview_id'), scanPeriod: 5, mirror: false });
scanner_id.addListener('scan',function(content){  
    if(content !="" &&content != null && content != undefined )        
    $('#da_id').val(content);
    $('#ModalForDARSBSAScanner').modal('toggle');
});  


</script>
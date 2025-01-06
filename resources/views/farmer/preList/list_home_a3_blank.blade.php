<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{$title}}</title>

    <style>

        /* set PDF margins to 0 */
        @page { margin: 10px; }
        /* set PDF margins to 0 */

        table {
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid black;
        }

        td{
            padding: 2px;
        }

        body{
            font-size: 12px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>




</head>
<?php
    //variable declaration
    $itm = 1;
    $max_row = 20;
    $page = 1;

?>

    
    @for($page; $page <= $page_count;$page++)



<body>
    
        
		<table style="width:100%; margin-right: 10px; margin-top: 15px;">
            <thead>
                <tr>
                    <th colspan="21" style="page-break-after: always;">
                        <img src="{{ public_path('images/da_philrice.jpg')}}" 
                            style="width: auto; height: 110px; margin-top:15px;margin-left: 20px; z-index: -50; position: absolute;">

                            <img src="{{ public_path('images/rcef_seed_program.jpg')}}" 
                            style="height: 100px; width: auto;margin-top:25px;margin-left: 140px; position: absolute;">

                        <div style="margin-top:10px; width: 21%; margin-left: 260px;">
                            <left><span style="font-size: 20px;"> <center> Farmer Acknowledgement Receipt  <br>(Seeds) </center> </span></left>
                            <left><span style="font-size: 20px;"></span></left>
                        </div>

                        
                        <div style="margin-top:10px; border: 1px dotted black; width: 20%; margin-left: 260px; margin-bottom:20px;">
                             <span style="font-size: 14px;">* Disclaimer Notice *</span>
                            <hr style="margin-top: 0px;">   
                             <table style="border: 0; margin-left: 10px; margin-top: 0px;" width="100%">
                                 <tr> 
                                    <td  style="border-style: none;"> 
                                     Paalala Itala lamang ang mga magsasaka na nakasama sa listahan na nasa mobile app (Distribution App)
                               </td>
                            
                                </tr>
                            </table>

                        </div>
                        



                        <div style="position: absolute; border: 1px dotted black; top:5px; width: 50%; height: auto; right:190px;">
                            <span style="font-size: 14px;">Important Notes:</span>
                            <hr style="margin-top: 0px;">

                            <table style="border: 0; margin-left: 10px; margin-top: 0px;" width="100%">
                                <tr>   <td style="border-style: none;" width="100%" colspan="2"> * <u>RSBSA No</u>- RSBSA stub on-hand of each individual farmer, and will be presented upon claiming of seeds.  </td>
                                </tr>
                                <tr> 
                                    <td colspan="2" style="border-style: none;"> *<u> Gross harvest</u> - Total number of bags from the thresher/harvester during the previous season (including shares of harvesters, machine/s, owner [if land is leased/rented])</td>
                             

                                </tr>
                                <tr> 
                                    <td width="80%" style="border-style: none;"> *<u> Average wt. (kg) </u>- Average weight per bag in kilograms of the previous season's harvest</td>
                                    <td style="border-style: none;" width="20%"> * <u>FDV</u> - Fertilizer Disbursement Voucher</td>
                                </tr>
                                <tr> 
                                    <td width="80%"  style="border-style: none;"> * <u>Area harvested (ha) </u>- Total area harvested during the previous season</td>

                                    <td style="border-style: none;" width="20%"> * <u>No. of bags</u> - Number of bags received per variety</td>
                                    


                                </tr>
                                
                                <tr> 
                                    <td width="80%" style="border-style: none;" > * <u>Area to be planted (ha) </u>- Intended area for planting for wet season 2022 </td>
                                    <td style="border-style: none;" width="20%" >  * <u>Variety Received </u>- Name of variety received</td>

                                  
                                </tr>

                                <tr> 
                                    <td width="80%"  style="border-style: none;" > * <u>Total Area (ha) </u>- Maximum/Total Physical Rice Area </td>
                                    <td style="border-style: none;" width="20%" >  * <u>Crop Establishment </u> <br> &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp;  [D-Direct Seeding | T-Transplanted]</td>

                                </tr>
                                <tr> 
                                <td width="100%" colspan="2"  style="border-style: none;" > 
                                * <u>Date of sowing </u>[Month / Week]-Format: &nbsp;  &nbsp;<u> 1 - 12 </u> (Jan. - Dec.) / &nbsp;&nbsp; <u>1 - 4</u> (1st - 4th Week)  </td> 
                                </tr>

                                <tr> 
                                    <td width="100%" colspan="2"  style="border-style: none;" >
                                   * <u>Irrigation Status</u> [ I=Irrigated | RU=Rainfed Upland | RL=Rainfed Lowland ] -  
                                    Irrigated (eg. NIS, CIS, SWIS, STW)
                                    <!-- Rainfed (Rainfall only) -->
                                    </td>
                                </tr>
                             
                                
                               
                            </table>
                        </div>

                        <div style="position: absolute; border: 1px dotted black; top:10px; width: 11%; height: 185px; right:10px;">
                            <span style="font-size: 14px;">Data Sharing Consent:</span>
                           <hr style="margin-top: 0px;">   
                            <table style="border: 0; margin-left: 10px; margin-top: 0px;" width="100%">
                                <tr> 
                                   <td  style="border-style: none;"> 
                                       Sumasang-ayon ka ba na ang lahat ng impormasyon na inyong ibabahagi ay maaring gamitin ng PhilRice at ng Department of Agriculture sa iba pang programa nito?  Lagyan ng "<img src="{{ public_path('images/far_check.jpg')}}" style="height: 12px; width: auto;">" kung ikaw ay sumasang-ayon  at "<img src="{{ public_path('images/far_cross.jpg')}}" style="height: 12px; width: auto;">" kung hindi.
                                   </td>
                            

                               </tr>
                           </table>

                       </div>

                       

                          <div style="position: fixed; text-align: left; margin-right: 10px;  top:0px;right:0px;"> Privacy Notice: In accordance with Republic Act No. 10173, otherwise known as Data Privacy Act of 2012, PhilRice ensures that all data collected are treated with strict confidentiality</div>
                        <div style="position: fixed; text-align: right; margin-right: 10px;  top:0px;right:0px;"> FAR V5.B</div>
                  


                        <div style="margin-top: 10px;margin-left: 10px; margin-bottom:10px;">
                            <span>Year/Season: __<u>Dry Season 2023</u>___</span><br>
                            <span>Drop-off Point (City/Municipality, Province) ____________________</span><br>
                            <span>RSBSA Code: Region:______, Province:______, Municipality:______, Barangay:______
                            
                            </span>
                        </div>
                        

                        
                    </th>
                </tr>
				
				
                <tr>
                    <th rowspan="2" style=""></th>
                    <th style="" colspan="3"><center>FARMER NAME</center></th> 
                    <th style="width: 120px;" rowspan="2" align="center">RSBSA No.</th>
                 
                    
                    {{-- <th rowspan="2" style="width: 25px;"><center>Sex<br>(M/F)</center></th> --}}
                    
                   
                    <th style="font-size: 10px;" style="width:40px;"><center> Data Sharing Consent</center></th>

                    <th  style="width:90px;" align="center">Active Mobile Number </th>
                    <th style="" colspan="3"><center>2022 WS YIELD</center></th> 
                    <th style="width:80px;" rowspan="2"><center>Irrigation Status <br>  [ I | RU | RL ]</center></th> 
                    <th style="width: 50px;" rowspan="2" align="center"> Total Area</th>
                    <th style="width: 50px;" rowspan="2" align="center"> Number of bags</th>
                    <th style="width: 50px;" rowspan="2" align="center"> Variety Received</th>
                    <th rowspan="2" style="width: 25px;"><center>FDV<br>(Y/N)</center></th>
                    
                    <th  align="center" style="width: 80px;">Area to be planted</th>
                    
                    <th  style="width:60px; font-size: 10px;" align="center" rowspan="2">Date of Sowing <br> [Month/Week] </th>
                    <th  style="width:30px; font-size: 10px;" align="center" rowspan="2">Crop Est <br> [D/T] </th>

                    <th style="font-size: 10px; width: 100px;"><center>Name of Authorized Representative </center></th>
                    <th rowspan="2" style=" font-size: 10px; width: 80px;" align="center">Signature of Claimant</th>
                 
                    <th rowspan="2" align="center" style="width: 80px;">Date Received <i style="font-weight: 1px; font-size: 10px;" > <br> (mm/dd/yr) </i></th>
				</tr>
                <tr>
                    <th colspan="3" align="center" style="font-weight: 1px; font-style: italic; font-size: 10px; height: 22px; ">(Last Name, First Name, Middle Name)</th>
                    <th style="width: 80px;" ><center>Yes:" <img src="{{ public_path('images/far_check.jpg')}}" style="height: 12px; width: auto;">" <br>  No:"<img src="{{ public_path('images/far_cross.jpg')}}" style="height: 12px; width: auto;">"</center></th>
                    <th align="center" style="font-size:9px; font-style:italic;"> (11 Characters) <br> 09XXXXXXXXX</th>
                    <th  style="width:50px; font-size: 10px;" align="center">Area harvested <br> (ha)</th>
                    <th  style="width:50px; font-size: 10px;" align="center">Gross harvest <br> (bag)</th>
                    <th  style="width:50px; font-size: 10px;" align="center">Ave. wt. per bag <br> (kg)</th>
                   
                 



                    <th align="center" style="font-size: 10px; font-style: italic;">(must be equal or less than RSBSA Area)</th>
                    <th align="center" style="font-weight: 1px; font-style: italic; font-size: 10px;">(Last Name, First Name, Middle Initial)</th>
                </tr>
                
				
				
				
				
            </thead>
            <?php 

                        for($row_identifier = 1; $row_identifier <= $max_row; $row_identifier++){
                            ?>

                                    <tr>
                                        <td style="width: 10px; height: 29.5px;">{{$itm}} </td>
                                            <td colspan="3" style="width:200px;  font-size: 10px;"> </td>

                                            <td><center> </center></td>
                                            <!--AGE -->
                                            <td align="center" style="font-size: 11px; width: 30px;"> </td>
                                            <td> </td>
                                            <td></td>
                                            <td></td>
                                            <!-- CONTACT -->
                                            <td></td>
                                            <!--YIELD-->         
                                            <td></td>

                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <!--SBSA Registered Area-->
                                            <td style="width: 60px;"></td>
                                            <td style="width: 50px;"></td>
                                            <td style="width: 80px;"> </td>
                                            <td></td>



                                            <td style="width: 20px; font-size: 11px;"> </td>
                                            <!-- REPRESENTATIVE -->
                                            <td style="width: 100px;"> </td>


                                            <td style="width: 80px;"> </td>

                                    </tr>


            <?php     $itm++;  }
            ?>




                                  
                      

                 
          
			
								
						
            <!--footer-->
            <tr>
                <th colspan="21">


                    <div style="margin-top:0px;margin-left: 10px;">
                        <span>Issued By: </span>
                        <div style="margin-left: 50px;">
                            <span>____________________________________________</span><br>
                            <span style="margin-left: 10px; font-size: 10px;">Name, signature and position of authorized LGU Representative</span><br><br>
                           
                        </div>
                    </div>

                    <div style="margin-left: 900px;margin-top:-115px;">
                        <span>Noted By: </span>
                        <div style="margin-left: 50px;">
                            <span>____________________________________________</span><br>
                            <span style="margin-left:-10px; font-size: 10px;">Name and signature of RCEF Seed Regional/Provincial Coordinator</span><br><br><br><br>
                    
                        </div>
                    </div>
                    <div style="position: fixed; text-align: right; margin-right: 8px;  bottom:120px;right:5px;">
                        <img src="{{ public_path('images/iso.jpg')}}" 
                            style="width: auto; height: 75px;">
                    </div>
                        <div style="position: fixed; text-align: right; margin-right: 10px;  bottom:15px;right:0px;"> PhilRice RCEF FAR V5.B  Rev 01 Effectivity Date: 15 March 2022</div>

                    
                </th>
            </tr>


            </table>
        
        <div class="page-break"></div>

        @endfor
    

   

</body>
</html>
    
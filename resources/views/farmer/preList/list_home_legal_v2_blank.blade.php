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
    $max_row = 15;
    $page = 1;

?>



    @for($page; $page <= $page_count;$page++)



<body>
    
        
		<table style="width:100%; margin-right: 10px; margin-top: 15px;">
            <thead>
                <tr>
                    <div style="position:fixed; left:48%; top:30px;; border-style:dashed; width:430px; height:90px; z-index: -50; padding-left:10; padding-right:10; padding-bottom:10; padding-top:0;  ">
                        <p style="">
                            <b> Important Notice</b> <br>
                           
                            This Farmer Acknowledgement Receipt is intended for rice farmers who cannot be found in the RSMS - farmer beneciary list as of June 30, 2024 but are verified through the RSBSA Verifier App.

                        </p>
                    </div>


                    <th colspan="21" style="page-break-after: always;">
                        
                        
                        
                        <img src="{{ public_path('images/da_philrice_rcef.jpg')}}" 
                        style="width: auto; height: 110px; margin-top:0px;margin-left: 1%; z-index: -50; position: absolute; top:10px;">
                       
                        <div style="margin-top:10px; margin-left: 13%;">
                            <left><span style="font-size: 20px;"> <br>Farmer  Acknowledgement  Receipt </span></left>
                            <left><span style="font-size: 20px;"></span></left>
                        </div>

                        

                        <img src="{{ public_path('images/wqa.jpg')}}" 
                        style="width: auto; height: 120px; margin-top:15px;margin-left: 88%; z-index: -50; position: absolute; top:0px;">

                    
                    {{-- <div style="margin-top:10px; border: 1px dotted black; width: 20%; margin-left: 260px;">
                         <span style="font-size: 14px;">*Importance of Active Mobile Number *</span>
                        <hr style="margin-top: 0px;">   
                         <table style="border: 0; margin-left: 10px; margin-top: 0px;" width="100%">
                             <tr> 
                                <td  style="border-style: none;"> 
                                 Farmers with active & updated mobile numbers will have a chance to participate in the "Binhi e-Padala" Program.
                           </td>
                        
                            </tr>
                        </table>

                    </div> --}}



                   
                    <div style="position: fixed; text-align: left; margin-right: 10px;  top:0px;right:0px;">Privacy Notice: All collected information will be handled by DA-PhilRice in accordance with Republic Act No. 10173 (Data Privacy Act of 2012).</div>
                   
                    <div style="position: fixed; text-align: right; margin-right: 10px;  top:0px;right:0px;"> FAR V7.1B</div>
                   
                  


                    <div style="margin-top: 15px;margin-left: 13%; margin-bottom:25px;">
                        <span>Year/Season: _______________</span><br>
                        <span>Drop-off Point (City/Municipality, Province) _______________________________</span><br>
                        <span>RSBSA Code: Region:____, Province:____, Municipality:____</u>
                        _, Barangay:________
                        
                        </span>
                    </div>
                        
                    </th>
                </tr>
				
				
                <tr>
                    <th rowspan="2" style="">No.</th>
                    <th style="" colspan="3"><center>FARMER NAME</center></th> 
                    <th style="width: 110px;" rowspan="2" align="center">RSBSA No. <br> (FFRS System Generated) </th>

                    <th  style="width: 20px; font-size:10px;" rowspan="2" align="center">Registered <br> Municipal Rice <br> Area </th>
                  
                    
                    <th  align="center" style="width: 20px; font-size:10px;" rowspan="2">Area to be planted (ha)</th>
                    <th style="width: 30px; font-size:10px;" rowspan="2" align="center"> Number of bags <br> (20kg/bag)</th>
                    <th style="width: 70px;" rowspan="2" align="center">Rice Variety Received</th>
                  
                    {{-- <th rowspan="2" style="width: 25px;"><center>Sex<br>(M/F)</center></th> --}}
                    <th  style="width:20px; font-size: 10px;" align="center" rowspan="2">Crop Estab. <br> [D/T] </th>

                

                    <th  style="width:40px; font-size: 10px;" align="center" rowspan="2">Expected Sowing Date <br> [Month/Week] </th>


                    <th style="font-size: 10px;" style="width:20px;" rowspan="2"><center> Data Sharing Consent <br> (<img src="{{ public_path('images/far_check.jpg')}}" style="height: 12px; width: auto;">/ X)</center></th>
                    <th style="" colspan="5"><center>2024 WS YIELD <br> Major Seed and Variety Planted</center></th> 



                    <th style="width:40px;" rowspan="2"><center> No. of <br> KP Kits <br> Received</center></th> 

                    <th style="font-size: 10px; width: 90px;"><center>Name of Authorized Representative </center></th>
                    <th rowspan="2" align="center" style="width: 20px;">Date Received <br> <i style="font-weight: 1px; font-size: 10px;" > (mm/dd/yr) </i></th>
                    <th rowspan="2" style=" font-size: 10px; width: 60px;" align="center">Signature of Claimant</th>
                 
                  
				</tr>
                <tr>
                    <th colspan="3" align="center" style="font-weight: 1px; font-style: italic; font-size: 9px; height: 22px; ">(Last Name, First Name, Middle Name)</th>
                    {{-- <th align="center" style="font-size: 10px; font-style: italic;">(must be equal or less than Reg. Area)</th> --}}
                    <th  style="width:30px; font-size: 10px;" align="center">Seed Class <br> (H/I/F) </th>
                    <th  style="width:60px; font-size: 10px;" align="center">Planted Variety </th>
                    <th  style="width:20px; font-size: 9px;" align="center">Area harvested <br> (ha)</th>
                    <th  style="width:20px; font-size: 9px;" align="center">Total harvest <br> (bag)</th>
                    <th  style="width:40px; font-size: 9px;" align="center">Harvest <br> weight <br> per bag <br> (kg)</th>
                    
                    
                    
                    
                



                    <th align="center" style="font-weight: 1px; font-style: italic; font-size: 10px;">(Last Name, First Name, Middle Initial)</th>
                </tr>

				
				
				
				
            </thead>
            <?php 

                        for($row_identifier = 1; $row_identifier <= $max_row; $row_identifier++){
                            ?>

                                    <tr>
                                        <td style="width: 10px; height: 25px;">
                                            {{$itm}}
                                          
                                        </td>
                                            <td colspan="3" style="width:200px;"> </td>

                                            <td style="width:120px;"> </td>
                                            <!--AGE -->
                                            <td style="font-size: 11px; width: 60px;"> </td>
                                            <td style="width:40px;"> </td>
                                            <td></td>
                                            
                                            <!-- CONTACT -->
                                            <td></td>
                                            <!--YIELD-->         
                                            <td></td>
                                            <td></td>

                                            <td></td>
                                            <td></td>
                                            <!--SBSA Registered Area-->
                                            <td></td>
                                            <td > </td>
                                            <td > </td>
                                            <td></td>

                                            <td></td>
                                            

                                            <td style="width: 150px; font-size: 11px;"> </td>
                                            <!-- REPRESENTATIVE -->
                                            <td style="width:80px;"> </td>


                                            <td style="width:80px;"> </td>

                                    </tr>


            <?php     $itm++;  }
            ?>




                                  
                      

                 
          
			
								
						
            <!--footer-->
            <tr>
                <th colspan="21">


                    <div style="margin-top:10px;margin-left: 10px; width:500px; height:70px; margin-bottom:10px;">
                        <span>Issued By: </span>
                      
                    </div>

                    <div style="position: fixed; bottom:90px; margin-top:10px;margin-left: 30px; width:500px; margin-bottom:10px;">
                        <div style="margin-left: 50px;">
                            <span>_______________________________________________</span><br>
                            <span style="margin-left: 5%; font-size: 10px;">Name and Signature of Authorized LGU Representative </span><br><br>
                            <span>_______________________________________________</span><br>
                            <span style="margin-left: 20%; font-size: 10px;">Position/Designation </span>
                        </div>
                    </div>



                    <div style="position: fixed; right:-80; bottom:70; width:500px;">
                        <span>Noted By: </span>
                    
                    </div>


                    <div style="position: fixed; right:-90; bottom:70; width:500px;">
                        <div style="margin-left: 50px;">
                            <span>_______________________________________________</span><br>
                            <span style="margin-left: 20%; font-size: 10px;">Name and Signature</span><br><br>
                        
                            <span style="margin-left: 30px; font-size: 12px;">RCEF Seed Regional/Provincial Coordinator </span>
                        </div>
                    </div>




                    <div style="position: fixed; left:30%; bottom:75; width:850px;">
                            <span>Variety 1: ______________ <small> Total Farmers: __________  Total Area: __________  Total Bags: __________ </small> </span><br>
                            <span>Variety 2: ______________ <small> Total Farmers: __________  Total Area: __________  Total Bags: __________ </small> </span><br>
                            <span>Variety 3: ______________ <small> Total Farmers: __________  Total Area: __________  Total Bags: __________ </small> </span><br>
                            <span>Variety 4: ______________ <small> Total Farmers: __________  Total Area: __________  Total Bags: __________ </small> </span><br>
                            <span>Variety 5: ______________ <small> Total Farmers: __________  Total Area: __________  Total Bags: __________ </small> </span><br>
                    
                    </div>


                    <div style="position: fixed; text-align: right; margin-right: 8px;  bottom:120px;right:5px;">
                        {{-- <img src="{{ public_path('images/iso.jpg')}}" 
                            style="width: auto; height: 75px;"> --}}
                    </div>
                        <div style="position: fixed; text-align: right; margin-right: 10px;  bottom:15px;right:0px;"> PhilRice RCEF FAR V7.1B  Rev 02 Effectivity Date: 13 Sep 2024</div>

                    
                </th>
            </tr>


            </table>
        
        <div class="page-break"></div>

        @endfor
    

   

</body>
</html>
    
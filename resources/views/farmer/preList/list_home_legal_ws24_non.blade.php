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
<body>
     <?php 
     $w_data_col = 14;
     $wo_data_col = 17;
    $version_with_data = "7.2";
    $version_no_data = "7.2B";
    
    $td_height = "25px";
     
     $row_param = 15;
     $page_count = ceil(count($list) /  $row_param)?>
	  
    <?php $totalFarmer = count($list); ?>
    <?php $array_index = 1?>
    <?php $cnt = 0;
		  $itm = 1;

      
	?>
    
    <?php
        $brgy = $list[0]["brgy"];

        foreach ($list as $key => $value) {
           if($list[$key]["brgy"]!=$brgy){
            $brgy = $list[$key]["brgy"];
            $page_count++;
           }
        }
         $brgy = $list[0]["brgy"];
	
		 $addBlankPage =1;
        
       // $page_count = 1;
    ?>

<div class="row">
   

 
    

        <table style="border-style: none;">
            <thead>
                <tr>
                </td>
                <img src="{{ public_path('images/da_philrice_rcef.jpg')}}" 
                style="width: auto; height: 110px; margin-top:0px;margin-left: 25%; z-index: -50; position: absolute; top:10px;">
               
                <div style="margin-top:10px;">
                    <center><span style="font-size: 20px; font-weight:bold;"> <br>Farmer  Acknowledgement  Receipt <br> <small> (For farmers with small landholding area <br> less than 0.1 ha) </small> <br> Description Guide</span></center>
                </div>
                <img src="{{ public_path('images/wqa.jpg')}}" 
                style="width: auto; height: 100px; margin-top:15px;margin-left: 70%; z-index: -50; position: absolute; top:10px;">
                    <td style="border-style: none;">
                </tr>
         
            </thead>
        </table>
    </div>
       

        <div class="row" style="margin-top: 50px; margin-left:10%; width:80%; ">

    <table style="font-size: 20px;border-style: solid;">
        {{-- <thead>
            <tr>
                <th>Labels</th>
                <th>Description</th>

            </tr>
        </thead> --}}
        <tbody>
            <tr>   <td style="vertical-align: top;">  <strong> RSBSA No.
                (FFRS System
                Generated)</strong> </td>
                <td style="vertical-align:top;">Unique code assigned to each farmer contained in the RSBSA stub/ RCEF ID/ DA Interventions Monitoring Card, which will be presented upon seed claim</td>
            </tr>

            <tr> 
                <td style="vertical-align: top;">
                    <strong> Registered Municipal Rice Area</strong>
                </td>
                <td style="vertical-align:top;">
                    Maximum/Total Physical Rice Area located in the city/municipality
                </td>
            </tr>

            <tr> 
                <td style="vertical-align: top;">
                    <strong>Rice Variety Received</strong>
                </td>
                <td style="vertical-align:top;" >
                    Name of rice variety received
                </td>
            </tr>

          
            <tr> 
                <td style="vertical-align: top;">
                    <strong> Crop Estab.</strong>
                </td>
                <td style="vertical-align:top;">
                    Crop Establishment that will be employed for the <strong> 2025 Wet Season</strong> [<strong>D</strong>-Direct Seeding | <strong>T</strong> -Transplanted]
                </td>
            </tr>

         

            <tr> 
                <td style="vertical-align: top;">
                    <strong> Expected Sowing Date</strong>
                </td>
                <td style="vertical-align:top;">
                    [Month / Week]-Format: &nbsp;  &nbsp;<strong>1 - 12 </strong> (Jan. - Dec.) / &nbsp; <strong>1 - 4</strong> (1st - 4th week) (e.g. 9/3 = Sept. 3rd Week)
                </td>
            </tr>
            <tr> 
                <td style="vertical-align: top;">
                    <strong> Data Sharing Consent</strong>
                </td>
                <td style="vertical-align:top;" >
                    Sumasang-ayon ka ba na ang lahat ng impormasyon na inyong ibabahagi ay maaring gamitin ng PhilRice at ng Department of Agriculture sa iba pang programa nito?  Lagyan ng "<img src="{{ public_path('images/far_check.jpg')}}" style="height: 12px; width: auto;">" kung ikaw ay sumasang-ayon  at "<img src="{{ public_path('images/far_cross.jpg')}}" style="height: 12px; width: auto;">" kung hindi.
                </td>
            </tr>

          
            <tr> 
                <td style="vertical-align: top;">
                    <strong>No. of KP Kits Received</strong>
                </td>
                <td style="vertical-align:top;">
                    Knowledge products received during distribution
                </td>
            </tr>
     
            <tr> 
                <td style="vertical-align: top;">
                    <strong>RSBSA No. of Group Representative</strong>
                </td>
                <td style="vertical-align:top;">
                    Farmers who agreed to form a group and aggregate their farm sizes must assign a group representative who will sign the FAR on behalf of all group members. The representative's RSBSA No. must be written across all the rows corresponding to each group member under the column <i>"RSBSA No. of the Group Representative"</i>.
                </td>
            </tr>
            <tr> 
                <td style="vertical-align: top;">
                    <strong>Date Received</strong>
                </td>
                <td  style="vertical-align:top;">
                    Date of receipt of seed allocation in mm/dd/yy format (e.g., 09/12/25)
                </td>
            </tr>

            <tr> 
                <td style="vertical-align: top;">
                    <strong>Signature of Claimant</strong>
                </td>
                <td  style="vertical-align:top;">
                    Signature of the farmer/authorized representative who received/claimed the seeds
                </td>
            </tr>
     
           
        </tbody>

    </table>
 </div>


</div>


<div style="position: absolute; bottom:40px; left:135px; border-style:dashed; width:78%; font-size:20px; padding:5px;">
    <strong>NOTE: </strong>Documentary requirements (e.g., authorization letters, C/MAO certification, etc.) presented during seed distribution shall be collected by DA-PhilRice alongside the accomplished FAR upon the completion of seed distribution.
</div>


<div class="page-break"></div> 



    @for ($i = 1; $i <= $page_count; $i++)
		  <?php  
			$cntIdentifier = 0;
			$breaker =0;
			
			 if(empty($list[$cnt])){
             
                    continue;
               
                $brgy = "________";
                $itm = 1;
				$wData = 0;
				
				if($addBlankPage == 1){
				$blankPage = $page_count - $i; //Get remaining pages
					$page_count -= $blankPage;
					$page_count +=2;
					$addBlankPage = 0;
					//echo $page_count . "<br>".$i;	
				}			
            }else{
				$wData = 1;
			}

			?>


		<table style="width:100%; margin-right: 10px; margin-top: 12px; border-style:ridge">
            <thead>
                <tr>
                    <div style="position:fixed; left:60%; top:30px;; border-style:dashed; width:320px; height:100px; z-index: -50; padding-left:10; padding-right:10; padding-bottom:10; padding-top:0;  ">
                        <p style="">
                            <b> Important Notice</b> <br>
                            Pursuant to DA Memorandum No. 30 s. 2023, farmers with rice areas of less than 1,000 m&sup2; (0.1 ha) are <strong> NOT </strong> eligible as is for inbred seed support. However, we encourage these farmers to form groups by<strong> aggregating/combining their farm sizes </strong>to meet the minimum area requirement and be eligible for seed support equivalent to 1 bag (20 kg).
                        </p>
                    </div>

                    @if($wData == 1)
                        <th colspan="{{$w_data_col}}" style="page-break-after: always;">
                    @else
                        <th colspan="{{$wo_data_col}}" style="page-break-after: always;">
                    @endif
                        
                            <img src="{{ public_path('images/da_philrice_rcef.jpg')}}" 
                            style="width: auto; height: 110px; margin-top:0px;margin-left: 1%; z-index: -50; position: absolute; top:10px;">
                           <br>
                            <div style="margin-top:0px; margin-left: 13%;">
                                <left><span style="font-size: 20px;"> Farmer  Acknowledgement  Receipt <br> <small> (For farmers with small landholding area less than 0.1 ha)  </small> </span></left> <br>
                                
                            </div>
                            {{-- <div style="margin-top:10px; margin-left: 11%; position:fixed;">
                            <left><span style="font-size: 12px;">(For Individuals with Area less than 0.1 ha) </span></left>
                            </div> --}}

                            <img src="{{ public_path('images/wqa.jpg')}}" 
                            style="width: auto; height: 100px; margin-top:15px;margin-left: 89%; z-index: -50; position: absolute; top:10px;">

                     
                        
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
                        @if($wData == 1)
                        <div style="position: fixed; text-align: right; margin-right: 10px;  top:0px;right:0px;"> FAR V{{$version_with_data}}</div>
                        @else
                        <div style="position: fixed; text-align: right; margin-right: 10px;  top:0px;right:0px;"> FAR V{{$version_no_data}}</div>
                        @endif


                        @if($mark == "empty")

                        <div style="margin-top: 15px;margin-left: 13%; margin-bottom:25px;">
                            <span>Year/Season: _______________</span><br>
                            <span>Drop-off Point (City/Municipality, Province) _______________________________</span><br>
                            <span>RSBSA Code: Region:____, Province:____, Municipality:____</u>
							_, Barangay:_____
							
							</span>
                        </div>
                        @else
                        <div style="margin-top: 15px;margin-left: 13%; margin-bottom:25px;">
                            <span>Year/Season: __<u>2025 Wet Season</u>__</span><br>
                            <span>Drop-off Point (City/Municipality, Province) __<u>{{strtoupper($municipality)}}, {{strtoupper($province)}}</u>__</span><br>
                            <span>RSBSA Code: Region:_<u>{{$region_code}}</u>_, Province:_<u>{{$province_code}}</u>_, Municipality:_<u>{{$municipality_code}}</u>
							_, Barangay:_<u>{{$brgy}}</u>_
							
							</span>
                        </div> 
                        @endif
                        
                        <br>
                        
                    </th>

                </tr>
				
				@if($wData == 1)
				<tr>
                    <th rowspan="2" style="">No.</th>
                    <th style="" colspan="3"><center>FARMER NAME</center></th> 
                    <th style="width: 110px;" rowspan="2" align="center">RSBSA No. <br> (FFRS System Generated) </th>

                    <th  style="width: 20px; font-size:10px;" rowspan="2" align="center">Registered <br> Municipal Rice <br> Area </th>
                    {{-- <th  style="width: 20px; font-size:10px;" rowspan="2" align="center">Total <br>Parcel <br> Count</th> --}}
                    
                    {{-- <th  align="center" style="width: 20px; font-size:10px;" rowspan="2">Area to be planted (ha)</th> --}}
                    {{-- <th style="width: 30px; font-size:10px;" rowspan="2" align="center"> Number of bags <br> (20kg/bag)</th> --}}
                    <th style="width: 70px;" rowspan="2" align="center">Rice Variety Received</th>
                  
                    {{-- <th rowspan="2" style="width: 25px;"><center>Sex<br>(M/F)</center></th> --}}
                    <th  style="width:20px; font-size: 10px;" align="center" rowspan="2">Crop Estab. <br> [D/T] </th>

                

                    <th  style="width:40px; font-size: 10px;" align="center" rowspan="2">Expected Sowing Date <br> [Month/Week] </th>


                    <th style="font-size: 10px;" style="width:20px;" rowspan="2"><center> Data Sharing Consent <br> (<img src="{{ public_path('images/far_check.jpg')}}" style="height: 12px; width: auto;">/ X)</center></th>
                    {{-- <th style="" colspan="5"><center>2023 WS YIELD <br> Major Seed and Variety Planted</center></th>  --}}



                    <th style="width:40px;" rowspan="2"><center> No. of <br> KP Kits <br> Received</center></th> 

                    <th style="font-size: 12px; width: 90px;" rowspan="2"><center>RSBSA No. of Group Representative </center></th>
                    <th rowspan="2" align="center" style="width: 20px;">Date Received <br> <i style="font-weight: 1px; font-size: 10px;" > (mm/dd/yr) </i></th>
                    <th rowspan="2" style=" font-size: 10px; width: 60px;" align="center">Signature of Claimant</th>
                 
                  
				</tr>
                <tr>
                    <th colspan="3" align="center" style="font-weight: 1px; font-style: italic; font-size: 12px; height: 22px; ">(Last Name, First Name, Middle Name)</th>
                    {{-- <th align="center" style="font-size: 10px; font-style: italic;">(must be equal or less than Reg. Area)</th> --}}
                    {{-- <th  style="width:30px; font-size: 10px;" align="center">Seed Class <br> (H/I/F) </th>
                    <th  style="width:60px; font-size: 10px;" align="center">Planted Variety </th>
                    <th  style="width:20px; font-size: 9px;" align="center">Area harvested <br> (ha)</th>
                    <th  style="width:20px; font-size: 9px;" align="center">Total harvest <br> (bag)</th>
                    <th  style="width:40px; font-size: 9px;" align="center">Harvest <br> weight <br> per bag <br> (kg)</th> --}}
                    
                    
                    
                    
                



                    {{-- <th align="center" style="font-weight: 1px; font-style: italic; font-size: 10px;">(Last Name, First Name, Middle Initial)</th> --}}
                </tr>



                {{-- NO DATA --}}
				@else
				

                

				@endif
				
				
				
				
            </thead>
       
			  @for ($x = $cnt; $x < $totalFarmer; $x++)
                @if(!empty($list[$cnt]))
					 <?php
                    if($brgy!=$list[$cnt]["brgy"]){
                           // $i++;
                            $brgy = $list[$cnt]["brgy"];
							if($cntIdentifier <  $row_param){
                                $r =  $row_param - $cntIdentifier ;
                                for($e=0;$e<$r;$e++){
                                    ?>
                                    <!-- EMPTY ROW -->
                                    <tr>
									    <td style="width: 10px; height: {{$td_height}};">{{$itm}} </td>
                                            <td colspan="3" style="width:200px;  font-size: 10px;"> </td>

                                            <td><center></center></td>
                                            <!--AGE -->
                                            <td align="center" style="font-size: 11px; width: 50px;"></td>
                                         
                                            <!-- CONTACT -->
                                            <td></td>
                                            <!--YIELD-->         
                                            <td></td>
                                            <td></td>

                                            <td></td>
                                           

                                            <td></td>
                                            

                                            <td style="width: 20px; font-size: 11px;"> </td>
                                            <!-- REPRESENTATIVE -->
                                            <td style="width: 40px;"> </td>


                                            <td style="width: 30px;"> </td>
                               
									</tr>


                                    <?php
									$itm++;
                                }
                            }
							$itm = 1;
							$breaker =1;
                            $cntIdentifier = 0;
                            break;
                        }

                    if($cntIdentifier ==  $row_param){
                       $breaker =1;
                        break;
                    }
                    ?>
				
				<tr>
					<td style="width: 10px; height: {{$td_height}};">
                       
                        {{$itm}}
                         </td>
					<td colspan="3" style="width:260px; font-size: 11px;">
                    
                    @if($mark == "mark")
                        @if($list[$cnt]["is_ebinhi"] == "0")
                        <b> @if(isset($tag_val))
                            *{{strtoupper($list[$cnt]["lastName"])}}, {{strtoupper($list[$cnt]["firstName"])}} {{strtoupper($list[$cnt]["midName"])}}
                            @else
                            {{strtoupper($list[$cnt]["lastName"])}}, {{strtoupper($list[$cnt]["firstName"])}} {{strtoupper($list[$cnt]["midName"])}}
                            @endif
                        </b>
                        @else
                        <b> @if(isset($tag_val))
                            *{{strtoupper($list[$cnt]["lastName"])}}, {{strtoupper($list[$cnt]["firstName"])}} {{strtoupper($list[$cnt]["midName"])}}
                            @else
                            {{strtoupper($list[$cnt]["lastName"])}}, {{strtoupper($list[$cnt]["firstName"])}} {{strtoupper($list[$cnt]["midName"])}}
                            @endif *</b>
                        @endif 
                    @elseif($mark == "empty")
                        



                    @else
                    <b>  @if(isset($tag_val))
                        *{{strtoupper($list[$cnt]["lastName"])}}, {{strtoupper($list[$cnt]["firstName"])}} {{strtoupper($list[$cnt]["midName"])}}
                        @else
                        {{strtoupper($list[$cnt]["lastName"])}}, {{strtoupper($list[$cnt]["firstName"])}} {{strtoupper($list[$cnt]["midName"])}}
                        @endif </b>
                    @endif
                    
                    
                    
                    </td>

                    @if($mark == "empty")
                    <td></td>
                    <td></td>
                    @else
					<td style="width: 60px; font-size: 11px;" align="center">{{$list[$cnt]["rsbsa_control_no"]}}</td>
                  
                  

               
                    <td align="center">
                        
                        {{ number_format($list[$cnt]["final_area"], 4)}}
                    
                    </td>
             

                    @endif
                    <!-- CONTACT -->

              


            

                    

                    {{-- VARIETY --}}
                    <td></td> 

                    
                    @if(isset($list[$cnt]["is_prereg"]))
                        @if($list[$cnt]["crop_establishment"] == "transplanting")
                        <td align="center">T</td>

                        @else
                        <td align="center">D</td>

                        @endif
                    @else
                    <td style="width:40px;"> </td>
                    @endif

                    

                    @if(isset($list[$cnt]["is_prereg"]))
                        @if($list[$cnt]["ecosystem"] == "irrigated_cis")
                        <td align="center">CIS</td>
                        @elseif($list[$cnt]["ecosystem"] == "irrigated_nis_nia")
                        <td align="center">NIS</td>
                        @elseif($list[$cnt]["ecosystem"] == "irrigated_stw")
                        <td align="center">STW</td>
                        @elseif($list[$cnt]["ecosystem"] == "irrigated_swis")
                        <td align="center">SWIS</td>
                        @elseif($list[$cnt]["ecosystem"] == "rainfed_low")
                        <td align="center">RL</td>
                        @elseif($list[$cnt]["ecosystem"] == "rainfed_up")
                        <td align="center">RU</td>
                        @else
                        <td style="width: 40px;" align="center"></td>
                        @endif
                    @else
                    <td style="width: 40px;" align="center"></td>
                    @endif

                 
                  

                    @if(isset($list[$cnt]["is_prereg"]))
                        @if($list[$cnt]["sowing_week"] == "1st Week" )
                        @php $week = "01"; @endphp
                        @elseif($list[$cnt]["sowing_week"] == "2nd Week")
                        @php $week = "02"; @endphp
                        @elseif($list[$cnt]["sowing_week"] == "3rd Week")
                        @php $week = "03"; @endphp
                        @else
                        @php $week = "04"; @endphp
                        @endif

                        <td align="center">{{date("m",strtotime($list[$cnt]["sowing_month"]))}}/{{$week}}   </td>
                    @else
                        <td style="width:50px;" align="center"> </td>
                    @endif




					<td style="width:50px;"> </td>

                
           

                   
                    


                    <td style="width: 100px;"> </td>
                  
                    <td style="width: 20px; font-size: 11px;"> </td>
                    <td > </td>
				</tr>
					  {{ $cntIdentifier++}}
						  {{$itm++}}
                @else
                <tr>
                    <td style="width: 10px; height: {{$td_height}};">{{$itm}} </td>
                    <td colspan="3" style="width:230px; font-size: 13px;"> </td>
                    {{-- <td style="width: 60px; font-size: 11px;"> </td> SEX --}}
                    <td style="width: 60px; font-size: 11px;"> </td>
                    <td><center> </center></td>
                    <!-- Y/N -->
                
                    <td></td>
                    
                    <!-- CONTACT -->
                    <td></td>
                    <!--YIELD-->         
                 
                    <td></td>
                    <td></td>
                   
                    <td></td>
                      
                    <td style="width: 30px;"></td>    
                  
                    <td style="width: 40px;"></td>     
                    <!-- REPRESENTATIVE -->
                    <td style="width: 20px;"></td>
                  
                    <td></td>
                    <td style="width: 10px;"></td>
                </tr>
                @endif
                   
                {{$cnt++}}
            @endfor
					  <?php 
				 if($breaker != 1){
					 if($cntIdentifier <  $row_param){
								 $r =  $row_param - $cntIdentifier ;
								 for($e=0;$e<$r;$e++){
									 ?>
									 <!-- EMPTY ROW -->
                                      @if($wData == 1)
                                         <tr>
                                            <td style="width: 10px; height: {{$td_height}};">{{$itm}} </td>
                                            <td colspan="3" style=""> </td>
                                            {{-- <td style="width: 60px; font-size: 11px;"> </td> SEX  --}}
                                            
                                            <td><center> </center></td>
                                            <!-- Y/N -->
                                    
                                            <td></td>
                                            
                                            <!-- CONTACT -->
                                            <td></td>
                                            <!--YIELD-->         
                                       
                                            <td></td>
                                            
                                            <td></td>
                                      
                                           
                                            <td></td>
                                            <td ></td>
                                            <!-- REPRESENTATIVE -->
                                            <td ></td>
                                          
                                            <td></td>
                                            <td></td>
                                         </tr>
                                      @else
                                         <tr>
                                            <td style="width: 10px; height: {{$td_height}};">{{$itm}} </td>
                                            <td colspan="3" style="width:200px;  font-size: 10px;"> </td>
                                            {{-- <td style="width: 60px; font-size: 11px;"> </td> SEX  --}}
                                            <td><center> </center></td>
                                            <!--AGE -->
                                            <td align="center" style="font-size: 11px; width: 30px;"> </td>
                                            <td> </td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <!-- CONTACT -->
                                            <td></td>
                                            <!--YIELD-->         
                                            <td></td>
                                    
                                            <td></td>
                                            <!--SBSA Registered Area-->
                                            <td style="width: 60px;"></td>
                                            <td style="width: 50px;"></td>
                                          
                                            <td style="width: 20px; font-size: 11px;"> </td>
                                            <!-- REPRESENTATIVE -->
                                            <td style="width: 20px;"> </td>
                                            <td></td>
                                            <td style="width: 30px;"> </td>
                                        </tr>
                                      @endif

								
									 <?php
								$itm++;
								 }
							 }
				 }
					?>
            <!--footer-->
            <tr>
                    @if($wData == 1)
                        <th colspan="{{$w_data_col}}">
                    @else
                         <th colspan="{{$wo_data_col}}">
                    @endif
                   
                    <div style="margin-top:10px;margin-left: 10px; width:500px; height:75px; margin-bottom:10px;">
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

                    <div style="position: fixed; left:30%; bottom:70; width:540px; font-size:11px;">
                        "RSBSA No. of the Group Representative" : <br>
                        <span style="font-weight: normal;">
                            Farmers who agreed to form a group and aggregate their farm sizes must assign a group representative who will sign the FAR on behalf of all group members. The representative's RSBSA No. must be written across all the rows corresponding to each group member under the column <i>"RSBSA No. of the Group Representative"</i>. </span>
                    </div>


                    <div style="position: fixed; text-align: right; margin-right: 8px;  bottom:110px;right:5px;">
                        {{-- <img src="{{ public_path('images/iso.jpg')}}" 
                            style="width: auto; height: 75px;"> --}}
                    </div>
                        <div style="position: fixed; text-align: right; margin-right: 10px;  bottom:15px;right:0px;">
                              @if($wData == 1)
                               PhilRice RCEF FAR V{{$version_with_data}} Rev 01 Effectivity Date: 04 Sept 2023
                            @else
                               PhilRice RCEF FAR V{{$version_no_data}} Rev 01 Effectivity Date: 04 Sept 2023
                            @endif

                         </div>
                </th>
            </tr>


            </table>

            
        
        <div class="page-break"></div>
    @endfor
    

   

</body>
</html>
    
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
     $w_data_col = 21;
     $wo_data_col = 24;
    $version_with_data = "6.1";
    $version_no_data = "6.1B";
    
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
                <img src="{{ public_path('images/da_philrice.jpg')}}" 
                style="width: auto; height: 110px; margin-top:0px;margin-left: 25%; z-index: -50; position: absolute; top:10px;">
               
                <div style="margin-top:10px;">
                    <center><span style="font-size: 20px; font-weight:bold;"> <br>Farmer  Acknowledgement  Receipt <br> Description Guide</span></center>
                </div>
                <img src="{{ public_path('images/rcef_seed_program.jpg')}}" 
                style="width: auto; height: 100px; margin-top:15px;margin-left: 70%; z-index: -50; position: absolute; top:10px;">
                    <td style="border-style: none;">
                </tr>
         
            </thead>
        </table>
    </div>
       

        <div class="row" style="margin-top: 70px; margin-left:10%; width:80%;">

    <table style="font-size: 20px;border-style: none;">
        {{-- <thead>
            <tr>
                <th>Labels</th>
                <th>Description</th>

            </tr>
        </thead> --}}
        <tbody>
            <tr>   <td style="border-style: none;">  <strong>&#x2022; RSBSA No.
                (FFRS System
                Generated)</strong> </td>
                <td style="border-style: none;">Unique code assigned to each farmer contained in the RSBSA stub/ RCEF ID/ DA Interventions Monitoring Card, which will be presented upon seed claim. </td>
            </tr>

            <tr> 
                <td style="border-style: none; padding-top:10px;">
                    <strong>&#x2022; Registered Rice Area</strong>
                </td>
                <td style="border-style: none;">
                    Maximum/Total Physical Rice Area
                </td>
            </tr>

            <tr> 
                <td style="border-style: none;">
                    <strong>&#x2022;Area to be planted (ha)</strong>
                </td>
                <td style="border-style: none;">
                    Intended area for planting for <strong> 2023 Wet Season </strong>
                </td>
            </tr>


            <tr> 
                <td style="border-style: none;">
                    <strong>&#x2022;No. of bags (20kg/bag)</strong>
                </td>
                <td style="border-style: none;">
                    Number of bags received per variety
                </td>
            </tr>

            <tr> 
                <td style="border-style: none;">
                    <strong>&#x2022;Variety Received</strong>
                </td>
                <td style="border-style: none;">
                    Name of variety received
                </td>
            </tr>



            <tr> 
                <td style="border-style: none;">
                    <strong>&#x2022; Seed Class</strong>
                </td>
                <td style="border-style: none;">
                    [ I = Inbred | H = Hybrid | F= Farmer Seed ]
                </td>
            </tr>

            <tr> 
                <td style="border-style: none;">
                    <strong>&#x2022;Planted Variety</strong>
                </td>
                <td style="border-style: none;">
                    Variety planted in <strong> previous Season (2023 Dry season) </strong>
                </td>
            </tr>

            <tr> 
                <td style="border-style: none;">
                    <strong>&#x2022;Area harvested (ha)</strong>
                </td>
                <td style="border-style: none;">
                    Total area harvested during the <strong> previous season (2023 Dry Season) </strong>
                </td>
            </tr>



            <tr> 
                <td style="border-style: none;">
                    <strong>&#x2022;Total harvest (bag)</strong>
                </td>
                <td style="border-style: none;">
                    Total number of bags from the thresher/harvester during the previous season (including shares of harvesters, machine/s, owner [if land is leased/rented])
                </td>
            </tr>

            <tr> 
                <td style="border-style: none;">
                    <strong>&#x2022;Average wt. per bag (kg)</strong>
                </td>
                <td style="border-style: none;">
                    Average weight per bag in kilograms of palay harvested in the <strong> previous season (2023 Dry Season)</strong>
                </td>
            </tr>

          
            <tr> 
                <td style="border-style: none;">
                    <strong>&#x2022; Crop Establishment</strong>
                </td>
                <td style="border-style: none;">
                    [D-Direct Seeding | T-Transplanted]
                </td>
            </tr>

            <tr> 
                <td style="border-style: none;">
                    <strong>&#x2022; Irrigation Source</strong>
                </td>
                <td style="border-style: none;">
                  <strong> Irrigated </strong> : [NIS, CIS, SWIS, STW] | <strong> Rainfed </strong> : [RU = Upland, RL = Lowland]
                </td>
            </tr>

            <tr> 
                <td style="border-style: none;">
                    <strong>&#x2022; Expected Sowing Date</strong>
                </td>
                <td style="border-style: none;">
                    [Month / Week]-Format: &nbsp;  &nbsp;<strong>1 - 12 </strong> (Jan. - Dec.) / &nbsp;&nbsp; <strong>1 - 4</strong> (1st - 4th Week) 
                </td>
            </tr>

          

        </tbody>

    </table>
 </div>


</div>


 <hr style="margin-top: 30px; width:80%;">
 <div class="row" style="margin-top: 10px; margin-left:10%; width:80%;">
    
     <table style="border: 0; font-size: 20px; margin-left: 10px; margin-top: 0px;" >
        <thead>
            <tr>
                <th style="border-style: none;">
                    Data Sharing Consent
                    </th>
            </tr>
           
        </thead>
         <tr> 
            <td  style="border-style: none;"> 
                Sumasang-ayon ka ba na ang lahat ng impormasyon na inyong ibabahagi ay maaring gamitin ng PhilRice at ng Department of Agriculture sa iba pang programa nito?  Lagyan ng "<img src="{{ public_path('images/far_check.jpg')}}" style="height: 12px; width: auto;">" kung ikaw ay sumasang-ayon  at "<img src="{{ public_path('images/far_cross.jpg')}}" style="height: 12px; width: auto;">" kung hindi.
            </td>
     

        </tr>
    </table>
    </div>
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
                    <div style="position:fixed; left:61%; top:30px;; border-style:dashed; width:450px; height:80px; z-index: -50; padding-left:10; padding-right:10; padding-bottom:10; padding-top:0;  ">
                        <p style="">
                            <b> Important Notice</b> <br>
                            Pursuant to the DA Memorandum released on February 28, 2023, only farmers cultivating at least 1,000 sqm (0.1ha) are eligible to receive seeds from the program. Likewise, those who are cultivating more than five (5) ha are only allowed to receive seed support equivalent to a maximum of 5 ha. </p>
                    </div>

                    @if($wData == 1)
                        <th colspan="{{$w_data_col}}" style="page-break-after: always;">
                    @else
                        <th colspan="{{$wo_data_col}}" style="page-break-after: always;">
                    @endif
                        
                            <img src="{{ public_path('images/da_philrice.jpg')}}" 
                            style="width: auto; height: 110px; margin-top:0px;margin-left: 1%; z-index: -50; position: absolute; top:10px;">
                           
                            <div style="margin-top:10px; margin-left: 11%;">
                                <left><span style="font-size: 20px;"> <br>Farmer  Acknowledgement  Receipt (Seeds) </span></left>
                                <left><span style="font-size: 20px;"></span></left>
                            </div>

                            

                            <img src="{{ public_path('images/rcef_seed_program.jpg')}}" 
                            style="width: auto; height: 100px; margin-top:15px;margin-left: 50%; z-index: -50; position: absolute; top:10px;">

                     
                        
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



                       
                        <div style="position: fixed; text-align: left; margin-right: 10px;  top:0px;right:0px;"> Privacy Notice: In accordance with Republic Act No. 10173, otherwise known as Data Privacy Act of 2012, PhilRice ensures that all data collected are treated with strict confidentiality</div>
                        @if($wData == 1)
                        <div style="position: fixed; text-align: right; margin-right: 10px;  top:0px;right:0px;"> FAR V{{$version_with_data}}</div>
                        @else
                        <div style="position: fixed; text-align: right; margin-right: 10px;  top:0px;right:0px;"> FAR V{{$version_no_data}}</div>
                        @endif


                        @if($mark == "empty")

                        <div style="margin-top: 15px;margin-left: 11%; margin-bottom:25px;">
                            <span>Year/Season: _______________</span><br>
                            <span>Drop-off Point (City/Municipality, Province) _______________________________</span><br>
                            <span>RSBSA Code: Region:____, Province:____, Municipality:____</u>
							_, Barangay:_____
							
							</span>
                        </div>
                        @else
                        <div style="margin-top: 15px;margin-left: 11%; margin-bottom:25px;">
                            <span>Year/Season: __<u>2023 Wet Season</u>__</span><br>
                            <span>Drop-off Point (City/Municipality, Province) __<u>{{strtoupper($municipality)}}, {{strtoupper($province)}}</u>__</span><br>
                            <span>RSBSA Code: Region:_<u>{{$region_code}}</u>_, Province:_<u>{{$province_code}}</u>_, Municipality:_<u>{{$municipality_code}}</u>
							_, Barangay:_<u>{{$brgy}}</u>_
							
							</span>
                        </div> 
                        @endif
                        

                        
                    </th>

                </tr>
				
				@if($wData == 1)
				<tr>
                    <th rowspan="2" style=""></th>
                    <th style="" colspan="3"><center>FARMER NAME</center></th> 
                    <th style="width: 110px;" rowspan="2" align="center">RSBSA No. <br> (FFRS System Generated) </th>

                    <th  style="width: 20px; font-size:10px;" rowspan="2" align="center">Registered <br>Rice <br> Area</th>
                    <th  align="center" style="width: 20px; font-size:10px;" rowspan="2">Area to be planted (ha)</th>
                    <th style="width: 30px; font-size:10px;" rowspan="2" align="center"> Number of bags <br> (20kg/bag)</th>
                    <th style="width: 70px;" rowspan="2" align="center"> Variety Received</th>
                  
                    {{-- <th rowspan="2" style="width: 25px;"><center>Sex<br>(M/F)</center></th> --}}
                    <th  style="width:20px; font-size: 10px;" align="center" rowspan="2">Crop Estab. <br> [D/T] </th>
                    <th style="width:40px;" rowspan="2"><center>Irrigation Source</center></th> 

                    <th  style="width:40px; font-size: 10px;" align="center" rowspan="2">Expected Sowing Date <br> [Month/Week] </th>


                    <th style="font-size: 10px;" style="width:20px;" rowspan="2"><center> Data Sharing Consent <br> (<img src="{{ public_path('images/far_check.jpg')}}" style="height: 12px; width: auto;">/ X)</center></th>
                    <th style="" colspan="5"><center>2023 DS YIELD <br> Major Seed and Variety Planted</center></th> 



               

                    <th style="font-size: 10px; width: 90px;"><center>Name of Authorized Representative </center></th>
                    <th rowspan="2" style=" font-size: 10px; width: 40px;" align="center">Signature of Claimant</th>
                 
                    <th rowspan="2" align="center" style="width: 40px;">Date Received <i style="font-weight: 1px; font-size: 10px;" > (mm/dd/yr) </i></th>
				</tr>
                <tr>
                    <th colspan="3" align="center" style="font-weight: 1px; font-style: italic; font-size: 9px; height: 22px; ">(Last Name, First Name, Middle Name)</th>
                    {{-- <th align="center" style="font-size: 10px; font-style: italic;">(must be equal or less than Reg. Area)</th> --}}
                    <th  style="width:30px; font-size: 10px;" align="center">Seed Class <br> (H/I/F) </th>
                    <th  style="width:60px; font-size: 10px;" align="center">Planted Variety </th>
                    <th  style="width:20px; font-size: 9px;" align="center">Area harvested <br> (ha)</th>
                    <th  style="width:20px; font-size: 9px;" align="center">Total harvest <br> (bag)</th>
                    <th  style="width:40px; font-size: 9px;" align="center">Ave. wt. <br> per bag <br> (kg)</th>
                    
                    
                    
                    
                



                    <th align="center" style="font-weight: 1px; font-style: italic; font-size: 10px;">(Last Name, First Name, Middle Initial)</th>
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

                                            <td><center> </center></td>
                                            <!--AGE -->
                                            <td align="center" style="font-size: 11px; width: 50px;"> </td>
                                            <td> </td>
                                            <td></td>
                                            <td></td>
                                            <!-- CONTACT -->
                                            <td></td>
                                            <!--YIELD-->         
                                            <td></td>

                                            <td></td>
                                            <td></td>
                                            <!--SBSA Registered Area-->
                                            <td></td>
                                            <td style="width: 40px;"> </td>
                                            <td style="width: 40px;"> </td>
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
                  
                  

               
                    <td align="center">{{$list[$cnt]["crop_area"]}}</td>
                    @endif
                    <!-- CONTACT -->

                    @if(isset($list[$cnt]["is_prereg"]))
                    <td align="center"> {{$list[$cnt]["farmer_declared_area"]}}</td>
                    @else
                    <td></td>
                    @endif



                    @if(isset($list[$cnt]["is_prereg"]))
                    <td align="center">{{$list[$cnt]["bags"]}}</td>
                    @else
                    <td> </td>
                    @endif

                    

                    {{-- VARIETY --}}
                    <td></td> 

                    
                    @if(isset($list[$cnt]["is_prereg"]))
                        @if($list[$cnt]["crop_establishment"] == "transplanting")
                        <td align="center">T</td>

                        @else
                        <td align="center">D</td>

                        @endif
                    @else
                    <td> </td>
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
                    <td style="width: 40px;" align="center"></td>
                    @endif




					<td style="width: 40px;"> </td>

                    @if(isset($list[$cnt]["is_prereg"]))
                        <td align="center" style="width: 20px;">I</td>
                    @else
                        <td style="width: 20px;"> </td>
                    @endif
                    <!-- REPRESENTATIVE -->
{{--  --}}
<td style="width: 50px;" align="center"></td>
                    @if(isset($list[$cnt]["is_prereg"]))
                        <td style="width: 40px;" align="center">{{$list[$cnt]["yield_area"]}}</td>
                    @else
                        <td style="width: 40px;" align="center"> </td>
                    @endif

                    <!--SBSA Registered Area-->
                    

                    @if(isset($list[$cnt]["is_prereg"]))
                    <td style="width: 40px;" align="center">{{$list[$cnt]["yield_no_bags"]}}</td>
                    @else
                    <td style="width: 40px;" align="center"></td>
                    @endif

                    <!--MAX BAG-->
                    @if(isset($list[$cnt]["is_prereg"]))
                    <td style="width: 40px;" align="center">{{$list[$cnt]["yield_weight_bags"]}}</td>
                    @else
                    <td style="width: 40px;" align="center"></td>
                    @endif


                   
                    


                    <td style="width: 140px;"> </td>
                  
                    <td style="width: 70px; font-size: 11px;"> </td>
                    <td style="width: 10px;"> </td>
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
                    <td></td>
                   
                    <td></td>
                      
                    <td style="width: 30px;"></td>    
                    <td></td>
                    <td style="width: 20px; font-size: 11px;"></td>    
                    <td></td>
                    <!--SBSA Registered Area-->
                    <td style="width: 60px;"></td>
                    <!-- NO OF BAGS -->
                    <td style="width: 80px;"></td>
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
                                            <td colspan="3" style="width:230px; font-size: 13px;"> </td>
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
                                            
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <!--SBSA Registered Area-->
                                            <td ></td>
                                            <td ></td>
                                            <td ></td>
                                           
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
                                            <!-- CONTACT -->
                                            <td></td>
                                            <!--YIELD-->         
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <!--SBSA Registered Area-->
                                            <td style="width: 60px;"></td>
                                            <td style="width: 50px;"></td>
                                            <td style="width: 80px;"> </td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
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
                   
                    <div style="margin-top:30px;margin-left: 10px; width:500px; ">
                        <span>Issued By: </span>
                        <div style="margin-left: 50px;">
                            <span>_______________________________________________</span><br>
                            <span style="margin-left: 3px; font-size: 10px;">Name, signature and position of authorized LGU Representative</span><br><br>
                           
                        </div>
                    </div>

                    <div style="position: fixed; right:0; bottom:60; width:500px;">
                        <span>Noted By: </span>
                        <div style="margin-left: 50px;">
                            <span>__________________________________________________</span><br>
                            <span style="margin-left:5px; font-size: 10px;">Name and signature of RCEF Seed Regional/Provincial Coordinator</span>
                    
                        </div>
                    </div>


                    <div style="position: fixed; right:0; bottom:70; width:550px;">
                       
                            <span>Total Farmers: __________________  Total Area: __________________  Total Bags: __________________</span><br>
                    
                    </div>


                    <div style="position: fixed; text-align: right; margin-right: 8px;  bottom:110px;right:5px;">
                        {{-- <img src="{{ public_path('images/iso.jpg')}}" 
                            style="width: auto; height: 75px;"> --}}
                    </div>
                        <div style="position: fixed; text-align: right; margin-right: 10px;  bottom:15px;right:0px;">
                              @if($wData == 1)
                               PhilRice RCEF FAR V{{$version_with_data}} Rev 03 Effectivity Date: 03 March 2023
                            @else
                               PhilRice RCEF FAR V{{$version_no_data}} Rev 03 Effectivity Date: 03 March 2023
                            @endif

                         </div>
                </th>
            </tr>


            </table>

            
        
        <div class="page-break"></div>
    @endfor
    

   

</body>
</html>
    
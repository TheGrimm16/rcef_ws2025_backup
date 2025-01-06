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
     $w_data_col = 20;
     $wo_data_col = 23;
    $version_with_data = "4.1";
    $version_no_data = "4.2";
    
     
     $row_param = 20;
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
		 $page_count++;
         $page_count++;
         $page_count++;
         
		 $addBlankPage =1;
        
       // $page_count = 1;
    ?>

    @for ($i = 1; $i <= $page_count; $i++)
		  <?php  
			$cntIdentifier = 0;
			$breaker =0;
			
			 if(empty($list[$cnt])){
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
                    @if($wData == 1)
                        <th colspan="{{$w_data_col}}" style="page-break-after: always;">
                    @else
                        <th colspan="{{$wo_data_col}}" style="page-break-after: always;">
                    @endif
                    
                        <img src="{{ public_path('images/da_philrice.jpg')}}" 
                            style="width: auto; height: 110px; margin-top:15px;margin-left: 20px; z-index: -50; position: absolute;">

                            <img src="{{ public_path('images/rcef_seed_program.jpg')}}" 
                            style="height: 100px; width: auto;margin-top:25px;margin-left: 140px; position: absolute;">

                        <div style="margin-top:10px; width: 21%; margin-left: 260px;">
                            <left><span style="font-size: 20px;"> <center> Farmer Acknowledgement Receipt  <br>(Seeds) </center> </span></left>
                            <left><span style="font-size: 20px;"></span></left>
                        </div>

                        
                        <div style="margin-top:10px; border: 1px dotted black; width: 20%; margin-left: 260px;">
                             <span style="font-size: 14px;">*Importance of Active Mobile Number *</span>
                            <hr style="margin-top: 0px;">   
                             <table style="border: 0; margin-left: 10px; margin-top: 0px;" width="100%">
                                 <tr> 
                                    <td  style="border-style: none;"> 
                                     Farmers with active & updated mobile numbers will have a chance to participate in the "Binhi e-Padala" Program.
                               </td>
                            
                                </tr>
                            </table>

                        </div>



                        <div style="position: absolute; border: 1px dotted black; top:10px; width: 43.5%; height: auto; right:300px;">
                            <span style="font-size: 14px;">Important Notes:</span>
                            <hr style="margin-top: 0px;">

                            <table style="border: 0; margin-left: 10px; margin-top: 0px;" width="100%">
                                <tr>   <td style="border-style: none;" width="100%" colspan="2"> * <u>RSBSA No</u>- RSBSA stub on-hand of each individual farmer, and will be presented upon claiming of seeds.  </td>
                                </tr>
                                <tr> 
                                    <td colspan="2" style="border-style: none;"> *<u> Gross harvest</u> - Total number of bags from the thresher/harvester during the previous season (including shares of harvesters, machine/s, owner [if land is leased/rented])</td>
                             

                                </tr>
                                <tr> 
                                    <td colspan="2" style="border-style: none;"> *<u> Average wt. (kg) </u>- Average weight per bag in kilograms of the previous season's harvest</td>
                                </tr>
                                <tr> 
                                    <td colspan="2" style="border-style: none;"> * <u>Area harvested (ha) </u>- Total area harvested during the previous season</td>
                                </tr>
                                
                                <tr> 
                                    <td width="70%" style="border-style: none;" > * <u>Area to be planted (ha) </u>- Intended area for planting for wet season 2022 </td>

                                    <td style="border-style: none;" width="30%"> * <u>No. of bags</u> - Number of bags received per variety</td>

                                </tr>

                                <tr> 
                                    <td width="50%"  style="border-style: none;" > * <u>Total Area (ha) </u>- Maximum/Total Physical Rice Area </td>
                                    <td style="border-style: none;" width="50%" >  * <u>Variety Received </u>- Name of variety received</td>
                                    
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
                        <div style="position: absolute; border: 1px dotted black; top:10px; width: 18%; height: 185px; right:10px;">
                             <span style="font-size: 14px;">Data Sharing Consent:</span>
                            <hr style="margin-top: 0px;">   
                             <table style="border: 0; margin-left: 10px; margin-top: 0px;" width="100%">
                                 <tr> 
                                    <td  style="border-style: none;"> 
                                        Sumasang-ayon ka ba na ang lahat ng impormasyon na inyong ibabahagi ay maaring gamitin ng PhilRice at ng Department of Agriculture sa iba pang programa nito?  Lagyan ng <br> "<img src="{{ public_path('images/far_check.jpg')}}" style="height: 12px; width: auto;">" kung ikaw ay sumasang-ayon  at <br>"<img src="{{ public_path('images/far_cross.jpg')}}" style="height: 12px; width: auto;">" kung hindi.
                                    </td>
                             

                                </tr>
                            </table>

                        </div>
                        <div style="position: fixed; text-align: left; margin-right: 10px;  top:0px;right:0px;"> Privacy Notice: In accordance with Republic Act No. 10173, otherwise known as Data Privacy Act of 2012, PhilRice ensures that all data collected are treated with strict confidentiality</div>
                        @if($wData == 1)
                        <div style="position: fixed; text-align: right; margin-right: 10px;  top:0px;right:0px;"> FAR V{{$version_with_data}}</div>
                        @else
                        <div style="position: fixed; text-align: right; margin-right: 10px;  top:0px;right:0px;"> FAR V{{$version_no_data}}</div>
                        @endif


                        <div style="margin-top: 10px;margin-left: 10px; margin-bottom:10px;">
                            <span>Year/Season: __<u>Wet Season 2022</u>___</span><br>
                            <span>Drop-off Point (City/Municipality, Province) __<u>{{strtoupper($municipality)}}, {{strtoupper($province)}}</u>__</span><br>
                            <span>RSBSA Code: Region_<u>{{$region_code}}</u>_, Province:_<u>{{$province_code}}</u>_, Municipality:_<u>{{$municipality_code}}</u>
							_, Barangay:_<u>{{$brgy}}</u>_
							
							</span>
                        </div>
                        
                    </th>
                </tr>
				
				@if($wData == 1)
				<tr>
                    <th rowspan="2" style=""></th>
                    <th style="" colspan="3"><center>FARMER NAME</center></th> 
                    <th style="width: 120px;" rowspan="2" align="center">RSBSA No.</th>
                 
                    
                    <th rowspan="2" style="width: 25px;"><center>Sex<br>(M/F)</center></th>
                    
                   
                    <th style="font-size: 10px;" style="width:40px;"><center> Data Sharing Consent</center></th>

                    <th  style="width:90px;" align="center">Active Mobile Number </th>
                    <th style="" colspan="3"><center>2022 DS YIELD</center></th> 
                    <th style="width:80px;" rowspan="2"><center>Irrigation Status <br>  [ I | RU | RL ]</center></th> 
                    <th style="width: 50px;" rowspan="2" align="center"> Total Area</th>
                    <th style="width: 50px;" rowspan="2" align="center"> Number of bags</th>
                    <th style="width: 50px;" rowspan="2" align="center"> Variety Received</th>
                    
                    
                    <th  align="center" style="width: 80px;">Area to be planted</th>
                    
                    
                    <th style="font-size: 10px; width: 100px;"><center>Name of Authorized Representative </center></th>
                    <th rowspan="2" style=" font-size: 10px; width: 80px;" align="center">Signature of Claimant</th>
                    <th  style="width:60px; font-size: 10px;" align="center" rowspan="2">Date of Sowing <br> [Month/Week] </th>
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
				@else
				<tr>
                    <th rowspan="2" style=""></th>
                    <th style="" colspan="3"><center>FARMER NAME</center></th> 
                    <th style="width: 120px;" rowspan="2" align="center">RSBSA No.</th>
                    <th rowspan="2" style="width: 25px;"><center>Sex<br>(M/F)</center></th>
                    
                    <th align="center" style="font-size: 10px; width: 80px;"> Birthdate</th>
                     <th style="margin: 0px; font-size: 10px; padding: 0px; width: 40px;"><center>PWD</center></th>
                    <th style="margin: 0px; font-size: 9px; width: 40px;"> <center>Member of IP</center></th>
                    <th style="font-size: 10px;" style="width:40px;"><center> Data <br> Sharing <br> Consent</center></th>
                    <th  style="width:90px;" align="center">Active Mobile Number </th>
                    <th style="" colspan="3"><center>2022 DS YIELD</center></th> 
                    <th style="width:80px;" rowspan="2"><center>Irrigation Status <br>  [ I | RU | RL ]</center></th> 
                    <th style="width: 50px;" rowspan="2" align="center"> Total Area</th> 
                    <th style="width: 50px;" rowspan="2" align="center"> Number of bags</th>
                    <th style="width: 50px;" rowspan="2" align="center"> Variety Received</th>
                    <th  align="center" style="width: 80px;">Area to be planted</th>
                    <th style="font-size: 10px;"><center>Mother's Maiden Name </center></th>
                    <th rowspan="2" style=" font-size: 10px;" align="center">Signature of Claimant / Name of Authorized Representative</th>
                    <th  style="width:60px; font-size: 10px;" align="center" rowspan="2">Date of Sowing <br> [Month/Week] </th>
                    <th rowspan="2" align="center" style="width: 80px;">Date Received <i style="font-weight: 1px; font-size: 10px;" > <br> (mm/dd/yr) </i></th>
                </tr>
                <tr>
                    <th colspan="3" align="center" style="font-weight: 1px; font-style: italic; font-size: 10px; height: 22px; ">(Last Name, First Name, Middle Name)</th>
                    <th align="center">(mm/dd/yy)</th>
                    <th style="width: 100px;" colspan="3"><center>Yes:"<img src="{{ public_path('images/far_check.jpg')}}" style="height: 12px; width: auto;">" No:"<img src="{{ public_path('images/far_cross.jpg')}}" style="height: 12px; width: auto;">"</center></th>
                    <th align="center" style="font-size:9px; font-style:italic;"> (11 Characters) <br> 09XXXXXXXXX</th>
                    <th  style="width:50px; font-size: 10px;" align="center">Area harvested <br> (ha)</th>
                    <th  style="width:50px; font-size: 10px;" align="center">Gross harvest <br> (bag)</th>
                    <th  style="width:50px; font-size: 10px;" align="center">Ave. wt. per bag <br> (kg)</th>
                  
                



                    <th align="center" style="font-size: 10px; font-style: italic;">(must be equal or less than RSBSA Area)</th>
                    <th align="center" style="font-weight: 1px; font-style: italic; font-size: 10px;">(Last Name, First Name, Middle Initial)</th>
                </tr>

                

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
									    <td style="width: 10px; height: 30px;">{{$itm}} </td>
                                        <td colspan="3" style="width:230px; height: 17px; font-size: 13px;"> </td>
                                        <td style="width: 60px; font-size: 11px;"> </td>
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
                                        <!--SBSA Registered Area-->
                                        <td style="width: 60px;"> </td>
                                        <!-- BAGS -->
                                        <td style="width: 80px;"></td>
                                        <!-- Variety -->
                                        <td style="width: 50px;" align="center"> </td>
                                        <!-- Area to be planted -->
                                        <td></td>
                                        <!-- REPRESENTATIVE -->
                                        <td style="width: 100px;"> </td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                               
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
					<td style="width: 10px; height: 30px;">{{$itm}} </td>
					<td colspan="3" style="width:260px; height: 17px; font-size: 13px;"><b> {{strtoupper($list[$cnt]["last_name"])}}, {{strtoupper($list[$cnt]["first_name"])}} {{strtoupper($list[$cnt]["mid_name"])}} {{$list[$cnt]["ext_name"]}} </b></td>
					<td style="width: 60px; font-size: 11px;" align="center">{{$list[$cnt]["rsbsa_control_no"]}}</td>
                  


					<td><center>{{strtoupper($list[$cnt]["sex"] == "Female" ? "F" : "M")}}</center></td>
					<!-- Y/N -->
               
                    <td> </td>
                    
                    <!-- CONTACT -->
                    <td align="center">{{$list[$cnt]["contact_num"]}}</td>
                    <!--YIELD-->         
{{--                     
                    @if($list[$cnt]["area_harvested"] == 0 ||$list[$cnt]["yield"]==0 || $list[$cnt]["weight_per_bag"]==0 ) --}}
                    <td align="center">{{$list[$cnt]["area_harvested"]}} </td>
                    <td align="center">{{$list[$cnt]["total_production"]}} </td>
                    <td align="center">{{$list[$cnt]["ave_weight_bag"]}} </td>
                    {{-- @else
                    <td> {{$list[$cnt]["area_harvested"]}}</td>
                    <td> {{$list[$cnt]["yield"]}}</td>
                    <td> {{$list[$cnt]["weight_per_bag"]}}</td>
                    @endif --}}
                    
                        @if($list[$cnt]["eco_system"] == "Irrigated")
                            <td align="center">I</td>
                        @elseif($list[$cnt]["eco_system"] == "Rainfed")
                            <td align="center">R</td>
                        @else
                            <td align="center"> </td>
                        @endif

                    




                    <!--SBSA Registered Area-->
                        <td style="width: 40px;" align="center"> {{$list[$cnt]["actual_area"]}}</td>
                    <!--MAX BAG-->
                        <?php 
                            $bags = ceil($list[$cnt]["claim_area"] *2);
                        ?>

                    <td style="width: 60px;" align="center"> {{$bags}}</td>
                    <td style="width: 80px;" align="center"> </td>
                    <!-- AREA SERVED -->
					<td style="width: 80px;" align="center"> {{$list[$cnt]["claim_area"]}}</td>
                   
                    <!-- REPRESENTATIVE -->
                    <td style="width: 100px;"> </td>
                    <td></td>
                    <td align="center"> {{$list[$cnt]["sowing_date"]}}</td>
                    <td style="width: 70px;"> </td>
				</tr>
					  {{ $cntIdentifier++}}
						  {{$itm++}}
                @else
                <tr>
                    <td style="width: 10px; height: 30px;">{{$itm}} </td>
                    <td colspan="3" style="width:230px; height: 17px; font-size: 13px;"> </td>
                    <td style="width: 60px; font-size: 11px;"> </td>
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
                    <!--SBSA Registered Area-->
                    <td style="width: 60px;"></td>
                    <!-- NO OF BAGS -->
                    <td style="width: 80px;"></td>
                    <td style="width: 80px;"> </td>
                    <td></td>                   
                    <!-- REPRESENTATIVE -->
                    <td style="width: 100px;"> </td>
                    <td></td>
                    <td></td>
                    <td style="width: 80px;"> </td>
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
                                            <td style="width: 10px; height: 30px;">{{$itm}} </td>
                                            <td colspan="3" style="width:230px; height: 17px; font-size: 13px;"> </td>
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
                                            <!--SBSA Registered Area-->
                                            <td style="width: 60px;"></td>
                                            <td style="width: 60px;"></td>
                                            <td style="width: 80px;"> </td>
                                            <td></td>
                                            <!-- REPRESENTATIVE -->
                                            <td style="width: 100px;"> </td>
                                            <td></td>
                                            <td></td>
                                            <td style="width: 80px;"> </td>
                                         </tr>
                                      @else
                                         <tr>
                                            <td style="width: 10px; height: 29.5px;">{{$itm}} </td>
                                            <td colspan="3" style="width:200px;  font-size: 10px;"> </td>
                                            <td style="width: 60px; font-size: 11px;"> </td>
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
                                            <td> </td>
                                            <!-- REPRESENTATIVE -->
                                            <td style="width: 120px;"> </td>
                                            <td></td>
                                            <td></td>
                                            <td style="width: 80px;"> </td>
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

                    <div style="margin-top:0px;margin-left: 10px;">
                        <span>Issued By: </span>
                        <div style="margin-left: 50px;">
                            <span>_______________________________________________</span><br>
                            <span style="margin-left: 3px; font-size: 10px;">Name, signature and position of authorized LGU Representative</span><br><br>
                           
                        </div>
                    </div>

                    <div style="margin-left: 900px;margin-top:-115px;">
                        <span>Noted By: </span>
                        <div style="margin-left: 50px;">
                            <span>__________________________________________________</span><br>
                            <span style="margin-left:5px; font-size: 10px;">Name and signature of RCEF Seed Regional/Provincial Coordinator</span><br><br><br><br>
                    
                        </div>
                    </div>
                    <div style="position: fixed; text-align: right; margin-right: 8px;  bottom:110px;right:5px;">
                        <img src="{{ public_path('images/iso.jpg')}}" 
                            style="width: auto; height: 75px;">
                    </div>
                        <div style="position: fixed; text-align: right; margin-right: 10px;  bottom:15px;right:0px;">
                              @if($wData == 1)
                               PhilRice RCEF FAR V{{$version_with_data}} Rev 01 Effectivity Date: 15 March 2022
                            @else
                               PhilRice RCEF FAR V{{$version_no_data}} Rev 01 Effectivity Date: 15 March 2022
                            @endif

                         </div>
                </th>
            </tr>


            </table>
        
        <div class="page-break"></div>
    @endfor
    

   

</body>
</html>
    
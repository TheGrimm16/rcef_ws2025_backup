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
    //dd($list);
     $row_param = 20;
     $page_count = ceil(count($list) /  $row_param)?>
	 

    <?php $totalFarmer = count($list); ?>
    <?php $array_index = 1?>
    <?php $cnt = 0;
		  $itm = 1;

      
	?>
    
    <?php
        $brgy = $list[0]["barangay"];

        foreach ($list as $key => $value) {
           if($list[$key]["barangay"]!=$brgy){
            $brgy = $list[$key]["barangay"];
            $page_count++;
           }
        }
         $brgy = $list[0]["barangay"];
		 //$page_count++;
         //$page_count++;
         //$page_count++;
         
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
//dd($list);
			?>
        
		<table style="width:100%; margin-right: 10px; margin-top: 15px;">
            <thead>
                <tr>
                    @if($wData == 1)
                        <th colspan="21" style="page-break-after: always;">
                    @else
                        <th colspan="21" style="page-break-after: always;">
                    @endif
                    
                        <img src="{{ public_path('images/da_philrice.jpg')}}" 
                            style="width: auto; height: 110px; margin-top:15px;margin-left: 20px; z-index: -50; position: absolute;">

                            <img src="{{ public_path('images/rcef_seed_program.jpg')}}" 
                            style="height: 100px; width: auto;margin-top:25px;margin-left: 140px; position: absolute;">

                        <div style="margin-top:30px; width: 50%; margin-left: 260px;">
                            <left><span style="font-size: 20px;">(Binhi e-Padala) <br> Farmer Acknowledgement Receipt <br> (Seeds) </span></left>
                            <left><span style="font-size: 20px;">RCEF Seed Program</span></left><br> <br> 
                        </div>

                        <div style="position: absolute; border: 1px dotted black; top:10px; width: 42%; height: auto; right:310px;">
                            <span style="font-size: 14px;">Important Notes:</span>
                            <hr style="margin-top: 0px;">

                            <table style="border: 0; margin-left: 10px; margin-top: 0px;" width="100%">
                                <tr> 
                                    <td colspan="2" style="border-style: none;"> * Gross harvest - Total number of bags from the thresher/harvester during the previous season (including shares of harvesters, machine/s, owner [if land is leased/rented])</td>
                             

                                </tr>
                                <tr> 
                                    <td colspan="2" style="border-style: none;"> * Average wt. (kg) - Average weight per bag in kilograms of the previous season's harvest</td>
                                </tr>
                                <tr> 
                                    <td colspan="2" style="border-style: none;"> * Area harvested (ha) - Total area harvested during the previous season</td>
                                </tr>
                                
                                <tr> 
                                    <td width="100%" style="border-style: none;" colspan="2"> * Area to be planted (ha) - Intended area for planting for dry season 2022 </td>
                                </tr>

                                <tr> 
                                    <td width="100%" style="border-style: none;" colspan="2"> * RSBSA Registered Area (ha) - Area declared in the RSBSA </td>
                                </tr>
                                <!--
                                <tr > 
                                    <td style="border-style: none;" width="50%"> * IEC - Information, Education and Communication</td>
                                    <td style="border-style: none;" width="50%"> * KPs - Knowledge Products</td>
                                </tr> -->
                                <tr> 
                                    <td style="border-style: none;" width="50%"> * No. of bags - Number of bags of seeds received per variety</td>
                                    <td style="border-style: none;" width="50%"> * Variety Received- Name of variety / varieties received</td>
                                </tr>
                            </table>
                        </div>
                        <div style="position: absolute; border: 1px dotted black; top:10px; width: 18%; height: 150px; right:20px;">
                             <span style="font-size: 14px;">Data Sharing Consent:</span>
                            <hr style="margin-top: 0px;">   
                             <table style="border: 0; margin-left: 10px; margin-top: 0px;" width="100%">
                                 <tr> 
                                    <td  style="border-style: none;"> 
                                        Sumasang-ayon ka ba na ang lahat ng impormasyon na inyong ibabahagi ay maaring gamitin ng PhilRice at ng Department of Agriculture sa iba pang programa nito? Lagyan ng "<img src="{{ public_path('images/far_check.jpg')}}" style="height: 12px; width: auto;">" kung ikaw ay sumasang-ayon at "<img src="{{ public_path('images/far_cross.jpg')}}" style="height: 12px; width: auto;">" kung hindi.
                                    </td>
                             

                                </tr>
                            </table>

                        </div>
                        <div style="position: fixed; text-align: left; margin-right: 10px;  top:0px;right:0px;"> Privacy Notice: In accordance with Republic Act No. 10173, otherwise known as Data Privacy Act of 2012, PhilRice ensures that all data collected are treated with strict confidentiality</div>
                        @if($wData == 1)
                        <div style="position: fixed; text-align: right; margin-right: 10px;  top:0px;right:0px;"> FAR V4.4</div>
                        @else
                        <div style="position: fixed; text-align: right; margin-right: 10px;  top:0px;right:0px;"> FAR V4.5</div>
                        @endif


                        <div style="margin-top: 30px;margin-left: 10px;">
                            <span>Year/Season: __<u>Wet Season 2022</u>___</span><br>
                            <span>Drop-off Point (City/Municipality, Province) __<u>{{strtoupper($municipality_name)}}, {{strtoupper($province_name)}}</u>__</span><br>
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
                    <th style="width: 120px;" rowspan="2" align="center">DA-ICTS<br>RSBSA No.</th>
                    <th rowspan="2" style="width: 25px;"><center>Sex<br>(M/F)</center></th>
                    
                    <th align="center" style="font-size: 10px; width: 40px;"> 60 and above</th>
                     <th style="margin: 0px; font-size: 10px; padding: 0px; width: 40px;"><center>PWD</center></th>
                    <th style="margin: 0px; font-size: 9px; width: 40px;"> <center>Member of IP</center></th>
                    <th style="font-size: 10px;" style="width:40px;"><center> Data <br> Sharing <br> Consent</center></th>

                    <th  style="width:90px;" align="center">Contact Number </th>
                    <th style="" colspan="3"><center>2021 WS YIELD</center></th> 
                   
                    <th style="width: 50px;" rowspan="2" align="center"> RSBSA  Registered Area</th>
                    <th style="width: 50px;" rowspan="2" align="center"> Number of bags</th>
                    <th style="width: 50px;" rowspan="2" align="center"> Variety Received</th>
                    
                    
                    <th  align="center" style="width: 80px;">Area to be planted</th>
                    
                    
                    <th style="font-size: 10px; width: 170px;"><center>Name of Authorized Representative </center></th>
                    <th rowspan="2" style=" font-size: 10px; width: 110px;" align="center">Signature of Claimant</th>
                    <th rowspan="2" align="center" style="width: 80px;">Date Received <i style="font-weight: 1px; font-size: 10px;" > <br> (mm/dd/yr) </i></th>
				</tr>
                <tr>
                    <th colspan="3" align="center" style="font-weight: 1px; font-style: italic; font-size: 10px; height: 22px; ">(Last Name, First Name, Middle Name)</th>
                    <th style="width: 170px;" colspan="4"><center>Yes:"<img src="{{ public_path('images/far_check.jpg')}}" style="height: 12px; width: auto;">" No:"<img src="{{ public_path('images/far_cross.jpg')}}" style="height: 12px; width: auto;">"</center></th>
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
                    <th  style="width:90px;" align="center">Contact Number </th>
                    <th style="" colspan="3"><center>2021 WS YIELD</center></th> 
                    <th style="width: 50px;" rowspan="2" align="center"> RSBSA  Registered Area</th> 
                    <th style="width: 50px;" rowspan="2" align="center"> Number of bags</th>
                    <th style="width: 50px;" rowspan="2" align="center"> Variety Received</th>
                    <th  align="center" style="width: 80px;">Area to be planted</th>
                    <th style="font-size: 10px; width: 170px;"><center>Mother's Maiden Name </center></th>
                    <th rowspan="2" style=" font-size: 10px; width: 110px;" align="center">Signature of Claimant / Name of Authorized Representative</th>
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
                    if($brgy!=$list[$cnt]["barangay"]){
                           // $i++;
                            $brgy = $list[$cnt]["barangay"];
							if($cntIdentifier <  $row_param){
                                $r =  $row_param - $cntIdentifier ;
                                for($e=0;$e<$r;$e++){
                                    ?>
                                    <!-- EMPTY ROW -->
                                    <tr>
									    <td style="width: 10px; height: 30px;">{{$itm}} </td>
                                        <td colspan="3" style="width:230px; height: 17px; font-size: 13px;"> </td>
                                        <td style="width: 60px; font-size: 11px;"> </td>
                                        <td><center> </center></td>
                                        <!-- Y/N -->
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <!-- CONTACT -->
                                        <td></td>
                                        <!--YIELD-->         
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <!--SBSA Registered Area-->
                                        <td style="width: 60px;"> </td>
                                        <!-- BAGS -->
                                        <td style="width: 80px;"></td>
                                        <!-- Variety -->
                                        <td style="width: 110px;" align="center"> </td>
                                        <!-- Area to be planted -->
                                        <td></td>
                                        <!-- REPRESENTATIVE -->
                                        <td style="width: 170px;"> </td>
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
					<td colspan="3" style="width:260px; height: 17px; font-size: 13px;"><b> {{strtoupper($list[$cnt]["lastname"])}}, {{strtoupper($list[$cnt]["firstname"])}} {{strtoupper($list[$cnt]["middname"])}} </b></td>
					
     
                        <td style="width: 60px; font-size: 11px;">{{$list[$cnt]["rsbsa_control_no"]}}</td>
                

                    



					<td><center>
                        <?php
                            if($list[$cnt]["sex"] != "" || $list[$cnt]["sex"] != NULL){
                                if(strtoupper(substr($list[$cnt]["sex"], 0,1)) == "M"){
                                    echo "M";
                                }else{
                                    echo "F";
                                }
                            }
                        ?>
                    </center></td>
					<!-- Y/N -->
                    <td></td>
					<td></td>
                    <td></td>
                    <td></td>
                    
                    <!-- CONTACT -->
                    <td style="text-align:center;">{{$list[$cnt]["contact_no"]}}</td>
                    <!--YIELD-->         
                    <td></td>
                    <td></td>
                    <td></td>
                    <!--SBSA Registered Area-->
					
                        <td style="width: 60px;" align="center"> {{$list[$cnt]["area"]}}</td>
                

                    


                    <!--MAX BAG-->
                    <td style="width: 60px;" align="center">{{$list[$cnt]["bags"]}}</td>
                    <td style="width: 110px;" align="center"> </td>
                    <!-- AREA SERVED -->
					<td style="width: 80px; text-align: center;">{{$list[$cnt]["area"]}}</td>
                   
                    <!-- REPRESENTATIVE -->
                    <td style="width: 170px;"> </td>
                    <td></td>
                    <td style="width: 80px;"> </td>
				</tr>
					  {{ $cntIdentifier++}}
						  {{$itm++}}
                @else
                <tr>
                    <td style="width: 10px; height: 30px;">{{$itm}} </td>
                    <td colspan="3" style="width:230px; height: 17px; font-size: 13px;"> </td>
                    <td style="width: 60px; font-size: 11px;"> </td>
                    <td><center> </center></td>
                    <!-- Y/N -->
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    
                    <!-- CONTACT -->
                    <td></td>
                    <!--YIELD-->         
                    <td></td>
                    <td></td>
                    <td></td>
                    <!--SBSA Registered Area-->
                    <td style="width: 60px;"></td>
                    <!-- NO OF BAGS -->
                    <td style="width: 80px;"></td>
                    <td style="width: 110px;"> </td>
                    <td></td>                   
                    <!-- REPRESENTATIVE -->
                    <td style="width: 170px;"> </td>
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
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            
                                            <!-- CONTACT -->
                                            <td></td>
                                            <!--YIELD-->         
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <!--SBSA Registered Area-->
                                            <td style="width: 60px;"></td>
                                            <td style="width: 60px;"></td>
                                            <td style="width: 110px;"> </td>
                                            <td></td>
                                            <!-- REPRESENTATIVE -->
                                            <td style="width: 170px;"> </td>
                                            <td></td>
                                            <td style="width: 80px;"> </td>
                                         </tr>
                                      @else
                                         <tr>
                                            <td style="width: 10px; height: 30px;">{{$itm}} </td>
                                            <td colspan="3" style="width:230px; height: 17px; font-size: 13px;"> </td>
                                            <td style="width: 60px; font-size: 11px;"> </td>
                                            <td><center> </center></td>
                                            <!--AGE -->
                                            <td align="center" style="font-size: 11px; width: 30px;"> </td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <!-- CONTACT -->
                                            <td></td>
                                            <!--YIELD-->         
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <!--SBSA Registered Area-->
                                            <td style="width: 60px;"></td>
                                            <td style="width: 50px;"></td>
                                            <td style="width: 110px;"> </td>
                                            <td> </td>
                                            <!-- REPRESENTATIVE -->
                                            <td style="width: 170px;"> </td>
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
                        <th colspan="21">
                    @else
                         <th colspan="21">
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
                               PhilRice RCEF FAR V4.4 Rev 00 Effectivity Date: 04 April 2022
                            @else
                               PhilRice RCEF FAR V4.5 Rev 00 Effectivity Date: 04 April 2022
                            @endif

                         </div>
                </th>
            </tr>


            </table>
        
        <div class="page-break"></div>
    @endfor
    

   

</body>
</html>
    
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
     $row_param = 12;
     $col_param = 9;
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

			?>
        
		<table style="width:100%; margin-right: 50px; margin-top: 15px;">
            <thead>
                <tr>
                        <th colspan="{{$col_param}}" style="page-break-after: always;">
                        <img src="{{ public_path('images/da_philrice.jpg')}}" 
                            style="width: auto; height: 110px; margin-top:15px;margin-left: 20px; z-index: -50; position: absolute;">

                            <img src="{{ public_path('images/rcef_seed_program.jpg')}}" 
                            style="height: 100px; width: auto;margin-top:25px;margin-left: 140px; position: absolute;">

                        <div style="margin-top:30px; width: 50%; margin-left: 260px;">
                            <left><span style="font-size: 20px;">Farmer Acknowledgement Receipt <br> (Seeds) </span></left> 
                            <left><span style="font-size: 20px;">RCEF Seed Program <br> (Reference for Checking)</span></left>   <br> 
                        </div>

                        <div style="position: absolute; border: 1px dotted black; top:30px; width: 50%; height: auto; right:15px;">
                            <span style="font-size: 14px;">Important Notes:</span>
                            <hr style="margin-top: 0px;">

                            <table style="border: 0; margin-left: 10px; margin-top: 0px;" width="100%">
                               
                                <tr> 
                                    <td colspan="2" style="border-style: none;"> * DA-ICTS RSBSA No. - System generated RSBSA number cross-matched from the FFRS Database of DA</td>
                                </tr>
                                <tr> 
                                    <td colspan="2" style="border-style: none;"> * RSBSA No. (Collected) - Assigned RSBSA number collected on the ground from the respective municipality</td>
                                </tr>
                                
                                <tr> 
                                    <td width="100%" style="border-style: none;" colspan="2"> * RSBSA Registered Area (ha) - Area declared in the RSBSA </td>
                                </tr>

                          
                        
                            </table>
                        </div>
                    
                        <div style="position: fixed; text-align: left; margin-right: 10px;  top:0px;right:0px;"> Privacy Notice: In accordance with Republic Act No. 10173, otherwise known as Data Privacy Act of 2012, PhilRice ensures that all data collected are treated with strict confidentiality</div>
                        <div style="position: fixed; text-align: right; margin-right: 10px;  top:0px;right:0px;"> FAR V3.3</div>
                        

                        <div style="margin-top: 30px;margin-left: 10px;">
                            <span>Year/Season: __<u>Wet Season 2024</u>___</span><br>
                            <span>Drop-off Point (City/Municipality, Province) __<u>{{strtoupper($municipality)}}, {{strtoupper($province)}}</u>__</span><br>
                            <span>RSBSA Code: Region_<u>{{$region_code}}</u>_, Province:_<u>{{$province_code}}</u>_, Municipality:_<u>{{$municipality_code}}</u>
							_, Barangay:_<u>{{$brgy}}</u>_
							
							</span>
                        </div>
                        
                    </th>
                </tr>
				
			
				<tr>
                    <th rowspan="2" style=""></th>
                    <th style="" ><center>FARMER NAME</center></th> 
                    <th style="width: 60px;" rowspan="2" align="center">DA-ICTS<br>RSBSA No.</th>
                    <th style="width: 60px;" rowspan="2" align="center">RSBSA No.<br>(Collected)</th>
                     <th  style="width:80px;" align="center">Contact Number </th>

                     <th style="width: 50px;" rowspan="2" align="center"> RSBSA  Registered Area</th>
                    <th rowspan="2" style="width: 25px;"><center>Sex<br>(M/F)</center></th>
                    <th style="width: 50px;" rowspan="2" align="center"> Birthday <br> (mm/dd/yy) </th>
                    <th style="font-size: 10px; width: 120px;"><center> Mother's Maiden Name</center></th>
				</tr>
                <tr>
                    <th  align="center" style="font-weight: 1px; font-style: italic; font-size: 10px; height: 22px; ">(Last Name, First Name, Middle Name)</th>
                    <th align="center" style="font-size:9px; font-style:italic;"> (11 Characters) <br> 09XXXXXXXXX</th>
                    
                    <th align="center" style="font-weight: 1px; font-style: italic; font-size: 10px;">(Last Name, First Name, Middle Initial)</th>
                    
                </tr>
		
				
				
				
				
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
                                            <td style="width:120px; height: 17px; font-size: 13px;"><b> </b></td>
                                            <td style="width: 40px; font-size: 11px;"> </td>
                                            <td style="width: 40px; font-size: 11px;"> </td>
                                            <!-- CONTACT -->
                                            <td></td>
                                            <!-- Area -->
                                            <td></td>

                                            <td><center> </center></td>
                                            <!--AGE -->
                                            <td align="center" style="font-size: 11px; width: 30px;"> </td>
                                            <!-- REPRESENTATIVE -->
                                            <td style="width: 120px;"> </td>
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
					<td style="width:120px; height: 17px; font-size: 13px;"><b> {{strtoupper($list[$cnt]["lastName"])}}, {{strtoupper($list[$cnt]["firstName"])}} {{strtoupper($list[$cnt]["midName"])}} </b></td>

                    @if($list[$cnt]["icts_rsbsa"]=="0")
                        <td style="width: 60px; font-size: 11px; text-align: center;">N/A</td>
                    @else
                        <td style="width: 60px; font-size: 11px;">{{$list[$cnt]["icts_rsbsa"]}}</td>
                    @endif


                        

                       <td style="width: 60px; font-size: 11px;">{{$list[$cnt]["rsbsa_control_no"]}}</td>
                     
                    <!-- CONTACT -->

                    <?php
                        if(strlen($list[$cnt]["tel_number"]) < 10){
                            echo "<td></td>";
                        }elseif($list[$cnt]["tel_number"] == "00000000000"){
                             echo "<td></td>";
                        }   
                        else{
                            echo "<td>".$list[$cnt]["tel_number"]."</td>";
                        }


                    ?>


                    @if($list[$cnt]["da_area"] <= 0)
                        <td style="width: 60px;" align="center"> {{$list[$cnt]["actual_area"]}}</td>
                    @else
                        <td style="width: 60px;" align="center"> {{$list[$cnt]["da_area"]}}</td>
                    @endif

                    <td><center>{{strtoupper($list[$cnt]["sex"] == "Female" ? "F" : "M")}}</center></td>
                    <!--AGE -->
                    <td align="center" style="font-size: 11px; width: 30px;">{{$list[$cnt]["birthdate"] == "0000-00-00" ? "" : date('m/d/y', strtotime($list[$cnt]["birthdate"]))}}</td>
                    <!-- REPRESENTATIVE -->
                    <td style="text-align: center;">  {{strtoupper($list[$cnt]["mother_lname"])}}, {{strtoupper($list[$cnt]["mother_fname"])}} {{strtoupper($list[$cnt]["mother_mname"])}}</td>
				</tr>
					  {{ $cntIdentifier++}}
						  {{$itm++}}
                @else
                <tr>
                    <td style="width: 10px; height: 30px;">{{$itm}} </td>
                    <td style="width:120px; height: 17px; font-size: 13px;"><b> </b></td>
                    <td style="width: 40px; font-size: 11px;"> </td>
                    <td style="width: 40px; font-size: 11px;"> </td>
                    <!-- CONTACT -->
                    <td></td>
                    <!-- Area -->
                    <td></td>

                    <td><center> </center></td>
                    <!--AGE -->
                    <td align="center" style="font-size: 11px; width: 30px;"> </td>
                    <!-- REPRESENTATIVE -->
                    <td style="width: 120px;"> </td>
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
                                         <tr>
                                            <td style="width: 10px; height: 30px;">{{$itm}} </td>
                                            <td style="width:120px; height: 17px; font-size: 13px;"><b> </b></td>
                                            <td style="width: 40px; font-size: 11px;"> </td>
                                            <td style="width: 40px; font-size: 11px;"> </td>
                                            <!-- CONTACT -->
                                            <td></td>
                                            <!-- Area -->
                                            <td></td>

                                            <td><center> </center></td>
                                            <!--AGE -->
                                            <td align="center" style="font-size: 11px; width: 30px;"> </td>
                                            <!-- REPRESENTATIVE -->
                                            <td style="width: 120px;"> </td>
                                         </tr>
                              
								
									 <?php
								$itm++;
								 }
							 }
				 }
					?>
            <!--footer-->
      

            <tr>
     
                    <th colspan="{{$col_param}}">
                  

                    <div style="margin-top:0px;margin-left: 10px; width: 400px; ">
                        <span>Issued By: </span>
                        <div style="margin-left: 50px;">
                            <span>_______________________________________________</span><br>
                            <span style="margin-left: 3px; font-size: 10px;">Name, signature and position of authorized LGU Representative</span><br><br><br><br>
                           
                        </div>
                    </div>

                    <div style="position: fixed; right:200px; bottom:  125px;  width: 400px;">
                        <span>Noted By: </span>
                        <div style="margin-left: 50px;">
                            <span>__________________________________________________</span><br>
                            <span style="margin-left:5px; font-size: 10px;">Name and signature of RCEF Seed Regional/Provincial Coordinator</span>
                    
                        </div>
                    </div>





                   
                     
                    
                    </th>
            </tr>
 <div style="position: fixed; text-align: right; margin-right: 8px; width: 140px; bottom:130px;right:5px;">
                        <img src="{{ public_path('images/iso.jpg')}}" 
                            style="width: auto; height: 75px;">
                    </div>

   <div style="position: fixed; text-align: right; margin-right: 10px;  bottom:15px;right:0px;"> PhilRice RCEF FAR V3.3 Rev 00 Effectivity Date: 08 September 2021 </div>


            </table>
        
        <div class="page-break"></div>
    @endfor
    

   

</body>
</html>
    
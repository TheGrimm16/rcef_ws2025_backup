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
     <?php $page_count = ceil(count($list) / 15)?>
	 
    <?php $totalFarmer = count($list); ?>
    <?php $array_index = 1?>
    <?php $cnt = 0;
		  $itm = 1;
	?>
    
    <?php
   // dd($list);
        $brgy = $list[0]["barangay"];

        foreach ($list as $key => $value) {
           if($list[$key]["barangay"]!=$brgy){
            $brgy = $list[$key]["barangay"];
            $page_count++;
           }
        }
         $brgy = $list[0]["barangay"];
		// $page_count++;
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
					//$page_count +=4;
					$addBlankPage = 0;
					//echo $page_count . "<br>".$i;	
				}			
            }else{
				$wData = 1;
			}

			
			?>
     
		<table style="width:100%; ">
            <thead>
                <tr>
                    <th colspan="22" style="page-break-after: always;">
                        <img src="{{ public_path('images/da_philrice.png')}}" 
                            style="width: auto; height: 150px; margin-top:0px;margin-left: 90px; z-index: -50; position: absolute;">

                            <img src="{{ public_path('images/rcef_seed_program.png')}}" 
                            style="height: 150px; width: auto;margin-top:0px;margin-left: 960px; position: absolute;">

                        <div style="margin-top:30px;">
                            <center><span style="font-size: 20px;"> (e-Binhi Padala Phase II) <br> Farmer Acknowledgement Receipt <br> (Seeds and IEC Materials)</span></center>
                            <center><span style="font-size: 20px;">RCEF Seed Program</span></center><br>
                        </div>

                        <div style="margin-top:15px;margin-left: 10px;">
                            <span>Season Year: __<u>WET SEASON 2021</u>___</span><br>
                            <span>Drop-off Point (Pick up Location): <u>{{$dop}}</u> </span><br>
                            <span>RSBSA Code: Region_<u>{{$region_code}}</u>_, Province:_<u>{{$province_code}}</u>_, Municipality:_<u>{{$municipality_code}}_</u>_, Barangay:_<u>{{$brgy}}_</u>
							</span>
                        </div>
                        <br>
                    </th>
                </tr>
				
				@if($wData == 1)
				<tr>
                    <th rowspan="2" style=""></th>
                    <th style="" colspan="3"><center>FARMER NAME</center></th> 
                    <th style="" rowspan="2" align="center">RSBSA Farmer <br> Code</th>
                    <th rowspan="2"><center>Sex</center></th>
                   <!-- <th rowspan="2" width="50px"><center>Claim Code</center></th> -->
                   
                    <th colspan="2" rowspan="2"><center>Schedule</center></th>
                    <th rowspan="2" style="" align="center">Contact #</th>
                    <th style="" rowspan="2" align="center">Validated Area (ha)</th>
                    <th style="" rowspan="2" align="center">Seed Variety</th>
                    <th style="" rowspan="2"><center> No. of bags </center></th>
                    <th style="" colspan="2"><center> No. of IEC materials </center></th>
                    <th colspan="4"><center>Name of Authorized Representative </center></th>
                    <th rowspan="2" style=" font-size: 10px;" align="center">Signature of Claimant</th>
                    <th rowspan="2" colspan="3" style="" align="center"> Date Received <i style="font-weight: 1px; font-size: 10px;" > (mm/dd/yr) </i></th>
                    
				</tr>
                <tr>
                      
                    <th align="center" colspan="3" style="height: 20px; font-weight: 1px; font-style: italic; font-size: 10px; width: 200px;">(Last Name, First Name, Middle Name)</th>
                      
                    <th align="center">KPs</th>
                    <th align="center">Calendar</th>

                    <th align="center" colspan="4" style="font-weight: 1px; font-style: italic; font-size: 10px;">(Last, First, Initial)</th>
                   
                </tr>

				@else
				 <tr>
                    <th rowspan="2" style=""></th>
                    <th style="" colspan="3"><center>FARMER NAME</center></th> 
                    <th style="width: 120px;" rowspan="2" align="center">RSBSA Farmer <br> Code</th>
                    <th rowspan="2"><center>Sex</center></th>
                    <th><center>Claim Code</center></th>
                    <th style="margin: 0px; font-size: 10px; padding: 0px;"><center>PWD</center></th>
                    <th style="margin: 0px; font-size: 10px;"> <center>Member of IP</center></th>
                    
                    <th colspan="3"><center>Mother's Maiden Name</center></th>
                    <th rowspan="2" style="width:75px;">Contact #</th>
                    <th style="" rowspan="2">Area Planted (ha)</th>
                    <th style="" rowspan="2">Variety</th>
                    <th style="" rowspan="2"><center> No. of bags </center></th>
                    <th style="" colspan="2"><center> No. of IEC materials </center></th>
                    <th colspan="3"><center>Name of Authorized Representative </center></th>
                    <th rowspan="2" style=" font-size: 10px;" align="center">Signature of Claimant</th>
                    <th rowspan="2" align="center">Date Received <i style="font-weight: 1px; font-size: 10px;" > (mm/dd/yr) </i></th>
                </tr>
                <tr>
                    <th colspan="3" align="center" style="font-weight: 1px; font-style: italic; font-size: 10px; width: 220px;">(Last Name, First Name, Middle Name)</th>
                    <th align="center" style="font-weight: 1px; font-style: italic; font-size: 10px;">(mm/dd/yr)</th>
                    <th align="center" colspan="2" style="width: 80px;">(Y/N)</th>
                    <th align="center" colspan="3" style="font-weight: 1px; font-style: italic; font-size: 10px; width: 200px;">(Last Name, First Name, Middle Name)</th>
                    <th align="center">KPs</th>
                    <th align="center">Calendar</th>
                    <th align="center" colspan="3" style="font-weight: 1px; font-style: italic; font-size: 10px;">(Last, First, Initial)</th>
                    
                </tr>

                

				@endif
				


				
				
				
            </thead>
       
			  @for ($x = $cnt; $x < $totalFarmer; $x++)
                @if(!empty($list[$cnt]))
                <?php

                    if($brgy!=$list[$cnt]["barangay"]){
                           // $i++;
                            $brgy = $list[$cnt]["barangay"];
                            if($cntIdentifier < 15){
                                $r = 15 - $cntIdentifier ;
                                for($e=0;$e<$r;$e++){
                                    ?>
                                    <!-- EMPTY ROW -->
                                    <tr>
                                        <td style="width: 10%; height:18px;">{{$itm}} </td>
                                    <td colspan="3" style=""></td>
                                    <td style=""></td>
                                    <td><center></center></td>
                                    
                                    <td colspan="2" style=""></td>
                                    <td></td>
                                    <td><center></center></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td colspan="4"></td>
                                   
                                    <td></td>
                                    <td colspan="3"></td>
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
                 if($cntIdentifier == 15){
                       $breaker =1;
                        break;
                    }
                    ?>

				<tr>
					<td style="width: 10px; height: 22px;">{{$itm}} </td>
                    <?php
                        $name = $list[$cnt]["lastname"].", ". $list[$cnt]["firstname"]." ". $list[$cnt]["middname"];
                        $len = strlen($name);
                    ?>

                    @if($len<=28)
                    <td colspan="3" style="width:235px; height: 15px; font-size: 12px;"><b> {{strtoupper($list[$cnt]["lastname"])}}, {{strtoupper($list[$cnt]["firstname"])}} {{strtoupper($list[$cnt]["middname"])}} </b></td>
                    @else
                    <td colspan="3" style="width:235px; height: 15px; font-size: 10px;"><b> {{strtoupper($list[$cnt]["lastname"])}}, {{strtoupper($list[$cnt]["firstname"])}} {{strtoupper($list[$cnt]["middname"])}} </b></td>

                    @endif






					<td style="width: 120px; font-size: 11px;">{{$list[$cnt]["rsbsa_control_no"]}}</td>
					<td><center>@if(strtoupper($list[$cnt]["sex"])=="MALE" || strtoupper($list[$cnt]["sex"])=="M") {{"M"}} @elseif(strtoupper($list[$cnt]["sex"])=="FEMALE" || strtoupper($list[$cnt]["sex"])=="F"){{"F"}} @else {{""}} @endif
                    </center></td>
               

                    <td colspan="2" style="width:90px; font-size: 11px;" align="center"> 
                        {{date("M d", strtotime($list[$cnt]["schedule_start"])) }} - {{date("d, Y", strtotime($list[$cnt]["schedule_end"])) }} 
                    </td>
					<td style="width:70px; font-size: 12px;" align="center">
                        <?php
                            if(strlen($list[$cnt]["contact_no"])<11){
                                echo "0".$list[$cnt]["contact_no"];
                            }else{
                                echo $list[$cnt]["contact_no"];
                            }
                        ?>



                     </td>
					<td><center>{{$list[$cnt]["area"]}}</center></td>
					<td style="width: 150px;"> </td>
					<td align="center">{{$list[$cnt]["bags"]}}</td>    
					<td style="width: 40px;"> </td>
					<td style="width: 40px;"> </td>
					
					<td colspan="4" style="width: 140px"> </td>
                    <td style="width: 70px;"> </td>
                    <td colspan="3" style="width: 70px;"> </td>
				</tr>
					  {{ $cntIdentifier++}}
						  {{$itm++}}
                @else
                <tr>
               <td style="width: 10%; height:18px;">{{$itm}} </td>
                                      <td style="width: 10%; height:18px;">{{$itm}} </td>
                                        <td style="width: 10%; height:18px;">{{$itm}} </td>
                                    <td colspan="3" style=""></td>
                                    <td style=""></td>
                                    <td><center></center></td>
                                    
                                    <td colspan="2" style=""></td>
                                    <td></td>
                                    <td><center></center></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td colspan="4"></td>
                                   
                                    <td></td>
                                    <td colspan="3"></td>
                </tr>
                @endif
                   
                {{$cnt++}}
            @endfor
					  <?php 
				 if($breaker != 1){
					 if($cntIdentifier < 15){
								 $r = 15 - $cntIdentifier ;
								 for($e=0;$e<$r;$e++){
									 ?>
									 <!-- EMPTY ROW -->
								 <tr>
                					<td style="width: 10%; height:18px;">{{$itm}} </td>
                                    <td colspan="3" style=""></td>
                                    <td style=""></td>
                                    <td><center></center></td>
                                    
                                    <td colspan="2" style=""></td>
                                    <td></td>
                                    <td><center></center></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td colspan="4"></td>
                                   
                                    <td></td>
                                    <td colspan="3"></td>
								 </tr>
									 <?php
								$itm++;
								 }
							 }
				 }
					?>
            <!--footer-->
            <tr>

                <th colspan="22">
                   
                    <div style="margin-top:0px;margin-left: 10px;">
                        <span>Issued By: </span><br><br>
                        <div style="margin-left: 50px;">
                            <span>__________________________________________</span><br>
                            <span style="margin-left: 0px; font-size: 10px;">Name and signature of authorized <br> Representative of Seed Grower's Cooperative / Association</span><br><br>
                            
                            
                        </div>
                    </div>

                    <div style="margin-left: 550px;margin-top:-115px;">
                        <span>Noted By: </span><br><br>
                        <div style="margin-left: 50px;">
                            <span>__________________________________________________</span><br>
                            <span style="margin-left:0px; font-size: 10px;">Name and signature of RCEF Seeds Regional/Provincial Coordinator</span><br><br><br><br>
                        
                        </div>
                    </div>
                </th>
            </tr>


            </table>
    
        <div class="page-break"></div>
    @endfor
    

   

</body>
</html>
    
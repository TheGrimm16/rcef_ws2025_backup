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
     $row_param = 18;
     $col_param = 15;
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

			?>
        
		<table style="width:100%; margin-right: 50px; margin-top: 15px;">
            <thead>
                <tr>
                        <th colspan="{{$col_param}}" style="page-break-after: always;">
                        <img src="{{ public_path('images/da_philrice.jpg')}}" 
                            style="width: auto; height: 110px; margin-top:15px;margin-left: 20px; z-index: -50; position: absolute;">

                            <img src="{{ public_path('images/rcef_extension.jpg')}}" 
                            style="height: 100px; width: auto;margin-top:25px;margin-left: 140px; position: absolute;">

                        <div style="margin-top:30px; width: 50%; margin-left: 260px; text-align:center;">
                            <left><span style="font-size: 20px;">(Binhi e-Padala) <br>Farmer Acknowledgement Receipt <br> (IEC Materials)</span></left> <br>
                           <!-- <left><span style="font-size: 20px;">RCEF Seed Program</span></left><br> -->
                         
                        </div>

                     <div style="position: absolute; border: 1px dotted black; top:30px; width: 25%; height: 110px; right:20px;">
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
                        <div style="position: fixed; text-align: right; margin-right: 10px;  top:0px;right:0px;"> FAR V4.6</div>
                        

                        <div style="margin-top: 30px;margin-left: 10px;">
                            <span>Year/Season: __<u>Wet Season 2024</u>___</span><br>
                            <span>Drop-off Point (City/Municipality, Province) __<u>{{strtoupper($municipality_name)}}, {{strtoupper($province_name)}}</u>__</span><br>
                            <span>RSBSA Code: Region_<u>{{$region_code}}</u>_, Province:_<u>{{$province_code}}</u>_, Municipality:_<u>{{$municipality_code}}</u>
							_, Barangay:_<u>{{$brgy}}</u>_
							
							</span>
                        </div>
                        
                    </th>
                </tr>
				
			
		  <tr>
                    <th rowspan="2" style=""></th>
                    <th style="width: 150px;" rowspan="2"><center>RSBSA No.</center></th> 
                    <th style="width: 260px;" rowspan="2"><center>NAME</center></th> 
                    <th style="width: 50px;" rowspan="2"><center>Gender</center></th> 
                    <th style="width: 50px;" rowspan="2"><center>Age</center></th> 
                    <th style="width: 80px;" rowspan="2"><center>Birthdate</center></th> 
                    <th style="width: 30px;" ><center>PWD</center></th> 
                    <th style="width: 30px;" ><center>IP</center></th> 
                    <th style="width: 30px; font-size:10px;" ><center>Data Sharing Consent</center></th> 


                    <th align="center" colspan="4"> IEC materials distributed during RCEF seed distribution </th>
                    <th rowspan="2" style="font-size: 10px; width: 80px;"><center>I-check kung ang magsasaka ay naka-attend sa <b>Technical Briefing</b></center></th>
                    <th rowspan="2" style=" font-size: 10px; width: 80px;" align="center">Signature of Claimant</th>
                
                </tr>
                <tr>
                    <th style="" colspan="3"><center>Yes:"<img src="{{ public_path('images/far_check.jpg')}}" style="height: 12px; width: auto;">"<br> No:"<img src="{{ public_path('images/far_cross.jpg')}}" style="height: 12px; width: auto;">"</center></th>
                    <th style="width: 85px;" align="center">2022<br>Tekno - Kalendaryo</th>
                    <th style="width: 85px;" align="center">Gabay sa <br>Makabagong <br> Pagpapalayan</th>
                    <th style="width: 85px;" align="center">Gabay sa <br>Pagsasabog-tanim </th>
                    <th style="width: 85px;" align="center">yunPALAYun<br>Handout</th>
                </tr>
        
                
                
                
				
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
									       <td style="width: 10px; ">{{$itm}} </td>
                                           <td style="width: 80px; height: 17px; font-size: 13px;"><b> </b></td>
                                            <td style="width:120px; height: 17px; font-size: 13px;"><b> </b></td>
                                            
                                            <td style="width: 40px; font-size: 11px;"> </td>
                                            <td style="width: 40px; font-size: 11px;"> </td>
                                            <td style="width: 40px; font-size: 11px;"> </td>
                                            <td style="width: 40px; font-size: 11px;"> </td>
                                            <td style="width: 40px; font-size: 11px;"> </td>
                                            <td style="width: 40px; font-size: 11px;"> </td>


                                            <td style="width: 40px; font-size: 11px;"> </td>
                                            <td style="width: 40px; font-size: 11px;"> </td>
                                            <!-- CONTACT -->
                                            <td></td>
                                            <!-- Area -->
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
					<td style="width: 10px;">{{$itm}} </td>
                    
      
                        <td style="width: 60px; font-size: 11px;">{{$list[$cnt]["rsbsa_control_no"]}}</td>
                


					<td style="width:120px; height: 17px; font-size: 13px;"><b> {{strtoupper($list[$cnt]["lastname"])}}, {{strtoupper($list[$cnt]["firstname"])}} {{strtoupper($list[$cnt]["middname"])}} </b></td>

                    <td style="width: 50px; font-size: 11px; text-align: center;">
                         <?php
                            if($list[$cnt]["sex"] != "" || $list[$cnt]["sex"] != NULL){
                                if(strtoupper(substr($list[$cnt]["sex"], 0,1)) == "M"){
                                    echo "M";
                                }else{
                                    echo "F";
                                }
                            }
                        ?>

                        
                    </td>
                    <?php 
                            echo "<td style='width: 50px;'> </td>";
                            echo "<td style='width: 50px;'> </td>";
                        
                    ?>


                    <td style="width: 50px; font-size: 11px;"> </td>
                    <td style="width: 50px; font-size: 11px;"> </td>
                    <td style="width: 40px; font-size: 11px;"> </td>
                                            

					<td style="width: 40px; font-size: 11px;"></td>
					<td style="width: 40px; font-size: 11px;"></td>
                    <!-- CONTACT -->

                    <td></td>
                    <td></td>
                    <td></td>
                     <td></td>


				</tr>
					  {{ $cntIdentifier++}}
						  {{$itm++}}
                @else
                <tr>
                    <td style="width: 10px; height: 30px;">{{$itm}} </td>
                    <td style="width: 80px; height: 17px; font-size: 13px;"><b> </b></td>
                    <td style="width:120px; height: 17px; font-size: 13px;"><b> </b></td>

                    <td style="width: 40px; font-size: 11px;"> </td>
                    <td style="width: 40px; font-size: 11px;"> </td>
                    <td style="width: 40px; font-size: 11px;"> </td>
                    <td style="width: 40px; font-size: 11px;"> </td>
                    <td style="width: 40px; font-size: 11px;"> </td>
                    <td style="width: 40px; font-size: 11px;"> </td>
                                            


                    <td style="width: 40px; font-size: 11px;"> </td>
                    <td style="width: 40px; font-size: 11px;"> </td>
                    <!-- CONTACT -->
                    <td></td>
                    <!-- Area -->
                    <td></td>
                    <td></td>
                     <td></td>

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
                                            <td style="width: 10px; ">{{$itm}} </td>
                                            <td style="width: 80px; height: 17px; font-size: 13px;"><b> </b></td>
                                            <td style="width:120px; height: 17px; font-size: 13px;"><b> </b></td>

                                            <td style="width: 40px; font-size: 11px;"> </td>
                                            <td style="width: 40px; font-size: 11px;"> </td>
                                            <td style="width: 40px; font-size: 11px;"> </td>
                                            <td style="width: 40px; font-size: 11px;"> </td>
                                            <td style="width: 40px; font-size: 11px;"> </td>
                                            <td style="width: 40px; font-size: 11px;"> </td>
                                            
                                            <td style="width: 40px; font-size: 11px;"> </td>
                                            <td style="width: 40px; font-size: 11px;"> </td>
                                            <!-- CONTACT -->
                                            <td></td>
                                            <!-- Area -->
                                            <td></td>
                                            <td></td>
                                             <td></td>
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
                   

                    <div style="margin-top:0px;margin-left: 10px;">
                        <br><span>Issued By: </span>
                        <div style="margin-left: 50px;">
                            <span>_______________________________________________</span><br>
                            <span style="margin-left: 3px; font-size: 10px;">Name, signature and position of authorized LGU Representative</span><br>
                           
                        </div>
                    </div>

                    <div style="margin-left: 600px;margin-top:-125px;">
                        <br> <span>Noted By: </span>
                        <div style="margin-left: 50px;">
                            <span>__________________________________________________</span><br>
                            <span style="margin-left:5px; font-size: 10px;">Name and signature of RCEF Seed Regional/Provincial Coordinator</span><br><br><br>
                    
                        </div>
                    </div>
                    <div style="position: fixed; text-align: right; margin-right: 8px;  bottom:110px;right:10px;">
                        <img src="{{ public_path('images/iso.jpg')}}" 
                            style="width: auto; height: 75px;">
                    </div>
                      <div style="position: fixed; text-align: right; margin-right: 10px;  bottom:15px;right:0px;">    PhilRice RCEF FAR V4.6 Rev 00 Effectivity Date: 04 April 2022 </div>
                </th>
            </tr>

            </table>
        
        <div class="page-break"></div>
    @endfor
    


</body>
</html>
    
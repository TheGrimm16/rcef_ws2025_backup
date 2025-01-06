<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

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
            padding: 1px;
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
    <?php $array_index = 1?>
    <?php $cnt = 0?>
    
    @for ($i = 1; $i <= $page_count; $i++)
        <table style="width:100%">
            <thead>
                <tr>
                       <th colspan="23" style="page-break-after: always;">
                        <img src="{{ public_path('images/da_philrice.png')}}" 
                            style="width: auto; height: 150px; margin-top:15px;margin-left: 10px; z-index: -50; position: absolute;">

                            <img src="{{ public_path('images/rcef_seed_program.png')}}" 
                            style="height: 150px; width: auto;margin-top:15px;margin-left: 1170px; position: absolute;">

                        <div style="margin-top:50px;">
                            <center><span style="font-size: 20px;">Farmer Acknowledgement Receipt <br> (Seeds and IEC Materials)</span></center>
                            <center><span style="font-size: 20px;">RCEF Seed Program</span></center><br><br>
                        </div><br>

                        <div style="margin-top:15px;margin-left: 10px;">
                            <span>Season Year: __<u>WET SEASON 2021</u>___</span><br>
                            <span>Drop-off Point (Municipality, Province): _______________________</span><br>
                            <span>RSBSA Code: Region_______, Province:_______, Municipality:_______
                            , Barangay:_______
                            
                            </span>
                        </div>
                        
                        <br>
                    </th>
                </tr>
                 <tr>
                    <th rowspan="2" style=""></th>
                    <th style="" colspan="3"><center>FARMER NAME</center></th> 
                    <th style="width: 120px;" rowspan="2" align="center">RSBSA Farmer <br> Code</th>
                    <th rowspan="2"><center>Sex</center></th>
                    <th><center>Birthday</center></th>
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
            </thead>
       

            @for ($x = $cnt; $x < ($i * 15); $x++)
              <?php $itm = $x+1; ?>
                <tr>
                   <td style="width: 10%; height:18px;">{{$itm}} </td>
                    <td colspan="3" style=""></td>
                    <td style=""></td>
                    <td><center></center></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td colspan="3" style=""></td>
                    <td></td>
                    <td><center></center></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td colspan="3"></td>
                   
                    <td></td>
                    <td></td>
                </tr>
                    
                {{$cnt++}}
            @endfor

            <tr>
                <th colspan="23">
                    <div style="margin-top:0px;margin-left: 10px;">
                        <span>Issued By: </span><br><br>
                        <div style="margin-left: 50px;">
                            <span>____________________________________________</span><br>
                            <span style="margin-left: 10px; font-size: 10px;">Name and signature of authorized LGU Representative</span><br><br>
                            <span>____________________________________________</span><br>
                            <span style="margin-left:50px; font-size: 10px;">Position of LGU Representative</span>
                            
                        </div>
                    </div>

                    <div style="margin-left: 550px;margin-top:-115px;">
                        <span>Noted By: </span><br><br>
                        <div style="margin-left: 50px;">
                            <span>____________________________________________</span><br>
                            <span style="margin-left:-10px; font-size: 10px;">Name and signature of RCEF Seeds Regional/Provincial Coordinator</span><br><br><br><br>
                    
                        </div>
                    </div>
                    <br><br>
                </th>
            </tr>

            </table>
        
        <div class="page-break"></div>
    @endfor
    

   

</body>
</html>
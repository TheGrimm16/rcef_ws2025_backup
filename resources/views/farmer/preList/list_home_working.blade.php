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
            font-size: 10px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <?php $page_count = ceil(count($list) / 15)?>
    <?php $array_index = 1?>
    <?php $cnt = 0?>
    <?php $row_cnt = 0?>
    
    @for ($i = 1; $i <= $page_count; $i++)
        <table style="width:100%">
            <thead>
                <tr>
                    <th colspan="17" style="page-break-after: always;">
                        sadasdas
                    </th>
                </tr>
                <tr>
                    <th colspan="4"><center>FARMER NANME <br>(Last Name, First Name, Middle Name</center></th>
                    <th style="width:100px;">Farmer Code</th>
                    <th>Sex</th>
                    <th>Birthday</th>
                    <th>QR Code</th>
                    <th colspan="3"><center>Mother's Maiden Name <br>(Last Name, First Name, Middle Name)</center></th>
                    <th style="width:70px;">Contact #</th>
                    <th>Area Planted</th>
                    <th>Variety</th>
                    <th>No. of bags</th>
                    <th>Name of Authorized Representative</th>
                    <th>Signature of Claimant</th>
                </tr>
            </thead>
       

            @for ($x = $cnt; $x < ($i * 15); $x++)

                @if(!empty($list[$cnt]))
                    <tr>
                        <td style="width: 20px;">{{$cnt + 1}}). </td>
                        <td>{{strtoupper($list[$cnt]["fp_lastName"])}}</td>
                        <td>{{strtoupper($list[$cnt]["fp_firstName"])}}</td>
                        <td>{{strtoupper($list[$cnt]["fp_midName"])}}</td>
                        <td>{{strtoupper($list[$cnt]["fp_rsbsa_control_no"])}}</td>
                        <td>{{strtoupper($list[$cnt]["fp_sex"] == "Femal" ? "F" : "M")}}</td>
                        <td>{{$list[$cnt]["oi_birthdate"] == "0000-00-00" ? "" : $list[$cnt]["oi_birthdate"]}}</td>
                        <td></td>
                        <td>{{strtoupper($list[$cnt]["oi_mother_lname"])}}</td>
                        <td>{{strtoupper($list[$cnt]["oi_mother_fname"])}}</td>
                        <td>{{strtoupper($list[$cnt]["oi_mother_mname"])}}</td>
                        <td>{{$list[$cnt]["oi_phone"]}}</td>
                        <td>{{$list[$cnt]["fp_area"]}}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endif
                    

                {{$cnt++}}
            @endfor

            </table>
        
        <div class="page-break"></div>
    @endfor
    

   

</body>
</html>
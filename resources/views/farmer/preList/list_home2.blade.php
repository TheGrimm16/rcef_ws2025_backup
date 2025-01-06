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

        .footer {
            width: 100%;
            text-align: center;
            position: fixed;
        }

        thead:before, thead:after { display: none; }
        tbody:before, tbody:after { display: none; }
        tfoot:before, tfoot:after { display: none; }
    </style>
</head>
<body>
    
    <table>
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
        
        {{$cnt=1}}
        {{$row_cnt =1}}
        @foreach($list as $row)
            <tr>
                <td style="width: 20px;">{{$cnt}}). </td>
                <td>{{strtoupper($row["fp_lastName"])}}</td>
                <td>{{strtoupper($row["fp_firstName"])}}</td>
                <td>{{strtoupper($row["fp_midName"])}}</td>
                <td>{{strtoupper($row["fp_rsbsa_control_no"])}}</td>
                <td>{{strtoupper($row["fp_sex"] == "Femal" ? "F" : "M")}}</td>
                <td>{{$row["oi_birthdate"] == "0000-00-00" ? "" : $row["oi_birthdate"]}}</td>
                <td></td>
                <td>{{strtoupper($row["oi_mother_lname"])}}</td>
                <td>{{strtoupper($row["oi_mother_fname"])}}</td>
                <td>{{strtoupper($row["oi_mother_mname"])}}</td>
                <td>{{$row["oi_phone"]}}</td>
                <td>{{$row["fp_area"]}}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>

            {{++$row_cnt}}
            {{++$cnt}}
        @endforeach

        <tfoot>
            <tr>
                <td>Sum</td>
                <td>$180</td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
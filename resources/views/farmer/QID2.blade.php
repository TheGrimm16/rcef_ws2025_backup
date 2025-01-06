<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Farmer ID</title>
    <style>
        .pdf_page_wrapper{
            height: 100vh;
            background-color: green;
        }

        /* set PDF margins to 0 */
        @page { margin: 0px; }
        /* set PDF margins to 0 */

        .block_div{
            background-color: white;
            width: 260px;
            height: 300px;
            float: left;
            border: 1px solid black;
        }

        table td{
            border: 1px solid black;
            width: 269px;
            height: 378px;
            background-image:url({{url('images/logo_wm.png')}})
        }

        table tr {
            page-break-inside: avoid;
        }
        table tr td {
        page-break-inside: avoid;
        }
    </style>
</head>
<body style=" padding: 10px;">
    <table>
        {{$cnt=0}}

        <tr>
        @for ($i = $start_count; $i <= $end_count; $i++)

            {{$qr_code = "R".$region_code.sprintf("%'06d", $i)}}

            @if($cnt!=4)
                <td>
                    <img src="{{ public_path('images/plogo.jpg')}}" style="height: 120px;width: 120px;margin-top:10px;margin-left:5px" alt="">
                    <img style="margin-top:10px;margin-left:5px;" class="qr_class" src="data:image/png;base64, {{ base64_encode(QrCode::format('png')->backgroundColor(240, 240, 240)->color(83, 125, 82)->size(120)->margin(0)->generate("$qr_code")) }} ">
                    <div style="margin-top:-20px;margin-left:150px;"><strong>{{ $qr_code }}</strong></div>

                    <img src="{{ public_path('images/logo_wm.png')}}" alt="" style="position: absolute;z-index:0;margin-top:-80px;opacity:0.80;">
                    <div style="margin-left:10px;margin-top:20px;z-index:1">
                        <div style="padding-bottom:15px;">RSBSA #: _____________________</div> 
                        <div style="padding-bottom:15px;">VARIETY: ____________________</div>
                        <div style="padding-bottom:15px;">BAGS RECEIVED: _____________</div>
                        <div>AREA (HA): ___________________</div>
                    </div><br>

                    <div style="margin-top:-15px;">
                        <div style="position:absolute;">
                            <div style="margin-left:10px;margin-top:25px;">_______________</div>
                            <div style="margin-left:15px;">RELEASED BY:</div>
                        </div>
                        <img src="{{ public_path('images/rlogo2.png')}}" style="height: 70px;width: 180px;margin-top:15px;margin-left:105px;">
                    </div>
                </td>
                {{++$cnt}}
            @else
                </tr>
                <tr>

                    <td>
                        <img src="{{ public_path('images/plogo.jpg')}}" style="height: 120px;width: 120px;margin-top:10px;margin-left:5px" alt="">
                        <img style="margin-top:10px;margin-left:5px;" class="qr_class" src="data:image/png;base64, {{ base64_encode(QrCode::format('png')->backgroundColor(240, 240, 240)->color(83, 125, 82)->size(120)->margin(0)->generate("$qr_code")) }} ">
                        <div style="margin-top:-20px;margin-left:150px;"><strong>{{ $qr_code }}</strong></div>
    
                        <img src="{{ public_path('images/logo_wm.png')}}" alt="" style="position: absolute;z-index:0;margin-top:-80px;opacity:0.80;">
                        <div style="margin-left:10px;margin-top:20px;z-index:1">
                            <div style="padding-bottom:15px;">RSBSA #: _____________________</div> 
                            <div style="padding-bottom:15px;">VARIETY: ____________________</div>
                            <div style="padding-bottom:15px;">BAGS RECEIVED: _____________</div>
                            <div>AREA (HA): ___________________</div>
                        </div><br>
    
                        <div style="margin-top:-15px;">
                            <div style="position:absolute;">
                                <div style="margin-left:10px;margin-top:25px;">_______________</div>
                                <div style="margin-left:15px;">RELEASED BY:</div>
                            </div>
                            <img src="{{ public_path('images/rlogo2.png')}}" style="height: 70px;width: 180px;margin-top:15px;margin-left:105px;">
                        </div>
                    </td>
                
                
                {{$cnt=1}}
            @endif    
        @endfor
        </tr>
        <!--
        <tr>
            
            <td>2</td>
            <td>3</td>
            <td>4</td>
        </tr>-->
    </table>
</body>
</html>

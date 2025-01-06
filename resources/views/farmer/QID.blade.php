<!doctype html>
<head>
    <title>Farmer ID</title>
    <style>
        /* set PDF margins to 0 */
        @page { margin: 0px; }
        /* set PDF margins to 0 */

        table td{
            border: 1px solid black;
            width: 269px;
            height: 378px;
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
        <?php $cnt=0; ?>

        <tr>
        @for ($i = $start_count; $i <= $end_count; $i++)

            <?php $qr_code = "R".$region_code.sprintf("%'06d", $i); ?>

            @if($cnt!=4)
                <td>
                    <img src="{{ public_path('images/plogo.jpg')}}" style="height: 110px;width: 110px;margin-top:15px;margin-left:5px;z-index:1" alt="">
                    <img style="margin-top:15px;margin-left:20px;z-index:1" class="qr_class" src="data:image/jpg;base64, {{ base64_encode(QrCode::format('png')->backgroundColor(240, 240, 240)->color(83, 125, 82)->size(110)->margin(0)->generate("$qr_code")) }} ">
                    <div style="margin-top:-15px;margin-left:150px;z-index:1"><strong>{{ $qr_code }}</strong></div>

                    <img src="{{ public_path('images/logo_wm.jpg')}}" alt="" style="position: absolute;z-index:0;margin-top:-100px;margin-left:20px;">
                    <div style="margin-left:10px;margin-top:0px;z-index:1">
                        <div style="padding-bottom:10px;font-size:15px;">NAME: __________________________</div> 
                        <div style="padding-bottom:10px;font-size:15px;">RSBSA #: ________________________</div>
                        <div style="padding-bottom:10px;font-size:15px;">PHONE #: _______________________</div> 
                        <div style="padding-bottom:10px;font-size:15px;">VARIETY: _______________________</div>
                        <div style="padding-bottom:10px;font-size:15px;">BAGS RECEIVED: ________________</div>
                        <div style="font-size:15px;">AREA (HA): ______________________</div>
                    </div><br>

                    <div style="margin-top:-30px;z-index:1">
                        <div style="position:absolute;">
                            <div style="margin-left:10px;margin-top:25px;">_______________</div>
                            <div style="margin-left:15px;">RELEASED BY</div>
                        </div>
                        <img src="{{ public_path('images/rlogo2.jpg')}}" style="height: 70px;width: 160px;margin-top:15px;margin-left:110px;">
                    </div>
                </td>
                <?php ++$cnt; ?>
            @else
                </tr>
                <tr>

                    <td>
                        <img src="{{ public_path('images/plogo.jpg')}}" style="height: 110px;width: 110px;margin-top:15px;margin-left:5px;z-index:1" alt="">
                        <img style="margin-top:15px;margin-left:20px;z-index:1" class="qr_class" src="data:image/jpg;base64, {{ base64_encode(QrCode::format('png')->backgroundColor(240, 240, 240)->color(83, 125, 82)->size(110)->margin(0)->generate("$qr_code")) }} ">
                        <div style="margin-top:-15px;margin-left:150px;z-index:1"><strong>{{ $qr_code }}</strong></div>
    
                        <img src="{{ public_path('images/logo_wm.jpg')}}" alt="" style="position: absolute;z-index:0;margin-top:-100px;margin-left:20px;">
                        <div style="margin-left:10px;margin-top:0px;z-index:1">
                            <div style="padding-bottom:10px;font-size:15px;">NAME: __________________________</div> 
                            <div style="padding-bottom:10px;font-size:15px;">RSBSA #: ________________________</div>
                            <div style="padding-bottom:10px;font-size:15px;">PHONE #: _______________________</div> 
                            <div style="padding-bottom:10px;font-size:15px;">VARIETY: _______________________</div>
                            <div style="padding-bottom:10px;font-size:15px;">BAGS RECEIVED: ________________</div>
                            <div style="font-size:15px;">AREA (HA): ______________________</div>
                        </div><br>
    
                        <div style="margin-top:-30px;z-index:1">
                            <div style="position:absolute;">
                                <div style="margin-left:10px;margin-top:25px;">_______________</div>
                                <div style="margin-left:15px;">RELEASED BY</div>
                            </div>
                            <img src="{{ public_path('images/rlogo2.jpg')}}" style="height: 70px;width: 160px;margin-top:15px;margin-left:110px;">
                        </div>
                    </td>
                
                
                <?php $cnt=1; ?>
            @endif    
        @endfor
        </tr>
    </table>
</body>
</html>

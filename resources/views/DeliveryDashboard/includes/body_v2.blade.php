<table class="header-info" cellspacing="0">
    <tr>
        <td class="header-titles" style="width:110px">
        @if ($seedType ==='Regular'|| $seedType === 'Binhi e-Padala')
            Code for CS
        @elseif ($seedType === 'NRP')
            Code for NRP
        @elseif ($seedType === 'Good Quality Seeds')
            Code for GQS
        @else
            <!-- Optional: You can put a default value or leave it empty if needed -->
        @endif
        </td>
        <td class="header-titles" style="width:230px">Item Description</td>
        <td class="header-titles" style="width:60px;">Unit</td>
        <td class="header-titles" style="width:40px">Qty</td>
        <td class="header-titles" style="width:70px">Cost</td>
        <td class="header-titles" style="width:70px">Amount</td>
        <td class="header-titles" style="width:80px">MOA No.</td>
    </tr>
</table>
<table class="header-info" cellspacing="0" style="margin-top:3px;">
    <!--by variety -->
    <tr>
        <td class="header-data" style="width:110px">
        @if ($seedType ==='Regular'|| $seedType === 'Binhi e-Padala')
            CS SEED
        @elseif ($seedType === 'NRP')
            SEED RESERVE
        @elseif ($seedType === 'Good Quality Seeds')
            GQS SEED
        @else
            <!-- Optional: You can put a default value or leave it empty if needed -->
        @endif
        </td>
        <td class="header-data" style="width:230px">
            <div class="">Variety 1:</div>
        </td>
        <td class="header-data" style="width:60px;">20kg/Bag</td>
        <td class="header-data" style="width:40px">&nbsp;</td>
        <td class="header-data" style="width:70px">&nbsp;</td>
        <td class="header-data" style="width:70px">&nbsp;</td>
        <td class="header-data" style="width:80px">{{ $MOA }}</td>
    </tr>
    <tr>
        <td class="header-data" style="width:110px"></td>
        <td class="header-data" style="width:230px">
            <div class="">Variety 2:</div>
        </td>
        <td class="header-data" style="width:60px;">20kg/Bag</td>
        <td class="header-data" style="width:40px">&nbsp;</td>
        <td class="header-data" style="width:70px">&nbsp;</td>
        <td class="header-data" style="width:70px">&nbsp;</td>
        <td class="header-data" style="width:80px"></td>
    </tr>
    <tr>
        <td class="header-data" style="width:110px"></td>
        <td class="header-data" style="width:230px">
            <div class="">Variety 3:</div>
        </td>
        <td class="header-data" style="width:60px;">20kg/Bag</td>
        <td class="header-data" style="width:40px">&nbsp;</td>
        <td class="header-data" style="width:70px">&nbsp;</td>
        <td class="header-data" style="width:70px">&nbsp;</td>
        <td class="header-data" style="width:80px"></td>
    </tr>
    <tr>
        <td class="header-data" style="width:110px"></td>
        <td class="header-data" style="width:230px">
            <div class="">Variety 4:</div>
        </td>
        <td class="header-data" style="width:60px;">20kg/Bag</td>
        <td class="header-data" style="width:40px">&nbsp;</td>
        <td class="header-data" style="width:70px">&nbsp;</td>
        <td class="header-data" style="width:70px">&nbsp;</td>
        <td class="header-data" style="width:80px"></td>
    </tr>
    <tr>
        <td class="header-data" style="width:110px"></td>
        <td class="header-data" style="width:230px">
            <div class="">Variety 5:</div>
        </td>
        <td class="header-data" style="width:60px;">20kg/Bag</td>
        <td class="header-data" style="width:40px">&nbsp;</td>
        <td class="header-data" style="width:70px">&nbsp;</td>
        <td class="header-data" style="width:70px">&nbsp;</td>
        <td class="header-data" style="width:80px"></td>
    </tr>
    <tr>
        <td class="header-data" style="width:110px"></td>
        <td class="header-data" style="width:230px">
            <div class="">Variety 6:</div>
        </td>
        <td class="header-data" style="width:60px;">20kg/Bag</td>
        <td class="header-data" style="width:40px">&nbsp;</td>
        <td class="header-data" style="width:70px">&nbsp;</td>
        <td class="header-data" style="width:70px">&nbsp;</td>
        <td class="header-data" style="width:80px"></td>
    </tr>
    <!-- by variety end -->
    <tr>
        <td class="header-titles" style="width:110px">Total</td>
        <td class="header-titles" style="width:250px"></td>
        <td class="header-titles" style="width:40px;"></td>
        <td class="header-titles" style="width:40px"></td>
        <td class="header-titles" style="width:70px"></td>
        <td class="header-titles" style="width:70px"></td>
        <td class="header-titles" style="width:80px"></td>
    </tr>
    <tr>
        <td class="header-titles">Purpose</td>
        <td class="header-data" colspan="6">
        @if ($seedType ==='Regular'|| $seedType === 'Binhi e-Padala')
            For the CS
        @elseif ($seedType === 'NRP')
            For the NRP
        @elseif ($seedType === 'Good Quality Seeds')
            For the GQS
        @else
            <!-- Optional: You can put a default value or leave it empty if needed -->
        @endif delivery in
            {{ $province }} , {{ $municipality }} , {{ $drop_off_point }}
        </div>
    </tr>
</table>
<table class="header-info" cellspacing="0" style="text-align:center;">
    <tr>
        <td class="header-titles" style="padding:10px">INSPECTION</td>
        <td class="header-titles" style="padding:10px">ACCEPTANCE</td>
    </tr>
    <tr>
        <td class="header-data" style="height:170px">
            <!-- <table style="width:100%">
                <tr>
                    <td style="text-align:center" colspan="2">
                        INSPECTED, verified and found OK<br/>
                        as to quantity and specifications.
                    </td>
                </tr>
                <tr>
                    <td style="height:150px" colspan="2"></td>
                </tr>
                <tr>
                    <td style="width: 70%">asdsadsad</td>
                    <td style="width: 25%">asdsad</td>
                </tr>
            </table> -->
            <div style="text-align:center;position:absolute;width: 100%; top: 0px;">
                INSPECTED, verified and found in order as to the<br/>
                quantity and specifications.
            </div>
            <div style="position:absolute; top:80px;width: 65%">
                <div style="width:90%;margin: 0 auto;">
                    <div style="border-bottom: black solid 1px; font-weight: bold">
                        &nbsp;
                    </div>
                    <div class="">
                        @php
                            $signatories = array(
                                "ABRA" => "",
                                "APAYAO" => "Rouella S. Melegrito",
                                "BENGUET" => "Kristine M. Paggao",
                                "IFUGAO" => "Kristine M. Paggao",
                                "KALINGA" => "Hiyasmin R. Mandac",
                                "MOUNTAIN PROVINCE" => "Hiyasmin R. Mandac",
                                "ISABELA" => "Kevin G. Reyes",
                                "CAGAYAN" => "Kevin G. Reyes",
                                "QUIRINO" => "Maria Aster Joy A. Garcia",
                                "NUEVA VIZCAYA" => "Maria Aster Joy A. Garcia"
                            );

                            $designation = array(
                                "ABRA" => "",
                                "APAYAO" => "Administrative Officer II",
                                "BENGUET" => "Accountant II",
                                "IFUGAO" => "Accountant II",
                                "KALINGA" => "Administrative Officer III",
                                "MOUNTAIN PROVINCE" => "Administrative Officer III",
                                "ISABELA" => "Administrative Officer III",
                                "CAGAYAN" => "Administrative Officer III",
                                "QUIRINO" => "Administrative Officer II",
                                "NUEVA VIZCAYA" => "Administrative Officer II"
                            );
                            $result_name = 'Name and Signature';
                            $result_desg = 'Designation and Office';

                            if (isset($signatories[$province])) {
                                $result_name = $signatories[$province];
                            } else {
                                $result_name = 'Name and Signature';
                            }

                            if (isset($designation[$province])) {
                                $result_desg = $designation[$province];
                            } else {
                                $result_desg = 'Designation and Office';
                            }

                        @endphp
                        
                        {{$result_name}}
                    </div>
                </div>
            </div>

            <div style="position:absolute; top:120px;width: 65%">
                <div style="width:90%;margin: 0 auto;">
                    <div style="border-bottom: black solid 1px; font-weight: bold">
                        &nbsp;
                    </div>
                    <div class="">
                        {{$result_desg}}
                    </div>
                </div>
            </div>


            <div style="position:absolute; top:80px;width: 35%;margin-left:65%">
                <div style="width:90%;margin: 0 auto;">
                    <div style="border-bottom: black solid 1px; font-weight: bold">
                        &nbsp;
                    </div>
                    <div class="">
                        Date Inspected <br> (mm/dd/yr)
                    </div>
                </div>
            </div>
        </td>
        <td class="header-data" style="height:170px">
            <div style="font-weight: bold;position:absolute;width: 100%;">
                Complete Delivery
            </div>
            <div style="position:absolute; top:80px;width: 65%">
                <div style="width:90%;margin: 0 auto;">
                    <div style="border-bottom: black solid 1px; font-weight: bold">
                        &nbsp;
                    </div>
                    <div class="">
                       Name and Signature
                    </div>
                </div>
            </div>
             <div style="position:absolute; top:120px;width: 65%">
                <div style="width:90%;margin: 0 auto;">
                    <div style="border-bottom: black solid 1px; font-weight: bold">
                        &nbsp;
                    </div>
                    <div class="">
                       Designation and Office
                    </div>
                </div>
            </div>
            <div style="position:absolute; top:80px;width: 35%;margin-left:65%">
                <div style="width:90%;margin: 0 auto;">
                    <div style="border-bottom: black solid 1px; font-weight: bold">
                        &nbsp;
                    </div>
                    <div class="">
                        Date Signed <br> (mm/dd/yr)
                    </div>


                </div>
            </div>
        </td>
    </tr>
</table>
<div style="font-weight:bold; padding:10px">
    CHECKLIST
</div>
<table>
    <tr>
        <td class="box">&nbsp;</td>
        <td class="check-data">Check DR VS Delivery Schedule</td>
        {{-- {{$sadas}} --}}
       
        {{-- @if(Auth::user()->username == "processor_2") --}}

        <td rowspan="8">
            <div style="position:fixed; left:58%; bottom:2.5%; height: 220px; width: 185px; vertical-align:middle; border-style:solid;">


            </div>


            <div style="position:fixed; left:58.2%; bottom:23.5%; text-align:center; width:185px;  font-size:12px; vertical-align:middle; border-bottom:solid; padding-bottom:5px; background-color:#b3b6b7">
                SCAN ME USING THE RSMS ALL-IN-ONE APP     
            </div>





            <div style="position:fixed; left:59%; bottom:19.5%; vertical-align:middle;">

                <img class="qr_class" src="data:image/jpg;base64, {{ base64_encode(QrCode::format('png')->size(240)->margin(0)->generate($IAR_no)) }}" style="height:170px;width:auto;"/>
                
          </div>
    
            {{-- <div style="font-size:22px;  position:fixed; left:62%; bottom:14%; width:20%; text-align:center; font-weight:bold; vertical-align:bottom; padding-top:15px; padding-bottom:4px;">
               <u>SCAN ME</u>
            </div>
        
            <div style="font-size:15px; position:fixed; left:64%; bottom:5%; width:50%; font-weight:bold; height:50px; vertical-align:bottom; padding-top:15px;">
                * Seed Payments <br>
                * Document Processing <br>
                *<font style="font-size:12px;"> RCEF-SSU Please use the <br> &nbsp; &nbsp; RSMS All-in-one App </font>
                
            </div> --}}

          
        </td>












        {{-- <td rowspan="8">
            <div style="position:fixed; left:47%; bottom:14%; vertical-align:middle;">

                <img class="qr_class" src="data:image/jpg;base64, {{ base64_encode(QrCode::format('png')->size(240)->margin(0)->generate($IAR_no)) }}" style="height:120px;width:auto;"/>
                
          </div>
    
            <div style="font-size:22px;  position:fixed; left:62%; bottom:14%; width:20%; text-align:center; font-weight:bold; vertical-align:bottom; padding-top:15px; padding-bottom:4px;">
               <u>SCAN ME</u>
            </div>
        
            <div style="font-size:15px; position:fixed; left:64%; bottom:5%; width:50%; font-weight:bold; height:50px; vertical-align:bottom; padding-top:15px;">
                * Seed Payments <br>
                * Document Processing <br>
                *<font style="font-size:12px;"> RCEF-SSU Please use the <br> &nbsp; &nbsp; RSMS All-in-one App </font>
                
            </div>

          
        </td> --}}
        {{-- @endif --}}
        {{-- ALLOW THIS WHEN APPROVED --}}
        {{--  --}}

    </tr>



    <tr>
        <td class="box">&nbsp;</td>
        <td class="check-data">Check RLA with DR</td>
    </tr>
  
    <tr>
        <td class="box">&nbsp;</td>
        <td class="check-data">Conduct Random weighing</td>
    </tr>
    <tr>
        <td class="box">&nbsp;</td>
        <td class="check-data">Count total delivery = Delivery schedule</td>
    </tr>
    <tr>
        <td class="box">&nbsp;</td>
        <td class="check-data">Filled-up IAR form</td>
    </tr>
    <tr>
        <td class="box">&nbsp;</td>
        <td class="check-data">Signed acknowledgement receipt</td>
    </tr>
    <tr>
        <td class="box">&nbsp;</td>
        <td class="check-data">Completely filled-up apps</td>
    </tr>
    <tr>
        <td class="box">&nbsp;</td>
        <td class="check-data">Sent app data to server (local/web)</td>
    </tr>

</table>

  
     
   
    <table> 
        <tr> 
            <td> <font style=" position: fixed; bottom: 0; text-align:left; font-size: 12px; font-style:italic; color: #808080;" >PhilRice RCEF Seed IAR Rev 01 Effectivity Date: 29 June 2021</font> 
            </td>
        
            <td> <div style="position: fixed; bottom: -20; right: -20; max-height: 48px;"></div> </td>
            <!-- <td> <img style="position: fixed; bottom: -20; right: -20; max-height: 48px;"  src="< ?php echo $_SERVER["DOCUMENT_ROOT"] . '/rcef_ws2021/public/images/socotec_logo_iar.jpg' ?>" /> </td> -->
        </tr>


    </table>



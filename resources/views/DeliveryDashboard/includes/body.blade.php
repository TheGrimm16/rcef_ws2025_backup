<table class="header-info" cellspacing="0">
    <tr>
        <td class="header-titles" style="width:110px">
        @if ($seedType ==='Regular' || $seedType === 'Binhi e-Padala')
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
        @if ($seedType ==='Regular' || $seedType === 'Binhi e-Padala')
            CS SEED
        @elseif ($seedType === 'NRP')
            SEED RESERVE
        @elseif ($seedType === 'Good Quality Seeds')
            GQS SEED
        @else
            <!-- Optional: You can put a default value or leave it empty if needed -->
        @endif/td>
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
        @if ($seedType ==='Regular' || $seedType === 'Binhi e-Padala')
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
        <td class="header-data" style="height:180px">
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
                INSPECTED, verified and found OK<br/>
                as to quantity and specifications.
            </div>
            <div style="position:absolute; top:140px;width: 65%">
                <div style="width:90%;margin: 0 auto;">
                    <div style="border-bottom: black solid 1px; font-weight: bold">
                        &nbsp;
                    </div>
                    <div class="">
                        &nbsp;
                    </div>
                </div>
            </div>
            <div style="position:absolute; top:140px;width: 35%;margin-left:65%">
                <div style="width:90%;margin: 0 auto;">
                    <div style="border-bottom: black solid 1px; font-weight: bold">
                        &nbsp;
                    </div>
                    <div class="">
                        Date Inspected
                    </div>
                </div>
            </div>
        </td>
        <td class="header-data" style="height:180px">
            <div style="font-weight: bold;position:absolute;width: 100%;">
                Complete Delivery
            </div>
            <div style="position:absolute; top:140px;width: 65%">
                <div style="width:90%;margin: 0 auto;">
                    <div style="border-bottom: black solid 1px; font-weight: bold">
                        &nbsp;
                    </div>
                    <div class="">
                        &nbsp;
                    </div>
                </div>
            </div>
            <div style="position:absolute; top:140px;width: 35%;margin-left:65%">
                <div style="width:90%;margin: 0 auto;">
                    <div style="border-bottom: black solid 1px; font-weight: bold">
                        &nbsp;
                    </div>
                    <div class="">
                        Date Signed
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
    </tr>
    <tr>
        <td class="box">&nbsp;</td>
        <td class="check-data">Check RLA with DR</td>
    </tr>
    <tr>
        <td class="box">&nbsp;</td>
        <td class="check-data">Conduct visual inspection</td>
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

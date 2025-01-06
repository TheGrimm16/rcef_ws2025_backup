<!DOCTYPE html>
<html lang="en" dir="ltr">
    <style type="text/css">
        * {
          font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
        }
        .logo{
            width: 150px;
            height: 40px;
        }
        .title{
            text-align: center;
            width: 450px;
        }
        .title-main{
            font-weight: bold;
            font-size: 25px;
        }
        .title-track{
            width: 120px;
        }
        .title-normal{
            font-size: 14px;
        }
        .small-text{
            font-size: 10px;
        }
        .text-right{
            text-align: right;
        }
        .pt-1{
            padding-top: 5px;
        }
        .header-info{
            width: 740px;

        }
        .header-titles{
            background-color: #B3B6B7;
            font-size: 12px;
            width: 80px;
            font-weight: bold;
            padding: 5px;
            border: black solid 1px;
        }
        .header-data{
            font-size: 12px;
            padding: 5px;
            border: black solid 1px;
        }
        .box{
            width: 20px;
            height: 20px;
            border: solid 3px black;
        }
        .check-data{
            padding-left: 10px;
        }
    </style>
    <body>
        <table>
            <td><img src="<?php echo $_SERVER["DOCUMENT_ROOT"] . '/rcef/public/images/logo.svg' ?>" alt="" class="logo"></td>
            <td class="title">
                <div class="title-main">Inspection and Acceptance Report</div>
                <div class="title-add">Philippine Rice Research Institute</div>
            </td>
            <td class="title-track">
                <div class="text-right">
                    <div class="small-text">
                         Tracking No. 
                    </div>
                    <div class="title-normal">
                        <!-- sample -->
                        {{-- OED19-0027 --}} &nbsp;
                    </div>
                </div>
                <div class="title-normal pt-1">
                    Page 1 of 1
                </div>
            </td>
        </table>
        <table class="header-info" cellspacing="0">
            <tr>
                <td class="header-titles">Supplier Address</td>
                <td class="header-data" style="width:400px">
                    <!-- coop name -->
                    {{ $CoopName }}<br/>
                    <!-- Drop off Points -->
                    {{ $coopAddress }}</td>
                <td class="header-titles">IAR No.</td>
                <td class="header-data" style="width:70px">{{ $IAR_no }}</td>
            </tr>
            <tr>
                <td class="header-titles">Office Division</td>
                <td class="header-data" style="width:400px">
                    RCEP PMO
                <td class="header-titles">Date</td>
                <td class="header-data" style="width:70px">{{ $Date }}</td>
            </tr>
            <tr>
                <td class="header-titles">DOCS</td>
                <td class="header-data" colspan="3">
                    <!-- Invoice No./Date: 911 / 2019-01-20 <br/>
                    Delivery Receipt/Date: n/a / 2019-01-20 -->
                </div>
            </tr>
        </table>
    </body>
</html>

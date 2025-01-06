<!DOCTYPE html>
<html lang="en" dir="ltr">
    <style type="text/css">
        * {
          font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
        }
        .logo{
            width: auto;
            height: 75px;
        }
        .title{
            text-align: center;
            width: 740px;
        }
        .title-main{
            padding-left: 30px;
            font-weight: bold;
            font-size: 20px;
             width: 500px;
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
            font-size: 11px;
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
            <tr>
            <td><img style="position: fixed; top:-15;"  src="<?php echo $_SERVER["DOCUMENT_ROOT"] . '/rcef_ds2024/public/images/da_philrice.jpg' ?>" alt="" class="logo"></td>
            <td class="title">
                <div class="title-main"> <center>INSPECTION AND ACCEPTANCE RECEIPT </center></div>
                <div class="title-add">RCEF Seed Program</div>
            </td>
            <td class="title-track" align="right">
               <img style="position: fixed; top:-15; right: -10; " src="<?php echo $_SERVER["DOCUMENT_ROOT"] . '/rcef_ds2024/public/images/rcef_seed_program.jpg' ?>" alt="" class="logo">
            </td>
            </tr>
        </table>
        <br>
        <table class="header-info" cellspacing="0">
            <tr>
                <td class="header-titles" style="width:110px;">Name of Seed Grower Coop/Ass’n</td>
                <td class="header-data" style="width:400px">
                    <!-- coop name -->
                    {{ $CoopName }}<br/>

                <td class="header-titles" rowspan="2">IAR No.</td>
                <td class="header-data" style="width:70px" rowspan="2">{{ $IAR_no }}</td>
            </tr>
            
            <tr>
                <td class="header-titles" style="width:110px">Address</td>
                <td class="header-data" style="width:400px">
                    {{ $coopAddress }}</td>
            </tr>

            <tr>
                <td class="header-titles">Office Division/ PhilRice Station </td>
                <td class="header-data" style="width:400px">
                    RCEP PMO
                <td class="header-titles">Date (mm/dd/yyyy)</td>
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

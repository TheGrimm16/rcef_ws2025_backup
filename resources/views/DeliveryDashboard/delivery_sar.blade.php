<!DOCTYPE html>
<html lang="en" dir="ltr">
    <style type="text/css">
        * {
          font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
        }
        .title{
            font-size: 18px;
            width: 100%;
            text-align: center;
            margin-top: 30px;
            margin-bottom: 40px;
        }
        .header-title{
            font-weight: bold;
            letter-spacing: 2px;
        }
        .header-data2{
            letter-spacing: 1px;
        }
        .table-head{
            border: black 1px solid;
            text-align: center;
            font-weight: bold;
            padding: 5px;
        }
        .table-data{
            border: black 1px solid;
            text-align: left;
            padding: 5px;
        }
        .c1{
            width: 35%
        }
        .c2{
            width: 35%
        }
        .c3{
            width: 30%;
            text-align: right;
            padding-right: 10px;
        }
		.ds-logo-right{
           position: absolute;
           top: -10px; 
           right: 30px;
        }
        .ds-logo-left{
            position: absolute;
            top: -10px; 
            left: 30px;
        }
        .tbldata{
            padding-top:0;
            padding-bottom:0;
        }

        .logo1{
            width: 100px;
            height: auto;
        }
    </style>
    <body>
    <div class="ds-logo-left"><img 
        src="<?php echo $_SERVER["DOCUMENT_ROOT"] . '/rcef_ds2024/public/images/da_philrice.jpg' ?>" 
        alt="" class="logo1">
    </div>
    <div class="ds-logo-right"><img 
        src="<?php echo $_SERVER["DOCUMENT_ROOT"] . '/rcef_ds2024/public/images/rcef_seed_program.jpg' ?>" 
        alt="" class="logo1">
    </div>
        <div class="title" >
            <b> <font size="15">  SEED ACKNOWLEDGEMENT RECEIPT </font> </b> <br>
            RCEF Seed Program  
            <div class="col-md-12" style="margin-top:20px;">
                <label class="col-md-6" style="border:2px solid; ">&nbsp;&nbsp;&nbsp;&nbsp;</label> LGU  &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;
                <label class="col-md-6" style="border:2px solid;">&nbsp;&nbsp;&nbsp;&nbsp;</label> PhilRice 
            </div>

        </div>
      

        <table style="border:1px solid;"width="100%" cellspacing="0" >
            <tr>
                <td align="center" style="border:1px solid; width:30%;"> Name of Receiving Entity: </td>
                <td align="center" style="border:1px solid; width:40%; font-size:13px;"> {{$delivery[0]->municipality}}, {{$delivery[0]->province}}</td>
                <td align="center" style="border:1px solid;width:15%;"> Date <br> (mm/dd/yr)</td>
                <td align="center" style="border:1px solid;width:15%;"> </td>
            </tr>

        </table>





        <table cellspacing="0" style="width:100%;margin-top:20px;">
            <tr>
                 <td width="25%" align="center" class="table-head c1">Lab/Lot No.</td>
                <td width="25%" align="center" class="table-head c2">Variety</td>
                <td width="25%" align="center" class="table-head c1">Quantity <br> (no. of bags) </td>
                <td width="25%" align="center" class="table-head c1">Remarks</td>
            </tr>
        
        
            <?php 
            $count = 0;
    ?>
    @foreach($delivery as $key => $dv)
    <tr>
        <td class="table-data tbldata" style="font-size:12px;" > {{$dv->seedTag}}</td>
        <td class="table-data tbldata" style="font-size:12px; ">{{$dv->seedVariety}}</td>
        <td class="table-data tbldata">&nbsp;</td>
        <td class="table-data tbldata">&nbsp;</td>
        
    </tr>
        <?php $count++; ?>
    @endforeach
        @for($count; $count < 14; $count++)
        <tr>
        <td class="table-data tbldata">&nbsp;</td>
        <td class="table-data tbldata">&nbsp;</td>
        <td class="table-data tbldata">&nbsp;</td>
        <td class="table-data tbldata">&nbsp;</td>

        </tr>
        
        @endfor
        
        
        </table>
    


        
        <div style="margin-top: 40px;"> 
            The undersigned hereby acknowledge receipt of seeds described above for temporary safekeeping until distribution and/or full retrieval.

        </div>



        <table cellspacing="0" border="1" style="width:100%; margin-top:40px; ">
            <tr>
                <td colspan="4" align="center"> <b> Received By </b> </td>
                <td colspan="4" align="center"><b> Received From </b></td>
            </tr>

            <tr>
                <td colspan="3" style="border-right: 0; border-bottom: 0; padding-left:20px; padding-top:40px"> ___________________ </td>
                <td align="right" style="border-left: 0; border-bottom: 0; padding-right:20px; padding-top:40px">____________</td>
                
                <td colspan="3" style="border-right: 0; border-bottom: 0;padding-left:20px; padding-top:40px"> ___________________</td>
                <td align="right" style="border-left: 0; border-bottom: 0;padding-right:20px; padding-top:40px">____________</td>
            </tr>
            <tr>
                <td colspan="3" style="border-right: 0;vertical-align: top; border-bottom: 0; border-top: 0;padding-left:20px;"> Name and Signature </td>
                <td align="center" style="border-left: 0; vertical-align: top; border-bottom: 0;  border-top: 0;padding-right:15px;  ">Date <br> (mm/dd/yr)</td>
                
                <td colspan="3" style="border-right: 0;vertical-align: top; border-bottom: 0;  border-top: 0;padding-left:20px;"> Name and Signature </td>
                <td align="center" style="border-left: 0;vertical-align: top; border-bottom: 0;  border-top: 0;padding-right:15px;">Date <br> (mm/dd/yr)</td>
                
            </tr>

            <tr>
                <td colspan="3" style="border-right: 0;vertical-align: top; border-bottom: 0; border-top: 0;padding-left:20px; padding-top:25px"> ___________________ </td>
                <td align="right" style="border-left: 0; vertical-align: top; border-bottom: 0;  border-top: 0;padding-left:20px;padding-top:25px  "></td>
                
                <td colspan="3" style="border-right: 0;vertical-align: top; border-bottom: 0;  border-top: 0;padding-left:20px; padding-top:25px"> ___________________ </td>
                <td align="right" style="border-left: 0;vertical-align: top; border-bottom: 0;  border-top: 0;padding-left:20px;padding-top:25px"></td>
                
            </tr>


            <tr>
                <td colspan="3" style="border-right: 0;vertical-align: top; border-bottom: 0; border-top: 0;padding-left:20px; padding-bottom:15px;"> Position/Affiliation </td>
                <td align="right" style="border-left: 0; vertical-align: top; border-bottom: 0;  border-top: 0;  padding-left:20px; padding-bottom:15px;"></td>
                
                <td colspan="3" style="border-right: 0;vertical-align: top; border-bottom: 0;  border-top: 0;padding-left:20px; padding-bottom:15px;"> Position/Affiliation </td>
                <td align="right" style="border-left: 0;vertical-align: top; border-bottom: 0;  border-top: 0;padding-left:20px; padding-bottom:15px;"></td>
                
            </tr>

        </table>




        <table> 
            <tr> <td> <div style="position: fixed; bottom: 0; text-align:left; font-size: 12px; font-style:italic; color: #808080;" >PhilRice RCEF SAR Rev 02 Effectivity Date: 26 May 2021</div> 
                </td>
            
                <td> <div style="position: fixed; bottom: -30; right: -20; max-height: 48px;"></div> </td>
                <!-- <td> <img style="position: fixed; bottom: -30; right: -20; max-height: 48px;"  src="< ?php echo $_SERVER["DOCUMENT_ROOT"] . '/rcef_ws2021/public/images/socotec_logo_iar.jpg' ?>" /> </td> -->
            </tr>


        </table>

    </body>




</html>

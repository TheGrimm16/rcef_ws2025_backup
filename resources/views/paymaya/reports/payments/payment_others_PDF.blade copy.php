<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>RCEF</title>
<style>
#emp, th, td {
  border: 1px solid black;
  border-collapse: collapse;
}
#emp, td, #emp th{
  padding: 1px;
  text-align :center;
}


#win {
  background-color: ;
  width: 100px;
  border: 1px solid black;
  padding: 0;
  margin: 0x;
}


@page { margin-top: 15px; margin-bottom: 0px; }
body { margin-top: 20px; margin-bottom: 0px;}


</style>

</head>
  <body>
  <div>
   
      <table style="border:none;" width="90%" style="border:none;" id="joe">
        <tr style="border:none;  vertical-align:top">
            <td style="border:none; text-align:center;">
                <span> <img src="{{ public_path('images/dbp2.png')}}"> </span>
            </td>
        </tr>  
    </table><br>
    <center><strong style="font-size: 18pt "><u>FUND TRANSFER APPLICATION FORM</u></strong></center>
      <br><br>

      <table style="border:none; font-size: 12pt" width="60%" > 
        <tr style =" border:none; vertical-align:top">
          <td  style ="border:none; text-align:left;" >Fund Transfer thru&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
          <td  style ="border:none; text-align:center;" ><div style="border: 1px solid black; height:20px; width:20px; margin-left:10px;"></div></td>
          <td  style ="border:none; text-align:left; padding-left:-30px;" >&nbsp;Philippine Peso-Real Time GrossSettlement (RTGS)</td>
        </tr>
        <tr style =" border:none; vertical-align:top">
          <td  style ="border:none; text-align:left; "></td>
          <td  style ="border:none; text-align:center;" ><div style="border: 1px solid black; height:20px; width:20px; margin-left:10px;">x</div></td>
          <td  style ="border:none; text-align:left;  padding-left:-30px;" >&nbsp;PCHC-Electronic Peso Clearing & Settlement (EPCS)</td>
        </tr>   
    </table>

    <table style="border:none; font-size: 12pt" width="95%"> 
      <tr style =" border:none; vertical-align:top">
        <td  style ="border:none; text-align:left; padding-right:70px;" >Value date &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;</td>
        <td  style ="border:none; text-align:left; border-bottom:1px solid black;" >{{date('F d, Y',strtotime($date3))}}</td>
      </tr>

      <tr style =" border:none; vertical-align:top">
        <td  style ="border:none; text-align:left;" >Currency</td>
        <td  style ="border:none; text-align:left; margin-left:40px; "  >PHILIPPINE PESO</td>
      </tr>
      
      <tr style =" border:none; vertical-align:top">
        <td  style ="border:none; text-align:left;" >Amount</td>
        <td  style ="border:none; text-align:left; border-bottom:1px solid black;" ><b><strong>{{$word_number}}</strong></b></td>
      </tr>

      <tr style =" border:none; vertical-align:top">
        <td  style ="border:none; text-align:left;" ></td>
        <td  style ="border:none; text-align:left; border-bottom:1px solid black;" >PHP {{number_format($overall_net_amount,'2')}}</td>
      </tr>

      <tr style =" border:none; vertical-align:top">
        
        <td  style ="border:none; text-align:left;" >FROM:</td>
        <td  style ="border:none; text-align:left;" ></td>

      </tr>

      <tr style =" border:none; vertical-align:top">
        
        <td  style ="border:none; text-align:left;" ><u>Ordering Customer</u></td>
        <td  style ="border:none; text-align:left;" ></td> 
      </tr>

      <tr style =" border:none; vertical-align:top">
        <td  style ="border:none; text-align:left;" >Name</td>
        <td  style ="border:none; text-align:left; border-bottom:1px solid black;" >PHILRICE RCEF</td>
      </tr>
   
      <tr style =" border:none; vertical-align:top">
        <td  style ="border:none; text-align:left;" >Address</td>
        <td  style ="border:none; text-align:left; border-bottom:1px solid black;" >Maligaya Science City Of Muñoz, Nueva Ecija</td>
      </tr>
      
      <tr style =" border:none; vertical-align:top">
        <td  style ="border:none; text-align:left;" >Contact Number</td>
        <td  style ="border:none; text-align:left; border-bottom:1px solid black;" >09056506032 </td>
      </tr>
    </table>
{{-- start --}}
    <table style="border:none; font-size: 12pt" width="70%" > 
      <tr style =" border:none; vertical-align:top">
        <td  style ="border:none; text-align:left;" ><u>Transaction Details:</u></td>
      </tr>
      <tr style =" border:none; vertical-align:top">
        <td  style ="border:none; text-align:left;" >Fund/Charge to be debited from</td>
        <td  style ="border:none; text-align:center;" ><div style="border: 1px solid black; height:20px; width:20px; margin-left:15px;"></div></td>
        <td  style ="border:none; text-align:left; padding-left:-20px;" >Savings Account No. &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; _____________________________</td>
      </tr>
      <tr style =" border:none; vertical-align:top">
        <td  style ="border:none; text-align:left; "></td>
        <td  style ="border:none; text-align:center;" ><div style="border: 1px solid black; height:20px; width:20px; margin-left:15px;">x</div></td>
        <td  style ="border:none; text-align:left;  padding-left:-20px;" >Current Account No. &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;____<u>00-0-01611-530-4</u>__________</td>
      </tr>
      <tr style =" border:none; vertical-align:top">
        <td  style ="border:none; text-align:left; "></td>
        <td  style ="border:none; text-align:center;" ><div style="border: 1px solid black; height:20px; width:20px; margin-left:15px;"></div></td>
        <td  style ="border:none; text-align:left;  padding-left:-20px;" >Time/OPS/SS No. &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;_____________________________</td>
      </tr> 
      <tr style =" border:none; vertical-align:top">
        <td  style ="border:none; text-align:left; "></td>
        <td  style ="border:none; text-align:center;" ><div style="border: 1px solid black; height:20px; width:20px; margin-left:15px;"></div></td>
        <td  style ="border:none; text-align:left;  padding-left:-20px;" >Other &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;_____________________________</td>
      </tr>   
  </table>
</div>

  <br><br><br><br><br><br>


   
  <div>
      <p>TO:<br>
        <u>Beneficiary Details:</u>
      </p>


        <table style="border:none; font-size: 11pt" width="100%" align="center"> 
          <tr style =" border:none; vertical-align:top">
            <th colspan="2;" style ="border:none; text-align:center;" >Name/Payee</th>
            <th colspan="2;" style ="border:none; text-align:center;" >Address</th>
            <th style ="border:none; text-align:center;" >Bank Name/Branch</th>
            <th style ="border:none; text-align:center;" >Account No.</th>
            <th style ="border:none; text-align:center;" >Amount</th>
              
          </tr>
          
            @foreach ($table_details as $row)
            {{$r++}}
            <tr style =" border:none; vertical-align:top">
              <td colspan="2;" style ="border:none; text-align:center; font-size: 10pt;" >{{$row->coop_name}}</td>
              <td colspan="2;" style ="border:none; text-align:center;  font-size: 10pt;"  >{{$row->address_1}}</td>
              <td style ="border:none; text-align:center;" >{{$row->branch}}</td> 
              <td style ="border:none; text-align:center;" >{{$row->account_no}}</td> 
              <td style ="border:none; text-align:center;" >{{number_format($row->net_amount,'2')}}</td> 
            </tr>
            @endforeach

            @for ($i = $r; $i <8; $i++)
              <tr style =" border:none; vertical-align:top">
                <td colspan="2;" style ="border:none; text-align:center;" >-</td>
                <td colspan="2;" style ="border:none; text-align:center; " >-</td>
                <td style ="border:none; text-align:center;" >-</td>  
                <td style ="border:none; text-align:center;" >-</td> 
                <td style ="border:none; text-align:center;" >-</td> 

                {{-- <td  style="color:white; border:none;">-</td>
                <td  style="color:white; border:none;">-</td>  
                <td  style="color:white; border:none;">-</td>  
                <td  style="color:white; border:none;">-</td>
                <td  style="color:white; border:none;">-</td>  --}}
              </tr>
            @endfor
              
       
          
          
          </tr>
          <tr style =" border:none; vertical-align:top">
            <td colspan="2;"  style ="border:none; text-align:center; border-bottom:1px solid black;" > </td>
            <td  colspan="2;" style ="border:none; text-align:center; border-bottom:1px solid black;" ></td>
            <td  style ="border:none; text-align:center; border-bottom:1px solid black;"></td>
            <th  style ="border:none; text-align:center; border-bottom:1px solid black; font-size: 12pt" >GRAND TOTAL</th> 
            <th  style ="border:none; text-align:center; border-bottom:1px solid black; font-size: 12pt" >{{number_format($overall_net_amount,'2')}}</th>    
          </tr>
          
      </table>
        <br><br><br>     
        <table style="border:none;" width="100%" align="center">
          <tr style =" border:none; vertical-align:top">     
            <td  style ="border:none; border-bottom:1px solid black; text-align:center; " >{{$signatory2->full_name}}</td>
            <td  style ="border:none;  text-align:center; " > &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</td>
            <td  style ="border:none; border-bottom:1px solid black; text-align:center;" >{{$signatory3->full_name}}</td>  
          </tr>

          <tr style =" border:none; vertical-align:top">     
            <td  style ="border:none; text-align:center; " >(Client Authorized Signature)</td>
            <td  style ="border:none;  text-align:center; " >  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</td>
            <td  style ="border:none text-align:center;" >(Client Authorized Signature)</td>  
          </tr>

        </table>
        <br>
        <p>CM/BRANCH AUTHORIZED SIGNATURE</p>
        <table style="font-size: 12pt" width="100%" id="emp">    
          <tr> 
            <td>Posted-CASA</td>
            <td>Verified</td>
            <td>Approved</td>
          </tr>              
      </table>



      <p>FOR CM USE ONLY</p>
      <table style="font-size: 12pt" width="100%" id="emp">    
        <tr> 
          <td>FORWARDED-CDU (RTGS)</td>
          <td>TRANSMITTED-CM (EPCS)</td>
        </tr>              
      </table>
      

</div>
  
    
<br><br><br><br><br><br><br><br><br><br>


    <div>
   
   
    <table  style ="border:none; text-align:left; font-size: 12pt" width="100%" id="emp" >    
      <tr> 

        <td style ="border:none; text-align:left;" >2023DS Binhi E-Padala:</td>
      </tr>
      <tr>
        <td style ="border:none; text-align:left;">Payment Summary of Other Banks</td>   
      </tr>
      <tr> 
        <td style ="border:none; text-align:left;">{{date('F d, Y',strtotime($date3))}}</td>
      </tr>              
  </table>
  <br>



  <table style="border:none; font-size: 11pt" width="100%" align="center"> 
  <thead>
    <tr style =" border:none; vertical-align:top">
      <td  style ="border:none; text-align:center; font-size: 11pt" >Name/Payee</td>
      <td  style ="border:none; text-align:center;" ># of bags</td>
      <td  style ="border:none; text-align:center; " >Amount</td>
      <td  style ="border:none; text-align:center;" >1% retention</td>  
      <td  style ="border:none; text-align:center;" >Net Amount Due</td> 
    </tr>
  </thead>
{{-- 
  total_bags
amount
retention
net_amount --}}
     <tbody id='databody'>
      @foreach ($table_details as $row)
      {{$r++}}
      <tr style =" border:none; vertical-align:top">
        <td  style ="border:none; text-align:center; font-size: 10pt" >{{$row->coop_name}}</td>
        <td  style ="border:none; text-align:center;" >{{number_format($row->total_bags,'2')}}</td>
        <td  style ="border:none; text-align:center;" >{{number_format($row->amount,'2')}}</td>
        <td  style ="border:none; text-align:center; " >{{number_format($row->retention,'2')}}</td>
        <td  style ="border:none; text-align:center;" >{{number_format($row->net_amount,'2')}}</td>  
      </tr>
      @endforeach
      
      @for ($i = $r; $i <7; $i++)
      <tr style =" border:none; vertical-align:top">
        <td  style ="border:none; text-align:center; font-size: 10pt" >-</td>
        <td  style ="border:none; text-align:center; " >-</td>
        <td  style ="border:none; text-align:center;" >-</td>  
        <td  style ="border:none; text-align:center;" >-</td> 
        
        <td  style ="border:none; text-align:center;" >-</td> 
      </tr>
      @endfor
      <tr style =" border:none; vertical-align:top">
        <th  style ="border:none; border-bottom:1px solid black; text-align:center;" >GRAND TOTAL</th>
        <th  style ="border:none; border-bottom:1px solid black;text-align:center;" >{{number_format($overall_bags,'2')}}</th>
        <th  style ="border:none; border-bottom:1px solid black;text-align:center;" >{{number_format($overall_amount,'2')}}</th>
        <th  style ="border:none; border-bottom:1px solid black; text-align:center; " >{{number_format($overall_retention,'2')}}</th>
        <th  style ="border:none; border-bottom:1px solid black; text-align:center;" >{{number_format($overall_net_amount,'2')}}</th>  
      </tr>


    </tbody>  
    
</table>

<br><br><br><br><br>


<table style="border:none;" width="100%" align="center">
  <tr style =" border:none; vertical-align:top">     
    <td  style ="border:none; border-bottom:1px solid black; text-align:center; " >{{$signatory1->full_name}}</td>
    <td  style ="border:none;  text-align:center; " > &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</td>
    <td  style ="border:none; border-bottom:1px solid black; text-align:center;" >{{$signatory2->full_name}}</td>  
    <td  style ="border:none;  text-align:center; " > &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</td>
    <td  style ="border:none; border-bottom:1px solid black; text-align:center;" >{{$signatory3->full_name}}</td> 
  </tr>

  <tr style =" border:none; vertical-align:top">     
    <td  style ="border:none; text-align:center; " >{{$signatory1->designation}}</td>
    <td  style ="border:none;  text-align:center; " >  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</td>
    <td  style ="border:none text-align:center;" >{{$signatory2->designation}}</td> 
    <td  style ="border:none;  text-align:center; " >  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</td>
    <td  style ="border:none text-align:center;" >{{$signatory3->designation}}</td>
  </tr>

</table>


</div>
 
</body>
</html>
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
@page { margin-top: 10px; margin-bottom: 0px; }
body { margin-top: 0px; margin-bottom: 0px;}

</style>

</head>
  <body>
    <br>
      <table style="" width="100%" style ="border:none;" id=""> 
        <tr style ="border:none;  vertical-align:center">
          <td style ="border:none; text-align:center;" >
            <br>
          </td>
          <td style ="border:none; text-align:center">
            <ul style="list-style-type:none">
            <li style="font-size: 12pt"> <strong>DEVELOPMENT BANK OF THE PHILLIPINES</strong></li>
            <li>CABANATUAN BRANCH, CABANATUAN CITY</li><br><br>
            <li><b>AUTHORITY TO DEBIT/CREDIT</b></li>
            </ul>
          </td>      
        </tr>
      </table>

  
    <p>Date: {{date('F d, Y',strtotime($date3))}}</p>
    

    <p>This is to authorize Development Bank of the Philiipines Cabanatuan Branch to debit our Current Account<br>
    Number <b>00-0-01611-530-4</b> in the amount of <b><strong>{{$word_number}}</strong></b> for:</p><br>
    
    <p>Please mark appopriate box.<br>
        ( ) Purchase of___________Personal/Commercial/MDS/Emergency Checkbooks<br>
        ( ) Loan/Interest/GRT/Default Changes/Commitment/Front End/Service Fee<br>
        (x) Credit to CA/SA No.:<br>
    </p>
    <br> <br>

      <table style="font-size: 10pt" width="100%" id="emp">  
          @foreach ($table_details as $row)  
            <tr> 
              <td>{{$row->coop_name}}</td>
              <td>{{$row->account_no}}</td>
              <td>{{number_format($row->net_amount, '2')}}</td>
            </tr>
          @endforeach
          
          <tr>
            <td></td>             
            <td ></td>             
            <th>{{number_format($overall_net_amount,'2')}}</th>                           
        </tr>            
      </table>
    <br><br>
    <p>
        ( ) Time/Special Savings/Option Savings Deposite/Blue Chip Fund/T-Bills Placement<br>
        ( ) Full/Partial Withdrawals in Managers Check/Credit to<br>
        ( ) Purchase of Managers Check (MC)<br>
        ( ) Cost of ATM Card/s<br>
        ( ) Others, pls. Specify ________________________________________________________<br>
    </p>
          <br><br><br><br>
      <table style="border:none;" width="100%" align="center">
        <tr style =" border:none; vertical-align:top">
              
          <td  style ="border:none; border-top:1px solid black; text-align:center; " >{{$signatory2->full_name}}</td>
          <td  style ="border:none;  text-align:center; " > &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</td>
          <td  style ="border:none; border-top:1px solid black; text-align:center;" >{{$signatory3->full_name}}</td>  
        </tr>
       
      </table>

        {{-- nag delete ng br br tnx --}}
            
          

      <center> <p>FOR BANKS USE ONLY</p></center>
      <table style="font-size: 10pt" width="100%" id="emp">    
        <tr> 
          <td>VERIFIED BY/ON:</td>
          <td>APPROVED BY/ON</td>
          <td>POSTED BY/ON:</td>
        </tr>
         <tr>
          <td><br></td>             
          <td><br></td>             
          <td><br></td>                           
       </tr>               
    </table>
    
    <br><br>
    <br><br><br><br><br><br><br><br><br><br><br><br>

    <table  style ="border:none; text-align:left; font-size: 12pt" width="100%" id="emp" > 
      <tr> 
        <td style ="border:none; text-align:left;" ><br><br></td>
      </tr>   
      <tr> 
        <td style ="border:none; text-align:left;" >2025DS Binhi E-Padala</td>
      </tr>
      <tr>
        <td style ="border:none; text-align:left;">Payment Summary with DBP Account</td>   
      </tr>
      <tr> 
        <td style ="border:none; text-align:left;">{{date('F d, Y',strtotime($date3))}}</td>
      </tr>              
  </table>
  <br><br>
  <table style="border:none; font-size: 10pt" width="100%" align="center"> 
    <thead>
      <tr style =" border:none; vertical-align:top">
        <td  style ="border:none; text-align:center; font-size: 10pt" >Name of Coop</td>
        <td  style ="border:none; text-align:center;" ># of bags</td>
        <td  style ="border:none; text-align:center; " >Amount</td>
        <td  style ="border:none; text-align:center;" >1% retention</td> 
        <td  style ="border:none; text-align:center;" >Net Amount Due</td>   
      </tr>
    </thead>
      
    <tbody id='databody'>
      @foreach ($table_details as $row)
      <tr style =" border:none; vertical-align:top">
        <td  style ="border:none; text-align:center; font-size: 10pt" >{{$row->coop_name}}</td>
        <td  style ="border:none; text-align:center;" >{{number_format($row->total_bags,'2')}}</td>
        <td  style ="border:none; text-align:center; " >{{number_format($row->amount,'2')}}</td>
        <td  style ="border:none; text-align:center;" >{{number_format($row->retention,'2')}}</td>  
        <td  style ="border:none; text-align:center;" >{{number_format($row->net_amount,'2')}}</td> 
      </tr>
      @endforeach

      <tr style =" border:none; vertical-align:top">
        <th  style ="border:none; border-bottom:1px solid black; text-align:center;" >GRAND TOTAL</th>
        <th  style ="border:none; border-bottom:1px solid black;text-align:center;" >{{number_format($overall_bags,'2')}}</th>
        <th  style ="border:none; border-bottom:1px solid black;text-align:center;" >{{number_format($overall_amount,'2')}}</th>
        <th  style ="border:none; border-bottom:1px solid black; text-align:center; " >{{number_format($overall_retention,'2')}}</th>
        <th  style ="border:none; border-bottom:1px solid black; text-align:center;" >{{number_format($overall_net_amount,'2')}}</th>  
      </tr>
    </tbody>  
    
</table>

<br><br><br>
<p>Prepared by:</p><br>


<p><strong>{{$signatory1->full_name}}</strong><br>
  {{$signatory1->designation}}
</p>
<br>

<p>Noted by:</p><br>
</p>

<p><strong>{{$signatory2->full_name}}</strong><br>
  {{$signatory2->designation}}<br>
</p>

<br>
<p>Approved by:</p><br>
</p>

<p> <strong>{{$signatory3->full_name}}</strong><br>
  {{$signatory3->designation}}<br>
</p>




  
 
</body>
</html>
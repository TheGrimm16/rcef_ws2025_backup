<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>DBP Summary</title>
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

    <table  style ="border:none; text-align:left; font-size: 12pt" width="100%" id="emp" > 
      <tr> 
        <td style ="border:none; text-align:left;" ><br><br></td>
      </tr>   
      <tr> 
        <td style ="border:none; text-align:left;" >Binhi e-Padala Dry Season 2025</td>
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

<br>
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
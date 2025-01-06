<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>DBP Payment Form</title>
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

td{
  border: none;
  margin: 0;
  padding: 0;
  height: 1.065rem;
  /* outline: solid 1px #000; */
}

.coops{
  font-size: 0.4rem;
  position: absolute;
  left: 5.8%;
  top: 47.6%;
  /* border: solid 1px #000; */
  width: 355px;
  box-sizing: border-box;
}

tr{
  margin: 0;
  padding: 0;
}

.coops tr td{
  /* outline: 1px solid #000; */
}

.coops tr td:nth-of-type(1){
  font-size: 1.2rem;
  width: 20%;
}
.coops tr td:nth-of-type(2){
  font-size: 1rem;
  width: 40%;
}
.coops tr td:nth-of-type(3){
  font-size: 1.2rem;
  width: 20%;
}
.coops tr td:nth-of-type(4){
  font-size: 1.2rem;
  width: 20%;
}

.signatories{
  position: absolute;
  bottom: 37.7%;
  width: 100%;
  font-size: 0.7rem;
}

.payee{
  width: 100%;
}

.payee tr td{
  /* outline: 1px solid #000; */
  height: 0.93rem;
}

.payee tr td:nth-of-type(1){
  width: 19.2%;
}
.payee tr td:nth-of-type(2){
  width: 38.4%;
}
.payee tr td:nth-of-type(3){
  width: 24.9%;
}
.payee tr td:nth-of-type(4){
  /* width: 21.2%; */
}

.coops_total, .coops_total_word{
  font-size: 0.4rem;
  position: absolute;
  left: 5.8%;
  top: 55.8%;
  /* border: solid 1px #000; */
  width: 355px;
  box-sizing: border-box;
}

.coops_total tr td:nth-of-type(1){
  font-size: 0.9rem;
  width: 20%;
}
.coops_total tr td:nth-of-type(2){
  font-size: 1rem;
  width: 40%;
}
.coops_total tr td:nth-of-type(3){
  font-size: 1rem;
  width: 20%;
}
.coops_total tr td:nth-of-type(4){
  font-size: 1.2rem;
  width: 20%;
}

.location{
  position: absolute;
  top: 12.25%;
  left: 44%;
  font-size: 0.6rem;
}

.time{
  position: absolute;
  top: 10.20%;
  right: 8%;
  font-size: 0.6rem;
}

</style>

</head>
  <body>
    <div>
      <img src="{{ public_path('images/dbp_new2.jpg')}}" style="width:100%; height:100%; position:absolute;">
    </div>
    <div class="time">
      {{date('F d, Y',strtotime($date3))}}
    </div>
    <div class="location">
      CABANATUAN
    </div>
    <div style="font-size: 0.6rem; position: absolute; top: 16%; margin: 0 6.3% 0 5.7%;">
      <table class="payee">
        <tr>
          <td>CURRENT</td>
          <td>PHILRICE RCEF</td>
          <td>00-0-01611-530-4</td>
          <td>{{number_format($overall_net_amount,'2')}}</td>
        </tr>
        <tr>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td style="height: 0.6rem;"></td>
          <td style="height: 0.6rem;"></td>
          <td style="height: 0.6rem;"></td>
          <td style="height: 0.6rem; font-size: 0.8rem;">{{number_format($overall_net_amount,'2')}}</td>
        </tr>
      </table>
      <table style="width: 100%; transform: translateY(-10%)">
        <tr>
          <td style="width: 20%; height: 1.6rem;"></td>
          <td style="width: 80%; height: 1.6rem; text-align: left; vertical-align: top; font-size: 0.8rem; line-height: 140%;">{{$word_number}}</td>
        </tr>
      </table>
    </div>

    <div>
      <table class="coops">
        @foreach($table_details as $row)
        <tr>
          @if (preg_match('/^00-5/', $row->account_no))
            <td>SAVINGS</td>
          @else
            <td>CURRENT</td>
          @endif
          <td>{{$row->coop_name}}</td>
          <td>{{$row->account_no}}</td>
          <td>{{number_format($row->net_amount,'2')}}</td>
        </tr>
        @endforeach
      </table>
      <table class="coops_total">
        <tr>
          <td></td>
          <td></td>
          <td></td>
          <td>{{number_format($overall_net_amount,'2')}}</td>
        </tr>
      </table>
      <table class="coops_total_word" style="transform: translateY(1rem); height: 1.6rem; font-size: 1rem; line-height: 80%">
        <tr>
          <td style="width: 34%;"></td>
          <td style="padding: 0; font-size: 0.4rem; text-align: start;">{{$word_number}}</td>
        </tr>
      </table>
    </div>

    <div>
      <table class="signatories">
        <tr>
          <td style="width: 50%;">{{$signatory2->full_name}}</td>
          <td style="width: 50%;">{{$signatory3->full_name}}</td>
        </tr>
      </table>
    </div>
    <!-- <table  style ="border:none; text-align:left; font-size: 12pt" width="100%" id="emp" > 
      <tr> 
        <td style ="border:none; text-align:left;" ><br><br></td>
      </tr>   
      <tr> 
        <td style ="border:none; text-align:left;" >eBinhi Padala Phase III</td>
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
</p> -->


 
</body>
</html>
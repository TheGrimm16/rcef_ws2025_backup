<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>RCEF</title>
<style>




@page {
   /* margin-top: 10px; 
   margin-bottom: 0px;  */
  }

  p{
  /* font-family: Arial, Helvetica, sans-serif; */
  position: absolute;
  font-size: 12px;
  }

  .amount_word{
    font-size: 9px;
  }
  .coop_name{
    font-size: 9px;
  }

body { 
  margin: 2px;
}

</style>

</head>

  <body>
    <div>
      <div>
        <img src="{{ public_path('images/dbp_new1.jpg')}}" style="width:100%; height:100%; position:absolute;">
      </div>
      <div>
        <p style="margin-left:421px; margin-top:108px;">{{date('m/d/Y',strtotime($date3))}}</p><br>
        <p style="margin-left:125px; margin-top:49px;">PHILRICE RCEF</p><br>
        <p style="margin-left:125px; margin-top:51px;">00-0-01611-530-4</p><br><br>
        <p class="amount_word" style="margin-left:2px; margin-top:50px;">{{$word_number}}</p>
        <p style="margin-left:475px; margin-top:50px;">{{number_format($table_details->net_amount, '2')}}</p><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
  
        <p style="margin-left:137px; margin-top:50px;">Current Account</p><br>
        <p class="coop_name" style="margin-left:136px; margin-top:46px;">{{$table_details->coop_name}}</p><br>
        <p style="margin-left:137px; margin-top:39px;">{{$table_details->account_no}}</p><br><br><br>
  
        <p style="margin-left:90px; margin-top:37px;">{{$signatory2->full_name}}</p>
        <p style="margin-left:465px; margin-top:37px;">{{$signatory3->full_name}}</p><br>
      </div>

    </div>
    

    
</body>

</html>


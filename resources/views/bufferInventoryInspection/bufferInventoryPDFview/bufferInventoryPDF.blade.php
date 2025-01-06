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
  padding: 3px;
  text-align :center;
}
@page { margin-top: 0px; margin-bottom: 0px; }
body { margin-top: 0px; margin-bottom: 0px;}
#watermark {
                position: fixed;

                /** 
                    Set a position in the page for your image
                    This should center it vertically
                **/
                bottom:   15cm;
                left:     3.5cm;

                /** Change image dimensions**/
                width:    12cm;
                height:   12cm;
                opacity: .1;
                /** Your watermark should be behind every content**/
                z-index:  1;
            }
</style>

</head>
<body> 
  <div id="watermark">
    <img src="{{ public_path('images/iarreplacementpdf/watermart.jpg')}}" height="100%" width="100%" />
</div>
  @foreach ($datas as $data)
      {{-- {{dd($data)}} --}}
  @for ($i = 0; $i < $data['countPage']; $i++)

  <br>
  <table style="border-bottom:1px solid black" width="100%" style ="border:none;" id="joe"> 
        <tr style ="border:none;  vertical-align:top">
          <td style ="border:none; text-align:left;" >
            <br>
          <span> <img src="{{ public_path('images/iarreplacementpdf/plogo.jpg')}}" style="height: 80px;width: 80px;" alt=""> 
          <img src="{{ public_path('images/iarreplacementpdf/One_DA.png')}}" style="height: 80px;width: 80px;" alt=""> </span> 
          </td>
          <td style ="border:none; text-align:left">
            <ul style="list-style-type:none">
            <li style="font-size: 14pt"> <strong>RCEF Buffer & Inventory Inspection Result  </strong></li>
            <li>(Replacement Seeds)</li>
            </ul>
          </td>      
        </tr>
        
    </table>
  <table width="100%" style ="border:none; margin-bottom:0px; font-size: 10pt" id="joe"> 
        <tr style ="border:none;  vertical-align:top">
          <td style ="border:none; text-align:left;" >
          <ul> 
          <li> <strong>Batch Ticket Number (ORIGIN):</strong><u>{{$data['originTicket']}}</u></li>
          <li> <strong>Batch # (REPLACEMENT):</strong> <u>{{$data['replacementTicket']}}</u></li>
          <li> <strong>Region:</strong> <u>{{$data['region']}}</u></li>
          <li> <strong>Province:</strong> <u>{{$data['province']}}</u></li>
          <li> <strong>Municipality:</strong> <u>{{$data['municipality']}}</u></li>
          <li> <strong>DOP:</strong> <u>{{$data['DOP']}}</u></li>
          </ul>
          </td>
          <td style ="border:none; text-align:left">
          <ul> 
          <li> <strong>Coop Name:</strong> <u>{{$data['CoopName']}}</u></li>
          <li> <strong>Accreditation No:</strong> <u>{{$data['accreditation_no']}}</u></li>
          <li> <strong>MOA Number:</strong> <u>{{$data['moa']}}</u></li>
          </ul>
          </td>         
        </tr>        
    </table>
    <div style="z-index:0; padding-left:10px;border:1px solid black; border-radius:10px; height:12%; width:98%; background: rgba(250,250,250,1); margin:auto; margin-top:0px">
  
    <table style="font-size: 10pt" width="95%" >
   {{--    <p style="padding-top:0px; padding-left:9px"></p> --}}
      <tr style ="border:none;  vertical-align:top">
        <td style ="border:none; text-align:left; width: 280px;" >
          <Strong> Replace Due to:</Strong>
          <ul>
            @if ($data['is_palleted']==0)
            <li>Not Palleted</li> 
            @endif
            @if ($data['is_good_stocking']==0)
            <li>Poor Stacking</li>
            @endif
            @if ($data['is_good_wh']==1)
            <li>Poor Warehousing 
              <ul>
                @if ($data['wh_pest']==1)
                <li>pest</li>
                @endif
                @if ($data['wh_temperature']==1)
                <li>Temperature</li>
                @endif
                @if ($data['wh_roofing']==1)
                <li>roofing</li>
                @endif                              
            </ul>
          </li>
            @endif

             
            
         
        </ul>
      </td>
    
          <td style ="border:none; text-align:left; width: 380px;  word-wrap:break-word;" >
           <strong>Remarks:</strong>    
           {{$data['remarks']}}          
        </td>
      </tr>
    </table>
     
    </div>
    <br>
    <hr style="width:95%; border-width:0.5px">
    <br>
    <table style="font-size: 10pt" width="100%" id="emp"> 
        <tr>
            <td style="text-align: center" colspan="4"><Strong>SeedTag / RLA List</Strong></td>
          </tr> 
        <tr>
          
          <td><strong>SG Name</strong></td>
          <td><strong>Seedtag</strong></td>
          <td><strong>Total Volume</strong></td>
          <td><strong>Total Delivered</strong></td>
        </tr>
       
     
         
       @foreach ($data['SeedTagList'][$i]['page'.$i.''] as $Sglist)
      {{--  {{dd($data['SeedTagList'][$i]['page'.$i.''][0]['sg_name'])}} --}}
      {{-- {{dd($item['sg_name'])}} --}}
         
            <tr>
              <td @if ($Sglist['sg_name']=="##")style="color: white"@endif>{{$Sglist['sg_name']}}</td>             
              <td @if ($Sglist['sg_name']=="##")style="color: white"@endif>{{$Sglist['seedTag']}}</td>             
              <td @if ($Sglist['sg_name']=="##")style="color: white"@endif>{{$Sglist['totalV']}}</td>             
              <td @if ($Sglist['sg_name']=="##")style="color: white"@endif>{{$Sglist['totalD']}}</td>             
            </tr>               
         
       @endforeach
       
      </table>




      <ul style="list-style-type:none">
        <li style="float: left;"> 

      <br>
          <table style="border:none;" width="60%" > 
            <tr style =" border:none; vertical-align:top">
              <td  style ="border:none; border-top:1px solid black; text-align:center;" >Name & Signature of PC/Seed Inspector</td>
              <td style ="border:none;"></td>
              <td style =" border:none; border-top:1px solid black;text-align:center">
                Date
              </td>      
            </tr>
            
        </table>
      
        <br><br>
          <table style="border:none;" width="60%" > 
            <tr style =" border:none; vertical-align:top">
              <td  style ="border:none; border-top:1px solid black; text-align:center;" >Name & Signature of RCEF Branch Unit Operation Focal</td>
              <td style ="border:none;"></td>
              <td style =" border:none; border-top:1px solid black;text-align:center">
                Date
              </td>      
            </tr>
            
        </table> 
        </li>
              <li style="float: left; padding-left: 1%;">
              
              </li>
            </ul>
      <br>
            <div style="border: 1px solid black; height:150px; width:240px;margin-left:65%;">Date Received:</div>
            <br><br>
            {{-- <img src="{{ public_path('images/iarreplacementpdf/Socotec-Logo.jpg')}}" alt="socotech logo" style="float:right; z-index:1; height: 80px;width: 140px;"> --}}
            <br><br><br><br>
            <br><br><br><br>
            <br><br><br><br>
  @endfor

@endforeach
</body>
</html>
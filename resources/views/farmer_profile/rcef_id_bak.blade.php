<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>RCEF Identification Generation</title>

    <style>
     

            /* set PDF margins to 0 */
            @page { margin: 10px; }
            /* set PDF margins to 0 */

            table {
                border-collapse: collapse;
            }

            *{font-family:Arial, Helvetica, sans-serif;}


            td{
                padding: 2px;
            }

            body{
                font-size: 12px;
            }

            .page-break {
                page-break-after: always;
            }
            
            ul{
                font-size:9.8px;
            }


    </style>
</head>
<body>



   <table style="width:100%; margin-right: 10px; margin-top: 12px;">
    

    {{-- @foreach($data as $info) --}}

        <tr>
            <td style="width:50%;"> 
                <div style="border-style:solid; width:362.83464567px; height:226.77165354px;  background: url('id_bg.jpg') 0 0/ 100px 100px no-repeat; "> 
                   

                

                    <img src="{{public_path('images/id_header.png')}}" style="height:60px;width:100%;margin-left:0px;margin-top:0px;"/>
                {{-- <img src="{{public_path('images/ph_logo.png')}}" style="height:33px;width:70px;margin-left:5px;margin-top:10px;"/>
                <img src="{{public_path('images/color_support.png')}}" style="float:right;height:33px;width:140px;margin-right:15px;margin-top:10px;"/> --}}
                <br>

                    <table style="width:100%; margin-right: 10px; margin-top: 12px;">
                        <tr>
                            <td style="width:120px; height:100px;" valign="top" rowspan="2">
                                <img src="{{public_path('images/1x1_pic.png')}}" style="height:96px;width:96px;margin-left:15px;"/> <br>
                                <img src="{{public_path('images/colors.png')}}" style="height:5px;width:96px;margin-left:15px; margin-top:7px;"/> 
                                
                            </td>
                            <td valign="top" style="height:80px;">

                                <big>
                               <b> {{strtoupper($info->firstName)}} {{strtoupper(substr($info->midName,0,1))}}. {{strtoupper($info->lastName)}}</b> <br>
                                RSBSA: {{$info->rsbsa_control_no}} </big> <br><br>
                                
                                <font size="8px;"> <b>BIRTHDATE(MM/DD/YYYY):</b> </font><br>
                                <b> {{date("m/d/Y", strtotime($info->birthdate))}} </b>   <br>
                                

                                    @php
                                        $address = strtoupper($info->brgy_name).strtoupper($info->municipality).",".strtoupper($info->province);
                                        if(strlen($address) > 23){
                                            $font_size = "6px";
                                        }else{
                                            $font_size = "8px";
                                        }

                                    @endphp
                                
                                <font size="8px"> <b>ADDRESS: </b> </font><br>
                                
                                <font size="{{$font_size}}"
                                <b>{{strtoupper($info->brgy_name)}} {{strtoupper($info->municipality)}}, {{strtoupper($info->province)}}  </b>
                                </font>

                              
                            </td>
                        </tr>
                       
                        <tr valign="top">
                               <td style="padding:0; margin:0; "> 
                                    <table style="width:100%; margin:0;padding:0;">
                                        <tr>
                                            <td valign="top" align="left">
                                                <font size="8px;"> <b>AFFILIATION:</b> </font>
                                            </td>
                                          
                                        </tr>

                                        <tr>
                                            <td style="width:180px; margin:0;padding:0;" valign="top" align="left">
                                                @if($info->fca_name == null)
                                                <b> -NONE- </b>
                                                @else
                                                <font size="11px;">  <b> {{$info->fca_name}} </b> </font>

                                                @endif
                                                

                                            </td>
                                          
                                        </tr>
                                    </table>
                                
                            
                            </td>
                        </tr>

                    </table>

                  
                
              


            </div> </td>
            <td style="width:50%;" > 

                

                <div  style="border-style:solid; width:362.83464567px; height:226.77165354px; background: url('id_bg.jpg')">
                    
                    
                    <table style="" >
                            <tr>
                                <td  valign="top"  align="left" style="padding:0;margin-left:0px; width:230px;"> 
                                    <ul style="margin:0;  padding-lef:15px; padding-top:5px; padding-bottom:0;">
                                        <li>This ID will serve as proof of identification during RCEF seed distribution. Present this ID upon claiming seeds.</li>
                                        <li>Avoid damages / scratches in the QR code.</li>
                                        <li>In case of loss, please inform your City/ Municipal Aggriculture Office.</li>
                                        <li>If found, please return to the rightful owner.</li>
                                    </ul> 
                                </td>


                                <td rowspan="2" valign="top" style="margin-left:10px; text-align:center;">
                                  
                                   
                                    <div style="border-style:solid; background-color: #fff; margin-left:7px;margin-top:5px; height:auto;width:100px;"> 

                                        <img class="qr_class" src="data:image/jpg;base64, {{ base64_encode(QrCode::format('png')->size(240)->margin(0)->generate($info->rcef_id)) }}" style="height:90px;width:90px;margin-right:0px;margin-top:5px;margin-bottom:5px;"/> <br>
                                    </div>
                                    {{-- <div style="border-left: solid; border-bottom: solid; border-right: solid; margin-left:7px;margin-top:0px; height:auto;width:100px;text-align:center;font-weight:bold;"> 
                                        SCAN ME!
                                    </div> --}}

                                    
                                   
                                    {{-- <table style="width:90%;">
                                        <tr>
                                            <td>  <img src="{{public_path('images/colors.png')}}" style="height:10px;width:96px;margin-left:5px;margin-top:7px;"/> </td>
                                        </tr>

                                        <tr>
                                            <td align="right" valign="bottom">
                                                 <img src="{{public_path('images/iarreplacementpdf/One_DA.png')}}" style="height:38px;width:38px; margin-left:5px; margin-top:20px; z-index:-1;"/>
                                                <img src="{{public_path('images/rlogo.png')}}" style="height:35px;width:55px;  margin-top:20px;"/> 
                                            </td>
                                        </tr>
                                    </table> --}}
                               
                               
                                </td>
                               
                            </tr>
                            <tr>
                               

                                <td valign="top" style="padding-left:5;height:30px; padding-top:0;">
                                    <small> Signature </small>  <br>
                                    <div style="height:22px;width:230px;border-style:solid;background-color:#E5E4E4;"> </div>
                                        <table  style="width:320px; font-size:10px; margin:0; padding:0; ">
                                                <tr>
                                                    <td colspan="2">
                                                        <b> CONTACT US: </b>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:0;margin:0;">Website:</td>
                                                    <td style="padding:0;margin:0;">rcef-seed.philrice.gov.ph</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:0;margin:0;">Email:</td>
                                                    <td style="padding:0;margin:0;">rcefseedsprogram@gmail.com</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:0;margin:0;">Facebook:</td>
                                                    <td style="padding:0;margin:0;">facebook.com/rcef.official</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:0;margin:0; ">PhilRice Text Center:</td>
                                                    <td style="padding:0;margin:0;">0917-111-7423</td>
                                                </tr>




                                        </table>



                                    {{-- <table  style="padding:0;margin:0; width:100%;">
                                        <tr>
                                            <td align="left" style="font-size:9px;">  <b>WEBSITE</b> </td>
                                            <td align="right" style="font-size:9px;"> <b> FACEBOOK PAGE:</b> </td>
                                        </tr>
                                        <tr>
                                            <td align="left" style="font-size:8px;"> www.rcef-seed.philrice.gov.ph </td>
                                            <td align="right" style="font-size:8px;"> facebook.com/rcef_pmo </td>
                                        </tr>
                                        <tr>
                                            <td align="left"> </td>
                                            <td align="right">  </td>
                                        </tr>
                                        
                                        <tr>
                                            <td align="left" style="font-size:9px;"> <b>EMAIL ADDRESS:</b> </td>
                                            <td align="right" style="font-size:9px;"> <b> PHILRICE TEXT CENTER:</b> </td>
                                        </tr>
                                        <tr>
                                            <td align="left" style="font-size:8px;"> rcefseedsprogram@gmail.com </td>
                                            <td align="right" style="font-size:8px;"> 0917-111-7423 </td>
                                        </tr>
                                        

                                    </table> --}}
                                </td>
                            </tr>
                    </table>



                    <img src="{{public_path('images/id_footer.png')}}" style=" height:30px;width:100%;margin-left:0px;margin-top:0px;"/>

                </div>
            </td>


                
        </tr>

        {{-- @endforeach --}}
        
    </table> 


 



  

</body>
</html>
    
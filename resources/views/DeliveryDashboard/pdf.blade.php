<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title></title>
  </head>
  <body>

      @include('DeliveryDAshboard.includes.header_v2')
   
      @if($region == "CENTRAL LUZON")
       @include('DeliveryDAshboard.includes.body_v2_r3')
      @else
        @include('DeliveryDAshboard.includes.body_v2')
      @endif
    
      {{-- <div style="page-break-before: always;"></div>
      @include('DeliveryDAshboard.delivery_pdf_v2') --}}

      <div style="page-break-before: always;"></div>
      @include('DeliveryDAshboard.delivery_pdf_v3')


    
      <div style="page-break-before: always;"></div>
       @include('DeliveryDAshboard.delivery_sar')

 
  </body>
</html>

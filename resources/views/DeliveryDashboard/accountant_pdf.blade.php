<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title></title>
    <style>
      footer {
        font-family: 'Arial Narrow';
        position: absolute; 
        bottom: -60px; 
        left: 0px; 
        right: 0px;
        height: 100px; 

        /** Extra personal styles **/
        color: grey;
        font-weight: normal;
        font-style: italic;
        font-size: 12px;
        text-align: right;
      }
    </style>
  </head>
  <body>
      @include('DeliveryDAshboard.includes.header')
      @include('DeliveryDAshboard.includes.acc_iar_body')
      <footer>
        PhilRice RCEF Seed IAR Rev 00 Effectivity Date: 01 February 2020
      </footer>
  </body>
</html>

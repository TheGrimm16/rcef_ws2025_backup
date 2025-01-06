<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Coop ID</title>

    <link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
    
    <style type="text/css" media="screen,print">
        .new-page {
            page-break-before: always;
        }
    </style>

    <style>
        textarea {
            vertical-align: middle;
        }

        .bag{
            width: 80px;
            height: 68px;
            display: inline-block;
            border: 3px solid black;
            padding-top: 21px;
            text-align: center;
        }
        .bag_no{
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 0;
        }

        .bag_row{
            display:inline-block;
        }
        .custom_block{
            display: inline-block;
            margin-bottom: 0;
        }
    </style>
</head>
<body style=" padding: 10px;"> 
    <div class="row">
        <div class="col-xs-2">
            <img class="qr_class" src="data:image/png;base64, {{ base64_encode(QrCode::format('png')->size(180)->margin(0)->generate("ARMM-N-2/21-Rcl-25955")) }} "><br>
            <span><strong>Upian Agri Pinoy Farmers Producer Cooperative</strong></span>
        </div>

        <div class="col-xs-2">
            <img class="qr_class" src="data:image/png;base64, {{ base64_encode(QrCode::format('png')->size(180)->margin(0)->generate("01-N-6/19-Rcl-25047")) }} "><br>
            <span><strong>Seed Growers Multi-Purpose Cooperative of La Union</strong></span>
        </div>

        <div class="col-xs-2">
            <img class="qr_class" src="data:image/png;base64, {{ base64_encode(QrCode::format('png')->size(180)->margin(0)->generate("01-R-6/19-Rcl-23082")) }} "><br>
            <span><strong>Pangasinan Organic Seed Growers and Nursery Multi-Purpose Cooperative</strong></span>
        </div>

        <div class="col-xs-2">
            <img class="qr_class" src="data:image/png;base64, {{ base64_encode(QrCode::format('png')->size(180)->margin(0)->generate("10-R-9/21-Rcl-24477")) }} "><br>
            <span><strong>Lanao Del Norte Seed Growers Multi-Purpose Cooperative</strong></span>
        </div>

        <div class="col-xs-2">
            <img class="qr_class" src="data:image/png;base64, {{ base64_encode(QrCode::format('png')->size(180)->margin(0)->generate("12-R-3/21-Rcl-18172")) }} "><br>
            <span><strong>Lambayong Grains Seed Producers Multi-Purpose Cooperative</strong></span>
        </div>

        <div class="col-xs-2">
            <img class="qr_class" src="data:image/png;base64, {{ base64_encode(QrCode::format('png')->size(180)->margin(0)->generate("02-R-5/22-Rcl-21302")) }} "><br>
            <span><strong>Isabela Grains Production and Marketing Cooperative</strong></span>
        </div>

        <div class="col-xs-2">
            <img class="qr_class" src="data:image/png;base64, {{ base64_encode(QrCode::format('png')->size(180)->margin(0)->generate("12-R-7/20-Rcl-18285")) }} "><br>
            <span><strong>Cotabato Agricultural Allied Services Cooperative</strong></span>
        </div>

        <div class="col-xs-2">
            <img class="qr_class" src="data:image/png;base64, {{ base64_encode(QrCode::format('png')->size(180)->margin(0)->generate("10-R-3/21-Rcl-24297")) }} "><br>
            <span><strong>Bukidnon Seed Growers Association (BUSGA) Producers Cooperative</strong></span>
        </div>
    </div>
    

    <script src=" {{ asset('public/js/jquery.min.js') }} "></script>
    <script src=" {{ asset('public/js/bootstrap.min.js') }} "></script>
</body>
</html>
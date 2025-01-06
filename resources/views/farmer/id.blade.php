<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Farmer ID</title>

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
    <?php $i = 1; ?>
    @foreach ($id_list as $list)
        @if($i == 2)
            <div class="row" style="border: 1px solid black;border-left:0;border-right:0;
            padding: 20px;">
                <div class="col-xs-2" style="width: 165px;margin-right:10px;">
                        <img src="{{ asset('public/images/plogo.jpg') }}" style="height: 150px;
                        width: 140px;margin-top: 0;" alt=""><br><br>
                        <center>
                            <img class="qr_class" src="data:image/png;base64, {{ base64_encode(QrCode::format('png')->size(150)->margin(0)->generate("$list->generatedID")) }} "><br>
                            <span><strong>{{ $list->generatedID }}</strong></span>
                        </center>
                </div>
                <div class="col-xs-10" class="custom_block" style="width: 87%;">
                    <p class="display:inline-block">
                        <textarea name="" id="" rows="15" class="form-control" style="border: 3px solid black;resize:none;"></textarea>
                    </p>

                    <p class="custom_block">
                        <span style="font-size: 20px;font-weight: 600;">Seed Variety:</span> <textarea name="" id="" cols="30" rows="3" style="border: 3px solid black;resize:none;margin-right:20px;"></textarea>
                    </p>
                    
                    <div class="custom_block">                      
                        <span style="font-size: 20px;font-weight: 600;">Bags Received:</span>                         
                        <div class="bag" style="margin-left:4px;">
                            <span class="bag_no">1</span>
                        </div>
                        <div class="bag">
                            <span class="bag_no">2</span>
                        </div>
                        <div class="bag">
                            <span class="bag_no">3</span>
                        </div>
                        <div class="bag">
                            <span class="bag_no">4</span>
                        </div>
                    </div>   
                </div>
            </div> 
            <div class="new-page"></div>
            <?php $i = 1; ?>
        @else
            <div class="row" style="border: 1px solid black;border-left:0;border-right:0;
            padding: 20px;">
                <div class="col-xs-2" style="width: 165px;margin-right:10px;">
                        <img src="{{ asset('public/images/plogo.jpg') }}" style="height: 150px;
                        width: 140px;margin-top: 0;" alt=""><br><br>
                        <center>
                            <img class="qr_class" src="data:image/png;base64, {{ base64_encode(QrCode::format('png')->size(150)->margin(0)->generate("$list->generatedID")) }} "><br>
                            <span><strong>{{ $list->generatedID }}</strong></span>
                        </center>
                </div>
                <div class="col-xs-10" class="custom_block" style="width: 87%;">
                    <p class="display:inline-block">
                        <textarea name="" id="" rows="15" class="form-control" style="border: 3px solid black;resize:none;"></textarea>
                    </p>

                    <p class="custom_block">
                        <span style="font-size: 20px;font-weight: 600;">Seed Variety:</span> <textarea name="" id="" cols="30" rows="3" style="border: 3px solid black;resize:none;margin-right:20px;"></textarea>
                    </p>
                    
                    <div class="custom_block">                      
                        <span style="font-size: 20px;font-weight: 600;">Bags Received:</span>                         
                        <div class="bag" style="margin-left:4px;">
                            <span class="bag_no">1</span>
                        </div>
                        <div class="bag">
                            <span class="bag_no">2</span>
                        </div>
                        <div class="bag">
                            <span class="bag_no">3</span>
                        </div>
                        <div class="bag">
                            <span class="bag_no">4</span>
                        </div>
                    </div>   
                </div>
            </div>
            <?php $i = $i + 1; ?>
        @endif
                       
    @endforeach
   
   

    <script src=" {{ asset('public/js/jquery.min.js') }} "></script>
    <script src=" {{ asset('public/js/bootstrap.min.js') }} "></script>
</body>
</html>
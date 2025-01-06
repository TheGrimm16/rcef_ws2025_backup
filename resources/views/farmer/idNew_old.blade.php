<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Farmer ID</title>
    <style>
        .bag{
            width: 310px;
            height: 35px;
            display: inline-block;
            border: 3px solid black;
            padding-top: 12px;
            text-align: center;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 0;
            margin-right: 5px;
        }

        .variety{
            width: 300px;
            height: 40px;
            display: inline-block;
            border: 3px solid black;
            padding-top: 11px;
            text-align: center;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 0;
            margin-right: 5px;
        }

        .bag_row{
            display:inline-block;
        }

        @page {
            margin:0px;
        }

    </style>
</head>
<body style=" padding: 15px;">
    @foreach($id_list as $list)
    <table style="width:100%;border: 1px solid black;border-left:0;border-right:0;">
        <tr>
            <td style="width:14%;">
                <img src="{{ public_path('images/plogo.jpg')}}" style="height: 120px;width: 120px;" alt=""><br><br>
                <img class="qr_class" src="data:image/png;base64, {{ base64_encode(QrCode::format('png')->size(135)->margin(0)->generate("$list->generatedID")) }} "><br>
                <span><strong>{{ $list->generatedID }}</strong></span>
            </td>
            <td>
                <br>
                <p style="margin-bottom: 0;margin-top:20px;">
                    <!--<textarea name="" style="border: 3px solid black;resize:none;width:100%;height:250px;"></textarea><br>-->
                    <div style="border: 3px solid black;width:100%;height:250px;margin-bottom:25px;">
                        <br><br><br><br><br><br><br><br><br><br><br>
                        <span style="margin-left:10px;">RSBSA Number: _____________________________</span>  <span style="margin-left:10px;">Area: __________________</span> <span style="margin-left:10px;">Sex: __________________</span> 
                    </div>
                    <span style="font-size: 20px;font-weight: 600;vertical-align: middle;margin-top:-10px;">Seed Variety: </span> 
                    <span class="variety"></span>  <span style="font-size: 20px;font-weight: 600;vertical-align: middle;">Bags Received:</span> <span class="bag"></span>
                </p>
            </td>
        </tr>
    </table>

    <!--<div class="new-page"></div>-->
    @endforeach
</body>
</html>
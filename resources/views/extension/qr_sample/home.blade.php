<!doctype html>
<head>
    <title>QR Sample</title>
</head>
<body style=" padding: 10px;">
    <img class="qr_class" src="data:image/jpg;base64, {{ base64_encode(QrCode::format('png')->merge('https://banner2.cleanpng.com/20180625/ez/kisspng-computer-icons-google-calendar-time-attendance-c-5b30fbc86143a3.5198751415299368403984.jpg', .3, true)->margin(0)->size(310)->generate("sample")) }} ">
</body>
</html>

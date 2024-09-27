<!DOCTYPE html>
<html>
<head>
    <title>Pagamento via Pix</title>
</head>
<body>
    <h1>Pagamento via Pix</h1>
    <p>Escaneie o QR Code abaixo para realizar o pagamento:</p>
    <img src="data:image/png;base64,{{ base64_encode(QrCode::format('png')->size(300)->generate($qrcode)) }}" alt="QR Code Pix">
    <p>Ou copie e cole o código abaixo no seu aplicativo de pagamento:</p>
    <textarea rows="5" cols="50">{{ $qrcode }}</textarea>
</body>
</html>

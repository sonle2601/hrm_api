<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác minh Email Laravel với OTP</title>
</head>

<body>
    <h1>Xin cảm ơn bạn đã đăng ký với chúng tôi!</h1>

    <p>Người dùng thân mến,</p>

    <p>Cảm ơn bạn đã đăng ký tài khoản.</p>

    <p>Vui lòng nhập mã xác minh vào ô được cung cấp:</p>

    <p style="background-color: black; color: white; padding: 10px;width: 50px;">{{ $validToken }}</p>

    <p>Mã xác minh sẽ có hiệu lực trong 1 phút. Xin đừng tiết lộ cho bất cứ ai.</p>

    <p>Nếu bạn không phải là người nhận tin nhắn này, vui lòng xóa nó khỏi thiết bị của bạn.</p>

    <p>Cảm ơn {{ $get_user_name }},</p>
    <p></p>
</body>

</html>

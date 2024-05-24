<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Verify Your Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            border: 2px solid #000000;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            color: white;
            padding: 15px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
        }

        .header img {
            width: 100px;
            height: auto;
            margin-bottom: 10px;
        }

        .content {
            padding: 20px;
        }

        .content p {
            font-size: 16px;
            line-height: 1.5;
        }

        .content h6 {
            font-size: 6px;
            color: red;
            margin-top: 5px;
        }

        .content h5 {
            font-size: 9px;
            color: white;
        }

        .otp {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            padding: 10px;
            background-color: #f4f4f4;
            border-radius: 4px;
        }

        .footer {
            background-color: #f4f4f4;
            padding: 10px;
            text-align: center;
            font-size: 12px;
            color: #555555;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('logo/eduskill.png') }}" alt="Eduskill Logo">
            <h1>Service Forgot Password</h1>
        </div>
        <div class="content">
            <p>Hi {{ $get_user_name }} !</p>
            <p>Please verify your forgot email password by entering the OTP code below:</p>
            <div class="otp">{{ $validTokenForgot }}</div>
            <p>The code will be expire in 1 hour!</p>
            <br>
            <p> Thank you for your loyalty in using Eduskill!</p>
            <h6>*If you didn't request this, please ignore this email.</h6>
            <br>
            <h5>Regards</h5>
            <h5>Eduskill</h5>
        </div>
        <div class="footer">
            <p>If you encounter any issues, please contact eduskillverif@gmail.com.</p>
        </div>
    </div>
</body>

</html>
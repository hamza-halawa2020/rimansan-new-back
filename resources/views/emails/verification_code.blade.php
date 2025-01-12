<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Verification Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            color: #333;
            padding: 20px;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            max-width: 600px;
            margin: auto;
        }

        h2 {
            color: #1d72b8;
        }

        .code {
            font-size: 30px;
            font-weight: bold;
            color: #1d72b8;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Verification Code</h2>
        <p>Dear {{ $user->name }},</p>
        <p>
            We have received a request to verify your email address. Please
            use the verification code below to complete the process:
        </p>
        <p class="code">{{ $verificationCode }}</p>
        <p>This verification code will expire in 5 minutes.</p>
        <div class="footer">
            <p>If you didn't request this, please ignore this email.</p>
        </div>
    </div>
</body>

</html>

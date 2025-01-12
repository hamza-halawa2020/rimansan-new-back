<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Password Reset</title>

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
        <h2>Password Reset</h2>


        <p>You requested a password reset. Click the link below to reset your password:</p>

        <p class="code">

            <a href="{{ url('/password-reset?token=' . $token) }}">Reset Password</a>

        </p>

        <div class="footer">
            <p>If you didn't request this, please ignore this email.</p>
        </div>





    </div>
</body>

</html>

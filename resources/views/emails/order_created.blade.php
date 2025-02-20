<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order Created</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            text-align: center;
        }

        p {
            font-size: 16px;
            color: #555;
        }

        .order-details {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        ul li {
            padding: 8px 0;
            font-size: 16px;
            color: #333;
        }

        ul li strong {
            color: #000;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>New Order Created</h1>
        <p>A new order has been created with the following details:</p>

        <div class="order-details">
            <ul>
                <li><strong>Order Number:</strong> {{ $order->order_number }}</li>
                <li><strong>Customer Name:</strong> {{ $order->user->name ?? $order->client->name }}</li>
                <li><strong>Total Price:</strong> ${{ number_format($order->total_price, 2) }}</li>
                <li><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</li>
                <li><strong>Status:</strong> <span style="color: {{ $order->status == 'Completed' ? 'green' : 'red' }};">
                        {{ ucfirst($order->status) }}
                    </span>
                </li>
            </ul>
        </div>

        <p>Thank you for using our application!</p>

        <div class="footer">
            &copy; {{ date('Y') }} Riman-San. All rights reserved.
        </div>
    </div>
</body>

</html>

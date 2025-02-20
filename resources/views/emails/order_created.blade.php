<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order Created</title>
</head>

<body>
    <h1>New Order Created</h1>
    <p>Hello Admin,</p>
    <p>A new order has been created with the following details:</p>

    <ul>
        <li><strong>Order Number:</strong> {{ $order->order_number }}</li>
        <li><strong>Customer Name:</strong> {{ $order->user->name ?? $order->client->name }}</li>
        <li><strong>Total Price:</strong> {{ $order->total_price }}</li>
        <li><strong>Payment Method:</strong> {{ $order->payment_method }}</li>
        <li><strong>Status:</strong> {{ $order->status }}</li>
    </ul>

    <p>Thank you for using our application!</p>
</body>

</html>

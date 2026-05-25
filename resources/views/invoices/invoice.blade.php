<!DOCTYPE html>
<html>
<head>
    <title>Invoice</title>

    <style>
        body{
            font-family: Arial, sans-serif;
        }

        th{
            background-color: #f3f3f3;
        }
        p{
            margin-bottom: 8px;
        }

        table{
            margin-top: 20px;
        }

        .container{
            width: 100%;
            padding: 20px;
        }

        .title{
            font-size: 28px;
            margin-bottom: 20px;
        }

        table{
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td{
            border: 1px solid #ddd;
        }

        th, td{
            padding: 10px;
            text-align: left;
        }
    </style>
</head>
<body>

<div class="container">

    <div style="margin-bottom: 30px;">
        <h2>Booking</h2>
        <p>Online Booking System</p>
    </div>

    <h1 class="title">Booking Invoice</h1>

    <p>
        <strong>Invoice Number:</strong>
        {{ $booking->invoice_number }}
    </p>

    <p>
        <strong>Invoice Type:</strong>

        {{
            match($booking->payment_status->value) {
                'partial' => 'Partial Payment Invoice',
                'paid' => 'Full Payment Invoice',
                default => 'Pending Payment Invoice',
            }
        }}
    </p>

    <p>
        <strong>Invoice Date:</strong>
        {{ now()->format('d M Y') }}
    </p>

    <p>
        <strong>Booking Date:</strong>
        {{ $booking->created_at->format('d M Y h:i A') }}
    </p>

    <p>
        <strong>Payment Date:</strong>
        {{ $payment->paid_at->format('d M Y h:i A') }}
    </p>

    <p>
        <strong>Payment Method:</strong>
        {{ ucfirst($payment->payment_method->value) }}
    </p>

    <p>
        Booking ID:
        {{ $booking->id }}
    </p>

    <p>
        Customer:
        {{ $booking->user->name }}
    </p>

    <table>
        <tr>
            <th>Property</th>
            <td>{{ $booking->property->name }}</td>
        </tr>

        <tr>
            <th>Room Number</th>
            <td>{{ $booking->room->number }}</td>
        </tr>

        <tr>
            <th>Number of Nights</th>
            <td>{{ $booking->nights_count }}</td>
        </tr>

        <tr>
            <th>Original Booking Price</th>
            <td>{{ number_format($booking->original_price, 2) }} EGP</td>
        </tr>

        @if($booking->discount_amount)
        <tr>
            <th>Discount Amount</th>
            <td>- {{ number_format($booking->discount_amount, 2) }} EGP</td>
        </tr>
        @endif

        <tr>
            <th>Final Total Booking Price</th>
            <td>{{ number_format($booking->total_price, 2) }} EGP</td>
        </tr>

        <tr>
            <th>Paid Amount:</th>
            <td>{{ number_format($payment->amount,2) }} EGP</td>
        </tr>

        <tr>
            <th>Remaining Amount:</th>
            <td>{{ number_format($payment->remaining,2) }} EGP</td>
        </tr>


        <tr>
            <th>Payment Status</th>
            <td>{{ ucfirst($payment->status->value) }}</td>
        </tr>

        <tr>
            <th>Transaction ID</th>
            <td>{{ $payment->transaction_id }}</td>
        </tr>

        <tr>
            <th>Status</th>
            <td>{{ ucfirst($booking->status->value) }}</td>
        </tr>

        <tr>
            <th>Check-in Date</th>
            <td>
                {{ $booking->check_in->format('d F Y \a\t h:i:s a') }}
            </td>
        </tr>

        <tr>
            <th>Check-out Date</th>
            <td>
                {{ $booking->check_out->format('d F Y \a\t h:i:s a') }}
            </td>
        </tr>
    </table>

</div>

<div style="margin-top: 40px; text-align:center; color:gray;">
    <p>Thank you for booking with us.</p>
</div>

</body>
</html>

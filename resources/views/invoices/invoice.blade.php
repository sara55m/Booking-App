<!DOCTYPE html>
<html>
<head>
    <title>Booking Invoice</title>

    <style>
        body{
            font-family: Arial, sans-serif;
            color:#333;
        }

        .container{
            width:100%;
            padding:20px;
        }

        h1{
            margin-bottom:5px;
        }

        h2{
            margin-top:35px;
            margin-bottom:10px;
            color:#444;
            border-bottom:2px solid #f3f3f3;
            padding-bottom:6px;
        }

        p{
            margin:6px 0;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-top:10px;
        }

        table,th,td{
            border:1px solid #ddd;
        }

        th{
            background:#f5f5f5;
            width:40%;
            text-align:left;
        }

        th,td{
            padding:10px;
        }

        .footer{
            margin-top:40px;
            text-align:center;
            color:#777;
            font-size:14px;
        }
    </style>
</head>
<body>

<div class="container">

    <div style="margin-bottom:30px;">
        <h2>Booking</h2>
        <p>Online Booking System</p>
    </div>

    <h1>Booking Invoice</h1>

    <p><strong>Invoice Number:</strong> {{ $booking->invoice_number }}</p>

    <p>
        <strong>Invoice Type:</strong>

        {{
            match($booking->payment_status){
                \App\Enums\BookingPaymentStatus::PARTIAL => 'Partial Payment Invoice',
                \App\Enums\BookingPaymentStatus::PAID => 'Full Payment Invoice',
                \App\Enums\BookingPaymentStatus::REFUNDED => 'Refund Invoice',
                default => 'Pending Payment Invoice',
            }
        }}
    </p>

    @php
    $isRefund = $booking->payment_status === \App\Enums\BookingPaymentStatus::REFUNDED;
    @endphp

    <p><strong>Invoice Date:</strong> {{ now()->format('d M Y') }}</p>

    <hr>

    <h2>Booking Details</h2>

    <table>

        <tr>
            <th>Booking Reference</th>
            <td>{{ $booking->reference }}</td>
        </tr>

        <tr>
            <th>Customer</th>
            <td>{{ $booking->user->name }}</td>
        </tr>

        <tr>
            <th>Property</th>
            <td>{{ $booking->property->name }}</td>
        </tr>

        <tr>
            <th>Room Number</th>
            <td>{{ $booking->room->number }}</td>
        </tr>

        <tr>
            <th>Room Type</th>
            <td>{{ $booking->room->roomType->name }}</td>
        </tr>

        <tr>
            <th>Booking Date</th>
            <td>{{ $booking->created_at->format('d M Y h:i A') }}</td>
        </tr>

        <tr>
            <th>Check-in</th>
            <td>{{ $booking->check_in->format('d F Y h:i A') }}</td>
        </tr>

        <tr>
            <th>Check-out</th>
            <td>{{ $booking->check_out->format('d F Y h:i A') }}</td>
        </tr>

        <tr>
            <th>Number of Nights</th>
            <td>{{ $booking->nights_count }}</td>
        </tr>

        <tr>
            <th>Booking Status</th>
            <td>{{ ucfirst($booking->status->value) }}</td>
        </tr>

    </table>

    <h2>Pricing Summary</h2>

    <table>

        <tr>
            <th>Original Booking Price</th>
            <td>{{ number_format($booking->original_price,2) }} EGP</td>
        </tr>

        @if($booking->discount_amount > 0)
        <tr>
            <th>Offer Discount</th>
            <td>- {{ number_format($booking->discount_amount,2) }} EGP</td>
        </tr>
        @endif

        <tr>
            <th>Final Booking Price</th>
            <td>{{ number_format($booking->total_price,2) }} EGP</td>
        </tr>

        @if($isRefund)
            <tr>
                <th>Total Refunded</th>
                <td>{{ number_format($totalRefunded,2) }} EGP</td>
            </tr>
            
            <tr>
                <th>Final Balance</th>
                <td>0.00 EGP</td>
            </tr>
        @else

            <tr>
                <th>Payment Portion</th>
                <td>{{ number_format($portion,2) }} EGP</td>
            </tr>

            @if($payment->discount_amount > 0)
            <tr>
                <th>Reward Points Discount</th>
                <td>- {{ number_format($payment->discount_amount,2) }} EGP</td>
            </tr>
            @endif

            <tr>
                <th>Amount Charged</th>
                <td>{{ number_format($payment->amount,2) }} EGP</td>
            </tr>

            <tr>
                <th>Total Paid So Far</th>
                <td>{{ number_format($totalPaid,2) }} EGP</td>
            </tr>

            <tr>
                <th>Remaining Balance</th>
                <td>{{ number_format($payment->remaining,2) }} EGP</td>
            </tr>
        @endif

    </table>

    <h2>Payment Information</h2>

    <table>

        <tr>
            <th>Latest Payment Date</th>
            <td>{{ $payment->paid_at->format('d M Y h:i A') }}</td>
        </tr>

        <tr>
            <th>Payment Method</th>
            <td>{{ ucfirst($payment->payment_method->value) }}</td>
        </tr>
        @if($isRefund)
            <tr>
                <th>Refund Status</th>
                <td>Refunded</td>
            </tr>
            
            <tr>
                <th>Refund Date</th>
                <td>{{ $payment->refunded_at?->format('d M Y h:i A') }}</td>
            </tr>
        @else

            <tr>
                <th>Payment Status</th>
                <td>{{ ucfirst($payment->status->value) }}</td>
            </tr>

            @if($payment->transaction_id)
            <tr>
                <th>Transaction ID</th>
                <td>{{ $payment->transaction_id }}</td>
            </tr>
            @endif
        @endif

    </table>

    @if($totalEarnedPoints > 0 || $totalRedeemedPoints > 0)

    <h2>Rewards</h2>

    <table>
        @if($isRefund)
            @if($totalEarnedPoints > 0)
            <tr>
                <th>Total Reward Points Reversed</th>
                <td>{{ number_format($totalEarnedPoints) }}</td>
            </tr>
            @endif

            @if($totalRedeemedPoints > 0)
            <tr>
                <th>Total Reward Points Returned</th>
                <td>{{ number_format($totalRedeemedPoints) }}</td>
            </tr>
            @endif
        @else

            @if($payment->earned_points > 0)
            <tr>
                <th>Reward Points Earned</th>
                <td>{{ number_format($payment->earned_points) }}</td>
            </tr>
            @endif

            @if($payment->redeemed_points > 0)
            <tr>
                <th>Reward Points Redeemed</th>
                <td>{{ number_format($payment->redeemed_points) }}</td>
            </tr>
            @endif
        @endif

        <tr>
            <th>Current Reward Points Balance</th>
            <td>{{ $currentRewardBalance}}</td>
        </tr>

    </table>

    @endif

</div>

<div class="footer">
    @if($isRefund)

        <p><strong>Your booking has been cancelled and refunded.</strong></p>

        <p>
        This invoice serves as confirmation of your refund.
        </p>

    @else

        <p><strong>Thank you for booking with us.</strong></p>

        <p>
            This invoice serves as proof of payment for your booking.
        </p>

        <p>
            Please keep this invoice for your records.
        </p>
    @endif

</div>

</body>
</html>

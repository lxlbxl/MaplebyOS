<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Quote #{{ $quote->quote_number }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            line-height: 1.6;
        }

        .header {
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .company-info h1 {
            color: #10b981;
            /* Brand color */
            margin: 0;
        }

        .invoice-details {
            text-align: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background: #f3f4f6;
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .totals {
            text-align: right;
        }

        .totals-table {
            width: 300px;
            margin-left: auto;
        }

        .footer {
            margin-top: 50px;
            font-size: 0.9em;
            color: #666;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <table style="border: none;">
            <tr>
                <td style="border: none;">
                    <div class="company-info">
                        <h1>ADGOD OS</h1>
                        <p>123 Shipping Lane<br>Logistics City, LC 45678<br>contact@adgodos.com</p>
                    </div>
                </td>
                <td style="border: none; text-align: right;">
                    <h2>QUOTE</h2>
                    <p>
                        <strong>Quote #:</strong> {{ $quote->quote_number }}<br>
                        <strong>Date:</strong> {{ $quote->issue_date->format('M d, Y') }}<br>
                        <strong>Valid Until:</strong>
                        {{ $quote->expiry_date ? $quote->expiry_date->format('M d, Y') : 'N/A' }}
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <div class="client-info">
        <h3>Prepare For:</h3>
        <p>
            <strong>{{ $quote->client->company_name }}</strong><br>
            {{ $quote->client->contact_person }}<br>
            {{ $quote->client->address }}<br>
            {{ $quote->client->city }}, {{ $quote->client->country }}
        </p>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Description</th>
                <th style="width: 80px; text-align: center;">Qty</th>
                <th style="width: 100px; text-align: right;">Price</th>
                <th style="width: 100px; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td style="text-align: center;">{{ $item->quantity }}</td>
                    <td style="text-align: right;">${{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align: right;">${{ number_format($item->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table class="totals-table">
            <tr>
                <td><strong>Subtotal:</strong></td>
                <td>${{ number_format($quote->subtotal, 2) }}</td>
            </tr>
            @if($quote->tax_total > 0)
                <tr>
                    <td><strong>Tax:</strong></td>
                    <td>${{ number_format($quote->tax_total, 2) }}</td>
                </tr>
            @endif
            <tr style="font-size: 1.2em;">
                <td style="border-top: 2px solid #333;"><strong>Total:</strong></td>
                <td style="border-top: 2px solid #333;"><strong>${{ number_format($quote->total_amount, 2) }}</strong>
                </td>
            </tr>
        </table>
    </div>

    @if($quote->terms_conditions)
        <div class="terms">
            <h3>Terms & Conditions</h3>
            <p>{!! nl2br(e($quote->terms_conditions)) !!}</p>
        </div>
    @endif

    <div class="footer">
        <p>Thank you for your business!</p>
    </div>
</body>

</html>
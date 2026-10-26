<!DOCTYPE html>
<html>

<head>
    <title>Quote #{{ $quote->quote_number }}</title>
</head>

<body>
    <p>Dear {{ $quote->client->contact_person }},</p>

    <p>Please find attached your quote #{{ $quote->quote_number }} from ADGOD OS.</p>

    <p>
        <strong>Total Amount:</strong> ${{ number_format($quote->total_amount, 2) }}<br>
        <strong>Valid Until:</strong> {{ $quote->expiry_date ? $quote->expiry_date->format('M d, Y') : 'N/A' }}
    </p>

    <p>If you have any questions, please feel free to contact us.</p>

    <p>Best regards,<br>
        ADGOD OS Team</p>
</body>

</html>
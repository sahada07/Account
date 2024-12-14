
<!-- resources/views/pdfs/payment-receipt.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt {{ $payment->payment_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .receipt-info {
            margin-bottom: 30px;
        }
        .payment-details {
            margin: 30px 0;
            padding: 20px;
            border: 1px solid #ddd;
            background-color: #f8f9fa;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PAYMENT RECEIPT</h1>
        <p>{{ config('app.name', 'Your Company Name') }}</p>
    </div>

    <div class="receipt-info">
        <p><strong>Receipt Number:</strong> {{ $payment->payment_number }}</p>
        <p><strong>Date:</strong> {{ $payment->payment_date->format('Y-m-d') }}</p>
        <p><strong>Payment Method:</strong> {{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}</p>
        @if($payment->reference_number)
            <p><strong>Reference Number:</strong> {{ $payment->reference_number }}</p>
        @endif
    </div>

    <div class="payment-details">
        <h3>Payment Details</h3>
        <p><strong>Amount Paid:</strong> ${{ number_format($payment->amount, 2) }}</p>
        <p><strong>Applied To:</strong> 
            @if($payment->payable_type === 'App\Models\Invoice')
                Invoice #{{ $payment->payable->invoice_number }}
            @else
                Bill #{{ $payment->payable->bill_number }}
            @endif
        </p>
        <p><strong>Paid By:</strong>
            @if($payment->payable_type === 'App\Models\Invoice')
                {{ $payment->payable->customer->name }}
            @else
                {{ $payment->payable->vendor->name }}
            @endif
        </p>
    </div>

    @if($payment->notes)
        <div class="notes">
            <h3>Notes:</h3>
            <p>{{ $payment->notes }}</p>
        </div>
    @endif

    <div class="footer">
        <p>This is an official receipt of payment. Thank you for your business!</p>
    </div>
</body>
</html>
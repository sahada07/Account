
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
        }
        .header {
            text-align: right;
            margin-bottom: 30px;
        }
        .invoice-info {
            margin-bottom: 30px;
        }
        .customer-info {
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
        }
        .totals {
            text-align: right;
            margin-top: 30px;
        }
        .total-row {
            font-weight: bold;
        }
        .notes {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>INVOICE</h1>
        <p>{{ config('app.name', 'Your Company Name') }}</p>
        <p>Invoice #: {{ $invoice->invoice_number }}</p>
        <p>Date: {{ $invoice->invoice_date->format('Y-m-d') }}</p>
        <p>Due Date: {{ $invoice->due_date->format('Y-m-d') }}</p>
    </div>

    <div class="customer-info">
        <h3>Bill To:</h3>
        <p>{{ $invoice->customer->name }}</p>
        @if($invoice->customer->address)
            <p>{{ $invoice->customer->address }}</p>
        @endif
        @if($invoice->customer->email)
            <p>{{ $invoice->customer->email }}</p>
        @endif
        @if($invoice->customer->phone)
            <p>{{ $invoice->customer->phone }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Tax Rate</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ number_format($item->quantity, 2) }}</td>
                    <td>${{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ $item->tax_rate }}%</td>
                    <td>${{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <p>Subtotal: ${{ number_format($invoice->subtotal, 2) }}</p>
        <p>Tax: ${{ number_format($invoice->tax_amount, 2) }}</p>
        <p class="total-row">Total: ${{ number_format($invoice->total, 2) }}</p>
        <p>Amount Paid: ${{ number_format($invoice->amount_paid, 2) }}</p>
        <p class="total-row">Balance Due: ${{ number_format($invoice->balance_due, 2) }}</p>
    </div>

    @if($invoice->notes)
        <div class="notes">
            <h3>Notes:</h3>
            <p>{{ $invoice->notes }}</p>
        </div>
    @endif

    <div class="footer">
        <p>Thank you for your business!</p>
    </div>
</body>
</html>
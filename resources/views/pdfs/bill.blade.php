<!-- resources/views/pdfs/bill.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bill {{ $bill->bill_number }}</title>
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
        .bill-info {
            margin-bottom: 30px;
        }
        .vendor-info {
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
        <h1>BILL</h1>
        <p>{{ config('app.name', 'Your Company Name') }}</p>
        <p>Bill #: {{ $bill->bill_number }}</p>
        <p>Date: {{ $bill->bill_date->format('Y-m-d') }}</p>
        <p>Due Date: {{ $bill->due_date->format('Y-m-d') }}</p>
    </div>

    <div class="vendor-info">
        <h3>From:</h3>
        <p>{{ $bill->vendor->name }}</p>
        @if($bill->vendor->address)
            <p>{{ $bill->vendor->address }}</p>
        @endif
        @if($bill->vendor->email)
            <p>{{ $bill->vendor->email }}</p>
        @endif
        @if($bill->vendor->phone)
            <p>{{ $bill->vendor->phone }}</p>
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
            @foreach($bill->items as $item)
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
        <p>Subtotal: ${{ number_format($bill->subtotal, 2) }}</p>
        <p>Tax: ${{ number_format($bill->tax_amount, 2) }}</p>
        <p class="total-row">Total: ${{ number_format($bill->total, 2) }}</p>
        <p>Amount Paid: ${{ number_format($bill->amount_paid, 2) }}</p>
        <p class="total-row">Balance Due: ${{ number_format($bill->balance_due, 2) }}</p>
    </div>

    @if($bill->notes)
        <div class="notes">
            <h3>Notes:</h3>
            <p>{{ $bill->notes }}</p>
        </div>
    @endif
</body>
</html>

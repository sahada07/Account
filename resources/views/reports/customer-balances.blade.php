<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Balances</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-4">Customer Balances</h2>

            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Customer</th>
                        <th class="px-6 py-3 text-right text-sm font-medium text-gray-700">Total Invoiced</th>
                        <th class="px-6 py-3 text-right text-sm font-medium text-gray-700">Total Paid</th>
                        <th class="px-6 py-3 text-right text-sm font-medium text-gray-700">Balance Due</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($customers as $customer)
                        <tr>
                            <td class="px-6 py-4">{{ $customer->name }}</td>
                            <td class="px-6 py-4 text-right">${{ number_format($customer->total_invoiced, 2) }}</td>
                            <td class="px-6 py-4 text-right">${{ number_format($customer->total_paid, 2) }}</td>
                            <td class="px-6 py-4 text-right">${{ number_format($customer->balance_due, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

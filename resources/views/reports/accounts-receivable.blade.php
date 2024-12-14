<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounts Receivable Aging</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-4">Accounts Receivable Aging</h2>

            <table class="min-w-full divide-y divide-gray-200 mb-6">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Aging Period</th>
                        <th class="px-6 py-3 text-right text-sm font-medium text-gray-700">Balance Due</th>
                    </tr>
                </thead>
                <tbody>
                    @if(array_sum($aging) > 0)
                        <tr>
                            <td class="px-6 py-4">Current</td>
                            <td class="px-6 py-4 text-right">${{ number_format($aging['current'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4">1-30 Days</td>
                            <td class="px-6 py-4 text-right">${{ number_format($aging['30days'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4">31-60 Days</td>
                            <td class="px-6 py-4 text-right">${{ number_format($aging['60days'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4">61+ Days</td>
                            <td class="px-6 py-4 text-right">${{ number_format($aging['90days'], 2) }}</td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="2" class="px-6 py-4 text-center text-gray-500">No aging data available.</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <h3 class="text-lg font-bold mb-4">Customer Balances</h3>
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Customer</th>
                        <th class="px-6 py-3 text-right text-sm font-medium text-gray-700">Total Due</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td class="px-6 py-4">{{ $customer->name }}</td>
                            <td class="px-6 py-4 text-right">${{ number_format($customer->total_due, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-6 py-4 text-center text-gray-500">No customer balances found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounts Payable Aging</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-4">Accounts Payable Aging</h2>

            <table class="min-w-full divide-y divide-gray-200 mb-6">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Aging Period</th>
                        <th class="px-6 py-3 text-right text-sm font-medium text-gray-700">Balance Due</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
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
                </tbody>
            </table>

            <h3 class="text-lg font-bold mb-4">Vendor Balances</h3>
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Customer</th>
                        <th class="px-6 py-3 text-right text-sm font-medium text-gray-700">Total Due</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($vendors as $vendor)
                        <tr>
                            <td class="px-6 py-4">{{ $vendor->name }}</td>
                            <td class="px-6 py-4 text-right">${{ number_format($vendor->total_due, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

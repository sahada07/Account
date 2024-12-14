<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Income Statement</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-4">Income Statement</h2>

            <p class="mb-6">For the period: {{ $startDate->format('Y-m-d') }} to {{ $endDate->format('Y-m-d') }}</p>

            @if($income == 0 && $expenses == 0)
                <p class="text-gray-500 text-center">No financial data available for the selected period.</p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-green-100 p-4 rounded-lg">
                        <h3 class="font-semibold text-lg mb-2">Income</h3>
                        <p class="text-2xl font-bold">${{ number_format($income, 2) }}</p>
                    </div>
                    <div class="bg-red-100 p-4 rounded-lg">
                        <h3 class="font-semibold text-lg mb-2">Expenses</h3>
                        <p class="text-2xl font-bold">${{ number_format($expenses, 2) }}</p>
                    </div>
                    <div class="bg-blue-100 p-4 rounded-lg">
                        <h3 class="font-semibold text-lg mb-2">Net Income</h3>
                        <p class="text-2xl font-bold">${{ number_format($netIncome, 2) }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</body>
</html>

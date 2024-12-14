<!-- resources/views/reports/index.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Financial Reports</h2>
                <a href="{{ route('dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Dashboard
                </a>
            </div>

            <!-- Reports Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Income Statement -->
                <div class="bg-white border rounded-lg p-6 hover:shadow-lg transition-shadow">
                    <h3 class="text-lg font-semibold mb-2">Income Statement</h3>
                    <p class="text-gray-600 mb-4">View your income, expenses, and profit/loss for any period.</p>
                    <a href="{{ route('reports.income-statement') }}" 
                       class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        View Report
                    </a>
                </div>

                <!-- Accounts Receivable Aging -->
                <div class="bg-white border rounded-lg p-6 hover:shadow-lg transition-shadow">
                    <h3 class="text-lg font-semibold mb-2">A/R Aging</h3>
                    <p class="text-gray-600 mb-4">Track overdue customer invoices and aging receivables.</p>
                    <a href="{{ route('reports.accounts-receivable') }}" 
                       class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        View Report
                    </a>
                </div>

                <!-- Accounts Payable Aging -->
                <div class="bg-white border rounded-lg p-6 hover:shadow-lg transition-shadow">
                    <h3 class="text-lg font-semibold mb-2">A/P Aging</h3>
                    <p class="text-gray-600 mb-4">Monitor vendor bills and payment schedules.</p>
                    <a href="{{ route('reports.accounts-payable') }}" 
                       class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        View Report
                    </a>
                </div>

                <!-- Tax Summary -->
                <div class="bg-white border rounded-lg p-6 hover:shadow-lg transition-shadow">
                    <h3 class="text-lg font-semibold mb-2">Tax Summary</h3>
                    <p class="text-gray-600 mb-4">Review collected and paid taxes for any period.</p>
                    <a href="{{ route('reports.tax-summary') }}" 
                       class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        View Report
                    </a>
                </div>

                <!-- Customer Balances -->
                <div class="bg-white border rounded-lg p-6 hover:shadow-lg transition-shadow">
                    <h3 class="text-lg font-semibold mb-2">Customer Balances</h3>
                    <p class="text-gray-600 mb-4">Summary of all customer account balances.</p>
                    <a href="{{ route('reports.customer-balances') }}" 
                       class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        View Report
                    </a>
                </div>

                <!-- Vendor Balances -->
                <div class="bg-white border rounded-lg p-6 hover:shadow-lg transition-shadow">
                    <h3 class="text-lg font-semibold mb-2">Vendor Balances</h3>
                    <p class="text-gray-600 mb-4">Overview of all vendor account balances.</p>
                    <a href="{{ route('reports.vendor-balances') }}" 
                       class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        View Report
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
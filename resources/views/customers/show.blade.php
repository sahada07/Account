<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Customer Details</h2>
                <a href="{{ route('customers.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Customers
                </a>
            </div>

            <!-- Customer Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Name</h3>
                    <p class="text-gray-900">{{ $customer->name }}</p>
                </div>

                <!-- Email -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Email</h3>
                    <p class="text-gray-900">{{ $customer->email ?? 'N/A' }}</p>
                </div>

                <!-- Phone -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Phone</h3>
                    <p class="text-gray-900">{{ $customer->phone ?? 'N/A' }}</p>
                </div>

                <!-- Tax Number -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Tax Number</h3>
                    <p class="text-gray-900">{{ $customer->tax_number ?? 'N/A' }}</p>
                </div>

                <!-- Address -->
                <div class="md:col-span-2">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Address</h3>
                    <p class="text-gray-900">{{ $customer->address ?? 'N/A' }}</p>
                </div>

                <!-- Status -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Status</h3>
                    <p class="text-gray-900">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $customer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $customer->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                </div>
            </div>

            <!-- Customer Statistics -->
            <div class="mt-6">
                <h3 class="text-lg font-medium text-gray-700 mb-4">Statistics</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Total Invoices</h4>
                        <p class="text-gray-900">{{ $statistics['total_invoices'] }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Total Amount</h4>
                        <p class="text-gray-900">${{ number_format($statistics['total_amount'], 2) }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Total Paid</h4>
                        <p class="text-gray-900">${{ number_format($statistics['total_paid'], 2) }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Total Due</h4>
                        <p class="text-gray-900">${{ number_format($statistics['total_due'], 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Customer Invoices -->
            <div class="mt-8">
                <h3 class="text-lg font-medium text-gray-700 mb-4">Recent Invoices</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance Due</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($customer->invoices as $invoice)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $invoice->invoice_number }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">${{ number_format($invoice->total, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">${{ number_format($invoice->balance_due, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ ucfirst($invoice->status) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No invoices found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

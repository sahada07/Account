<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Payment</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Record Payment</h2>
                <a href="{{ route(strtolower($payableType) . 's.show', $payable->id) }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to {{ ucfirst($payableType) }}
                </a>
            </div>

            @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Payable Summary -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">{{ ucfirst($payableType) }} Number</p>
                        <p class="font-semibold">{{ $payableType === 'Invoice' ? $payable->invoice_number : $payable->bill_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">{{ $payableType === 'Invoice' ? 'Customer' : 'Vendor' }}</p>
                        <p class="font-semibold">{{ $payableType === 'Invoice' ? $payable->customer->name : $payable->vendor->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Balance Due</p>
                        <p class="font-semibold text-red-600">${{ number_format($payable->balance_due, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <form action="{{ route('payments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="payable_type" value="{{ $payableType }}">
                <input type="hidden" name="payable_id" value="{{ $payable->id }}">
            
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Amount -->
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Payment Amount *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2">$</span>
                            <input type="number" 
                                   name="amount" 
                                   id="amount" 
                                   step="0.01" 
                                   max="{{ $payable->balance_due }}"
                                   value="{{ old('amount', $payable->balance_due) }}" 
                                   required
                                   class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Payment Date -->
                    <div>
                        <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">Payment Date *</label>
                        <input type="date" 
                               name="payment_date" 
                               id="payment_date" 
                               value="{{ old('payment_date', date('Y-m-d')) }}" 
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">Payment Method *</label>
                        <select name="payment_method" 
                                id="payment_method" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="check">Check</option>
                            <option value="credit_card">Credit Card</option>
                        </select>
                    </div>

                    <!-- Reference Number -->
                    <div>
                        <label for="reference_number" class="block text-sm font-medium text-gray-700 mb-2">Reference Number</label>
                        <input type="text" 
                               name="reference_number" 
                               id="reference_number" 
                               value="{{ old('reference_number') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Notes -->
                <div class="mt-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea name="notes" 
                              id="notes" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">{{ old('notes') }}</textarea>
                </div>

                <!-- Submit Button -->
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

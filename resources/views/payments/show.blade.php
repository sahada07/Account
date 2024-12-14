<!-- resources/views/payments/show.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details - {{ $payment->payment_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Payment Details</h2>
                <div class="flex space-x-2">
                    <a href="{{ route('payments.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to List
                    </a>
                    @if($payment->payable->status !== 'paid')
                        <a href="{{ route('payments.edit', $payment) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Edit Payment
                        </a>
                    @endif

                    <a href="{{ route('payments.pdf', $payment) }}" 
   class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
    Download Receipt
</a>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Payment Details -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Payment Information</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Payment Number:</span>
                            <span class="font-medium">{{ $payment->payment_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Amount:</span>
                            <span class="font-medium">${{ number_format($payment->amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Payment Date:</span>
                            <span class="font-medium">{{ $payment->payment_date->format('Y-m-d') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Payment Method:</span>
                            <span class="px-2 inline-flex text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}
                            </span>
                        </div>
                        @if($payment->reference_number)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Reference Number:</span>
                                <span class="font-medium">{{ $payment->reference_number }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Related Document -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Related Document</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Type:</span>
                            <span class="font-medium">{{ class_basename($payment->payable_type) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Document Number:</span>
                            <a href="{{ $payment->payable_type === 'App\Models\Invoice' 
                                        ? route('invoices.show', $payment->payable_id) 
                                        : route('bills.show', $payment->payable_id) }}" 
                               class="text-blue-600 hover:text-blue-900">
                                {{ $payment->payable_type === 'App\Models\Invoice' 
                                    ? $payment->payable->invoice_number 
                                    : $payment->payable->bill_number }}
                            </a>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ $payment->payable_type === 'App\Models\Invoice' ? 'Customer' : 'Vendor' }}:</span>
                            <span class="font-medium">
                                {{ $payment->payable_type === 'App\Models\Invoice' 
                                    ? $payment->payable->customer->name 
                                    : $payment->payable->vendor->name }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Document Status:</span>
                            <span class="px-2 inline-flex text-sm font-semibold rounded-full 
                                {{ $payment->payable->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $payment->payable->status === 'partial' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                {{ ucfirst($payment->payable->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if($payment->notes)
                <div class="mt-6">
                    <h3 class="text-lg font-semibold mb-2">Notes</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        {{ $payment->notes }}
                    </div>
                </div>
            @endif

            <!-- Delete Button -->
            @if($payment->payable->status !== 'paid')
                <div class="mt-6">
                    <form action="{{ route('payments.destroy', $payment) }}" method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this payment?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                            Delete Payment
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
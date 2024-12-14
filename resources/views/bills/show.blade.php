<!-- resources/views/bills/show.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill {{ $bill->bill_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow p-6">
            <!-- Header Actions -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Bill: {{ $bill->bill_number }}</h2>
                <div class="flex space-x-2">
                    <a href="{{ route('bills.index') }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to List
                    </a>
                    @if($bill->status === 'draft')
                        <a href="{{ route('bills.edit', $bill) }}" 
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Edit Bill
                        </a>
                        <form action="{{ route('bills.mark-as-received', $bill) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Mark as Received
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('bills.pdf', $bill) }}" 
                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                     Download PDF
                 </a>
                    
                </div>

            </div>

            <!-- Status Alert -->
            <div class="mb-6">
                <span class="px-4 py-2 rounded-full text-sm font-semibold
                    {{ $bill->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                    {{ $bill->status === 'received' ? 'bg-blue-100 text-blue-800' : '' }}
                    {{ $bill->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $bill->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                    Status: {{ ucfirst($bill->status) }}
                </span>
            </div>

            <!-- Bill Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <h3 class="text-lg font-semibold mb-2">Vendor Details</h3>
                    <div class="border rounded p-4">
                        <p class="font-bold">{{ $bill->vendor->name }}</p>
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
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-2">Bill Details</h3>
                    <div class="border rounded p-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-600">Bill Date:</p>
                                <p class="font-semibold">{{ $bill->bill_date->format('Y-m-d') }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Due Date:</p>
                                <p class="font-semibold">{{ $bill->due_date->format('Y-m-d') }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Amount Due:</p>
                                <p class="font-semibold">${{ number_format($bill->balance_due, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bill Items -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-2">Items</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tax Rate</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tax Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($bill->items as $item)
                                <tr>
                                    <td class="px-6 py-4">{{ $item->description }}</td>
                                    <td class="px-6 py-4">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="px-6 py-4">${{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-6 py-4">{{ number_format($item->tax_rate, 2) }}%</td>
                                    <td class="px-6 py-4">${{ number_format($item->tax_amount, 2) }}</td>
                                    <td class="px-6 py-4">${{ number_format($item->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-right font-medium">Subtotal:</td>
                                <td colspan="2" class="px-6 py-4 font-medium">${{ number_format($bill->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-right font-medium">Tax Total:</td>
                                <td colspan="2" class="px-6 py-4 font-medium">${{ number_format($bill->tax_amount, 2) }}</td>
                            </tr>
                            <tr class="bg-gray-100">
                                <td colspan="4" class="px-6 py-4 text-right font-bold">Total:</td>
                                <td colspan="2" class="px-6 py-4 font-bold">${{ number_format($bill->total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Payments -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-2">Payments</h3>
                @if($bill->payments->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($bill->payments as $payment)
                                    <tr>
                                        <td class="px-6 py-4">{{ $payment->payment_date->format('Y-m-d') }}</td>
                                        <td class="px-6 py-4">${{ number_format($payment->amount, 2) }}</td>
                                        <td class="px-6 py-4">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                        <td class="px-6 py-4">{{ $payment->reference_number ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500">No payments recorded yet.</p>
                @endif
            </div>

            <!-- Record Payment Button -->
            @if($bill->status === 'received' || $bill->status === 'partial')
                <div class="mt-6">
                    <a href="{{ route('payments.create', ['payable_type' => 'Bill', 'payable_id' => $bill->id]) }}"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                         Record Payment
                     </a>
                     
                </div>
            @endif

            <!-- Notes -->
            @if($bill->notes)
                <div class="mt-6">
                    <h3 class="text-lg font-semibold mb-2">Notes</h3>
                    <div class="border rounded p-4">
                        {{ $bill->notes }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
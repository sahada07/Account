<!-- resources/views/invoices/edit.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Invoice</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Edit Invoice #{{ $invoice->invoice_number }}</h2>
                <a href="{{ route('invoices.show', $invoice) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Invoice
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

            <form action="{{ route('invoices.update', $invoice) }}" method="POST" id="invoiceForm">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Customer Selection -->
                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-2">Customer *</label>
                        <select name="customer_id" 
                                id="customer_id" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id', $invoice->customer_id) == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Invoice Date -->
                    <div>
                        <label for="invoice_date" class="block text-sm font-medium text-gray-700 mb-2">Invoice Date *</label>
                        <input type="date" 
                               name="invoice_date" 
                               id="invoice_date" 
                               value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>

                    <!-- Due Date -->
                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">Due Date *</label>
                        <input type="date" 
                               name="due_date" 
                               id="due_date" 
                               value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Invoice Items -->
                <div class="mt-6">
                    <h3 class="text-lg font-medium text-gray-700 mb-4">Invoice Items</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="itemsTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tax %</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody" class="bg-white divide-y divide-gray-200">
                                @foreach($invoice->items as $index => $item)
                                <tr class="item-row" data-row="{{ $index }}">
                                    <td class="px-6 py-4">
                                        <input type="text" 
                                               name="items[{{ $index }}][description]" 
                                               value="{{ old('items.'.$index.'.description', $item->description) }}"
                                               required
                                               class="w-full px-2 py-1 border border-gray-300 rounded-md">
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="number" 
                                               name="items[{{ $index }}][quantity]" 
                                               value="{{ old('items.'.$index.'.quantity', $item->quantity) }}" 
                                               min="1" 
                                               step="any"
                                               required
                                               class="w-full px-2 py-1 border border-gray-300 rounded-md quantity">
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="number" 
                                               name="items[{{ $index }}][unit_price]" 
                                               value="{{ old('items.'.$index.'.unit_price', $item->unit_price) }}" 
                                               min="0" 
                                               step="0.01"
                                               required
                                               class="w-full px-2 py-1 border border-gray-300 rounded-md unit-price">
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="number" 
                                               name="items[{{ $index }}][tax_rate]" 
                                               value="{{ old('items.'.$index.'.tax_rate', $item->tax_rate) }}" 
                                               min="0" 
                                               step="0.01"
                                               required
                                               class="w-full px-2 py-1 border border-gray-300 rounded-md tax-rate">
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="line-total">{{ number_format($item->total, 2) }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <button type="button" class="text-red-600 hover:text-red-900 remove-row">Remove</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <button type="button" 
                                id="addRow" 
                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Add Item
                        </button>
                    </div>

                    <!-- Totals -->
                    <div class="mt-6 md:w-1/3 ml-auto">
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="font-medium">Subtotal:</span>
                                <span id="subtotal">${{ number_format($invoice->subtotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Tax:</span>
                                <span id="tax">${{ number_format($invoice->tax_amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total:</span>
                                <span id="total">${{ number_format($invoice->total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mt-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea name="notes" 
                              id="notes" 
                              rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">{{ old('notes', $invoice->notes) }}</textarea>
                </div>

                <!-- Submit Button -->
                <div class="mt-6 flex justify-end">
                    <button type="submit" 
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Update Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            let rowCount = {{ count($invoice->items) }};

            // Add new row
            $('#addRow').click(function() {
                const newRow = `
                    <tr class="item-row" data-row="${rowCount}">
                        <td class="px-6 py-4">
                            <input type="text" 
                                   name="items[${rowCount}][description]" 
                                   required
                                   class="w-full px-2 py-1 border border-gray-300 rounded-md">
                        </td>
                        <td class="px-6 py-4">
                            <input type="number" 
                                   name="items[${rowCount}][quantity]" 
                                   value="1" 
                                   min="1" 
                                   step="any"
                                   required
                                   class="w-full px-2 py-1 border border-gray-300 rounded-md quantity">
                        </td>
                        <td class="px-6 py-4">
                            <input type="number" 
                                   name="items[${rowCount}][unit_price]" 
                                   value="0.00" 
                                   min="0" 
                                   step="0.01"
                                   required
                                   class="w-full px-2 py-1 border border-gray-300 rounded-md unit-price">
                        </td>
                        <td class="px-6 py-4">
                            <input type="number" 
                                   name="items[${rowCount}][tax_rate]" 
                                   value="0" 
                                   min="0" 
                                   step="0.01"
                                   required
                                   class="w-full px-2 py-1 border border-gray-300 rounded-md tax-rate">
                        </td>
                        <td class="px-6 py-4">
                            <span class="line-total">0.00</span>
                        </td>
                        <td class="px-6 py-4">
                            <button type="button" class="text-red-600 hover:text-red-900 remove-row">Remove</button>
                        </td>
                    </tr>
                `;
                $('#itemsBody').append(newRow);
                rowCount++;
            });

            // Remove row
            $(document).on('click', '.remove-row', function() {
                if ($('.item-row').length > 1) {
                    $(this).closest('tr').remove();
                    calculateTotals();
                }
            });

            // Calculate line totals
            $(document).on('input', '.quantity, .unit-price, .tax-rate', function() {
                calculateRowTotal($(this).closest('tr'));
                calculateTotals();
            });

            function calculateRowTotal(row) {
                const quantity = parseFloat(row.find('.quantity').val()) || 0;
                const unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
                const taxRate = parseFloat(row.find('.tax-rate').val()) || 0;
                
                const subtotal = quantity * unitPrice;
                const tax = subtotal * (taxRate / 100);
                const total = subtotal + tax;
                
                row.find('.line-total').text(total.toFixed(2));
            }

            function calculateTotals() {
                let subtotal = 0;
                let tax = 0;

                $('.item-row').each(function() {
                    const quantity = parseFloat($(this).find('.quantity').val()) || 0;
                    const unitPrice = parseFloat($(this).find('.unit-price').val()) || 0;
                    const taxRate = parseFloat($(this).find('.tax-rate').val()) || 0;
                    
                    const lineSubtotal = quantity * unitPrice;
                    const lineTax = lineSubtotal * (taxRate / 100);
                    
                    subtotal += lineSubtotal;
                    tax += lineTax;
                });

                const total = subtotal + tax;

                $('#subtotal').text('$' + subtotal.toFixed(2));
                $('#tax').text('$' + tax.toFixed(2));
                $('#total').text('$' + total.toFixed(2));
            }

            // Initial calculations
            calculateTotals();
        });
    </script>
</body>
</html>
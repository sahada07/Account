@extends('layouts.app')

@section('content')
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Invoices</h2>

                @if(Auth::user()->role !=='accountant')
                <a href="{{ route('invoices.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Create Invoice
                </a>
                @endif
            </div>

            <!-- Search and Filter Section -->
            <div class="mb-6">
                <form action="{{ route('invoices.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Search invoices..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <select name="status" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">All Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <input type="date" 
                               name="start_date" 
                               value="{{ request('start_date') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <input type="date" 
                               name="end_date" 
                               value="{{ request('end_date') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div class="md:col-span-4 flex justify-end">
                        <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Search
                        </button>
                    </div>
                </form>
            </div>

            <!-- Invoices Table -->
            {{-- <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Invoice Number
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Customer
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Due Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Balance
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($invoices as $invoice)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600 hover:text-blue-900">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $invoice->customer->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $invoice->invoice_date->format('Y-m-d') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $invoice->due_date->format('Y-m-d') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    ${{ number_format($invoice->total, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    ${{ number_format($invoice->balance_due, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $invoice->status === 'sent' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $invoice->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                                        {{ $invoice->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        @if($invoice->status === 'draft')
                                            <a href="{{ route('invoices.edit', $invoice) }}" 
                                               class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="text-red-600 hover:text-red-900"
                                                        onclick="return confirm('Are you sure you want to delete this invoice?')">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('invoices.show', $invoice) }}" 
                                           class="text-blue-600 hover:text-blue-900">View</a>
                                        @if($invoice->status === 'draft')
                                            <form action="{{ route('invoices.mark-as-sent', $invoice) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-900">
                                                    Mark as Sent
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                    No invoices found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div> --}}


            <!-- Invoices Table -->
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Invoice Number
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Customer
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Due Date
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Total
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Balance
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($invoices as $invoice)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600 hover:text-blue-900">
                            {{ $invoice->invoice_number }}
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $invoice->customer->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $invoice->invoice_date->format('Y-m-d') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $invoice->due_date->format('Y-m-d') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        ${{ number_format($invoice->total, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        ${{ number_format($invoice->balance_due, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $invoice->status === 'sent' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $invoice->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                            {{ $invoice->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            @if($invoice->status === 'draft')
                                <a href="{{ route('invoices.edit', $invoice) }}" 
                                   class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-900"
                                            onclick="return confirm('Are you sure you want to delete this invoice?')">
                                        Delete
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('invoices.show', $invoice) }}" 
                               class="text-blue-600 hover:text-blue-900">View</a>
                            @if($invoice->status === 'draft')
                                <form action="{{ route('invoices.mark-as-sent', $invoice) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-900">
                                        Mark as Sent
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                        No invoices match your search criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="mt-4">
    {{ $invoices->links() }}
</div>


            <!-- Pagination -->
            <div class="mt-4">
                {{ $invoices->links() }}
            </div>
        </div>
    </div>
    @endsection
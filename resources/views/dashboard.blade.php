@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-blue-50 p-4 rounded-lg">
            <h3 class="font-semibold text-lg mb-2">Total Receivables</h3>
            <p class="text-2xl font-bold">${{ number_format($totalReceivables, 2) }}</p>
        </div>
        <div class="bg-red-50 p-4 rounded-lg">
            <h3 class="font-semibold text-lg mb-2">Total Payables</h3>
            <p class="text-2xl font-bold">${{ number_format($totalPayables, 2) }}</p>
        </div>
        <div class="bg-green-50 p-4 rounded-lg">
            <h3 class="font-semibold text-lg mb-2">Monthly Income</h3>
            <p class="text-2xl font-bold">${{ number_format($monthlyIncome, 2) }}</p>
        </div>
        <div class="bg-yellow-50 p-4 rounded-lg">
            <h3 class="font-semibold text-lg mb-2">Monthly Expenses</h3>
            <p class="text-2xl font-bold">${{ number_format($monthlyExpenses, 2) }}</p>
        </div>
    </div>

    <!-- Business Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-lg mb-2">Business Overview</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">Active Customers:</span>
                    <span class="font-medium">{{ $customerCount }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Active Vendors:</span>
                    <span class="font-medium">{{ $vendorCount }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Overdue Invoices:</span>
                    <span class="font-medium text-red-600">{{ $overdueInvoices }}</span>
                </div>
            </div>
        </div>

        <!-- Recent Invoices -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-lg mb-2">Recent Invoices</h3>
            @if($recentInvoices->count() > 0)
                <div class="space-y-2">
                    @foreach($recentInvoices as $invoice)
                        <div class="flex justify-between items-center">
                            <div>
                                <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600 hover:text-blue-900">
                                    {{ $invoice->invoice_number }}
                                </a>
                                <p class="text-sm text-gray-600">{{ $invoice->customer->name }}</p>
                            </div>
                            <div class="text-right">
                                <span class="font-medium">${{ number_format($invoice->total, 2) }}</span>
                                <p class="text-sm {{ $invoice->status === 'paid' ? 'text-green-600' : 'text-yellow-600' }}">
                                    {{ ucfirst($invoice->status) }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500">No recent invoices</p>
            @endif
        </div>

        <!-- Recent Bills -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-lg mb-2">Recent Bills</h3>
            @if($recentBills->count() > 0)
                <div class="space-y-2">
                    @foreach($recentBills as $bill)
                        <div class="flex justify-between items-center">
                            <div>
                                <a href="{{ route('bills.show', $bill) }}" class="text-blue-600 hover:text-blue-900">
                                    {{ $bill->bill_number }}
                                </a>
                                <p class="text-sm text-gray-600">{{ $bill->vendor->name }}</p>
                            </div>
                            <div class="text-right">
                                <span class="font-medium">${{ number_format($bill->total, 2) }}</span>
                                <p class="text-sm {{ $bill->status === 'paid' ? 'text-green-600' : 'text-yellow-600' }}">
                                    {{ ucfirst($bill->status) }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500">No recent bills</p>
            @endif
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Income vs Expenses -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-lg mb-4">Income vs Expenses</h3>
            <div class="relative" style="height: 300px;">
                <canvas id="incomeExpensesChart"></canvas>
            </div>
        </div>

        <!-- Invoice Status Distribution -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-lg mb-4">Invoice Status Distribution</h3>
            <div class="relative" style="height: 300px;">
                <canvas id="invoiceStatusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Second Charts Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Top Customers -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-lg mb-4">Top Customers</h3>
            <div class="relative" style="height: 300px;">
                <canvas id="topCustomersChart"></canvas>
            </div>
        </div>

        <!-- Aging Receivables -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-lg mb-4">Aging Receivables</h3>
            <div class="relative" style="height: 300px;">
                <canvas id="agingReceivablesChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Income vs Expenses Chart
    new Chart(document.getElementById('incomeExpensesChart'), {
        type: 'line',
        data: {
            labels: @json($monthlyData->pluck('month')),
            datasets: [
                {
                    label: 'Income',
                    data: @json($monthlyData->pluck('income')),
                    borderColor: '#34D399',
                    fill: false,
                    tension: 0.4
                },
                {
                    label: 'Expenses',
                    data: @json($monthlyData->pluck('expenses')),
                    borderColor: '#F87171',
                    fill: false,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Invoice Status Distribution Chart
    new Chart(document.getElementById('invoiceStatusChart'), {
        type: 'doughnut',
        data: {
            labels: @json($invoiceStatusCounts->pluck('status')),
            datasets: [
                {
                    data: @json($invoiceStatusCounts->pluck('count')),
                    backgroundColor: ['#34D399', '#F59E0B', '#F87171', '#3B82F6']
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Top Customers Chart
    new Chart(document.getElementById('topCustomersChart'), {
        type: 'bar',
        data: {
            labels: @json($topCustomers->pluck('customer.name')),
            datasets: [
                {
                    label: 'Total Amount',
                    data: @json($topCustomers->pluck('total_amount')),
                    backgroundColor: '#60A5FA'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Aging Receivables Chart
    new Chart(document.getElementById('agingReceivablesChart'), {
        type: 'pie',
        data: {
            labels: ['Current', '1-30 Days', '31-60 Days', '60+ Days'],
            datasets: [
                {
                    data: [
                        {{ $agingReceivables['current'] }},
                        {{ $agingReceivables['30days'] }},
                        {{ $agingReceivables['60days'] }},
                        {{ $agingReceivables['90days'] }}
                    ],
                    backgroundColor: ['#34D399', '#F59E0B', '#F87171', '#EF4444']
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
});
</script>
@endsection

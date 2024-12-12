
<!-- resources/views/dashboard.blade.php -->
@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold mb-4">Dashboard</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Quick Stats -->
        <div class="bg-blue-50 p-4 rounded-lg">
            <h3 class="font-semibold text-lg mb-2">Outstanding Invoices</h3>
            <p class="text-2xl font-bold">{{ $outstandingInvoices ?? 0 }}</p>
        </div>
        
        <div class="bg-green-50 p-4 rounded-lg">
            <h3 class="font-semibold text-lg mb-2">Total Receivables</h3>
            <p class="text-2xl font-bold">${{ number_format($totalReceivables ?? 0, 2) }}</p>
        </div>
        
        <div class="bg-red-50 p-4 rounded-lg">
            <h3 class="font-semibold text-lg mb-2">Total Payables</h3>
            <p class="text-2xl font-bold">${{ number_format($totalPayables ?? 0, 2) }}</p>
        </div>
    </div>
</div>
@endsection
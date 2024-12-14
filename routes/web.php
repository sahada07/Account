<?php
// routes/web.php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\PaymentController;

use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');



// Customers
Route::controller(CustomerController::class)->group(function () {
    Route::get('/customers', 'index')->name('customers.index');
    Route::get('/customers/create', 'create')->name('customers.create');
    Route::post('/customers', 'store')->name('customers.store');
    Route::get('/customers/{customer}', 'show')->name('customers.show');
    Route::get('/customers/{customer}/edit', 'edit')->name('customers.edit');
    Route::put('/customers/{customer}', 'update')->name('customers.update');
    Route::delete('/customers/{customer}', 'destroy')->name('customers.destroy');
    Route::get('/customers/{customer}/statements', 'statements')->name('customers.statements');
});

// Invoices
Route::controller(InvoiceController::class)->group(function () {
    Route::get('/invoices', 'index')->name('invoices.index');
    Route::get('/invoices/create', 'create')->name('invoices.create');
    Route::post('/invoices', 'store')->name('invoices.store');
    Route::get('/invoices/{invoice}', 'show')->name('invoices.show');
    Route::get('/invoices/{invoice}/edit', 'edit')->name('invoices.edit');
    Route::put('/invoices/{invoice}', 'update')->name('invoices.update');
    Route::delete('/invoices/{invoice}', 'destroy')->name('invoices.destroy');
    Route::post('/invoices/{invoice}/mark-as-sent', 'markAsSent')->name('invoices.mark-as-sent');
    Route::get('/invoices/{invoice}/pdf', 'downloadPdf')->name('invoices.pdf');
});

// Payments
Route::controller(PaymentController::class)->group(function () {
    Route::get('/payments', 'index')->name('payments.index');
    Route::get('/payments/create', 'create')->name('payments.create');
    Route::post('/payments', 'store')->name('payments.store');
    Route::get('/payments/{payment}', 'show')->name('payments.show');
    Route::get('/payments/{payment}/edit', 'edit')->name('payments.edit');
    Route::put('/payments/{payment}', 'update')->name('payments.update');
    Route::delete('/payments/{payment}', 'destroy')->name('payments.destroy');
    Route::get('/payments/payable-details', 'getPayableDetails')->name('payments.payable-details');
});
//Route::get('/dash', [DashController::class])->name('dash');

// Bills
Route::controller(BillController::class)->group(function () {
    Route::get('/bills', 'index')->name('bills.index');
    Route::get('/bills/create', 'create')->name('bills.create');
    Route::post('/bills', 'store')->name('bills.store');
    Route::get('/bills/{bill}', 'show')->name('bills.show');
    Route::get('/bills/{bill}/edit', 'edit')->name('bills.edit');
    Route::put('/bills/{bill}', 'update')->name('bills.update');
    Route::delete('/bills/{bill}', 'destroy')->name('bills.destroy');
    Route::post('/bills/{bill}/mark-as-received', 'markAsReceived')->name('bills.mark-as-received');
    Route::get('/bills/{bill}/pdf', 'downloadPdf')->name('bills.pdf');
});

// Vendors
Route::controller(VendorController::class)->group(function () {
    Route::get('/vendors', 'index')->name('vendors.index');
    Route::get('/vendors/create', 'create')->name('vendors.create');
    Route::post('/vendors', 'store')->name('vendors.store');
    Route::get('/vendors/{vendor}', 'show')->name('vendors.show');
    Route::get('/vendors/{vendor}/edit', 'edit')->name('vendors.edit');
    Route::put('/vendors/{vendor}', 'update')->name('vendors.update');
    Route::delete('/vendors/{vendor}', 'destroy')->name('vendors.destroy');
    Route::get('/vendors/{vendor}/statements', 'statements')->name('vendors.statements');
});


use App\Http\Controllers\ReportController;

// Reports
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/income-statement', [ReportController::class, 'incomeStatement'])->name('income-statement');
    Route::get('/accounts-receivable', [ReportController::class, 'accountsReceivableAging'])->name('accounts-receivable');
    Route::get('/accounts-payable', [ReportController::class, 'accountsPayableAging'])->name('accounts-payable');
    Route::get('/tax-summary', [ReportController::class, 'taxSummary'])->name('tax-summary');
    Route::get('/customer-bala\nces', [ReportController::class, 'customerBalances'])->name('customer-balances');
    Route::get('/vendor-balances', [ReportController::class, 'vendorBalances'])->name('vendor-balances');
});


use App\Http\Controllers\PDFController;
// PDF Routes
Route::get('/invoices/{invoice}/pdf', [PDFController::class, 'generateInvoicePDF'])->name('invoices.pdf');
Route::get('/bills/{bill}/pdf', [PDFController::class, 'generateBillPDF'])->name('bills.pdf');
Route::get('/payments/{payment}/pdf', [PDFController::class, 'generatePaymentReceiptPDF'])->name('payments.pdf');
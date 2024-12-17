<?php
// routes/web.php

use App\Http\Controllers\{
    AuthController,
    CustomerController,
    VendorController,
    InvoiceController,
    BillController,
    PaymentController,
    DashboardController,
    ReportController,
    PDFController,
    UserController
};

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// All authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard accessible by all authenticated users
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);
    });

    // Admin and Staff routes
    Route::middleware('role:admin,staff,accountant')->group(function () {
        Route::resource('customers', CustomerController::class);
        Route::resource('vendors', VendorController::class);
        
        // Invoice management
        Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::put('/invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
        Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
        Route::post('/invoices/{invoice}/mark-as-sent', [InvoiceController::class, 'markAsSent'])->name('invoices.mark-as-sent');

        // Bill management
        Route::get('/bills/create', [BillController::class, 'create'])->name('bills.create');
        Route::post('/bills', [BillController::class, 'store'])->name('bills.store');
        Route::get('/bills/{bill}/edit', [BillController::class, 'edit'])->name('bills.edit');
        Route::put('/bills/{bill}', [BillController::class, 'update'])->name('bills.update');
        Route::delete('/bills/{bill}', [BillController::class, 'destroy'])->name('bills.destroy');
        Route::post('/bills/{bill}/mark-as-received', [BillController::class, 'markAsReceived'])->name('bills.mark-as-received');
    });

    // Routes accessible by all roles (view only)
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
    Route::get('/bills/{bill}', [BillController::class, 'show'])->name('bills.show');
    
    // Payments
    Route::resource('payments', PaymentController::class)->only(['index', 'show']);
    Route::middleware('role:admin,staff')->group(function () {
        Route::resource('payments', PaymentController::class)->except(['index', 'show']);
    });
    Route::get('/payments/payable-details', [PaymentController::class, 'getPayableDetails'])->name('payments.payable-details');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/income-statement', [ReportController::class, 'incomeStatement'])->name('income-statement');
        Route::get('/accounts-receivable', [ReportController::class, 'accountsReceivableAging'])->name('accounts-receivable');
        Route::get('/accounts-payable', [ReportController::class, 'accountsPayableAging'])->name('accounts-payable');
        Route::get('/tax-summary', [ReportController::class, 'taxSummary'])->name('tax-summary');
        Route::get('/customer-balances', [ReportController::class, 'customerBalances'])->name('customer-balances');
        Route::get('/vendor-balances', [ReportController::class, 'vendorBalances'])->name('vendor-balances');
    });

    // PDF Routes
    Route::get('/invoices/{invoice}/pdf', [PDFController::class, 'generateInvoicePDF'])->name('invoices.pdf');
    Route::get('/bills/{bill}/pdf', [PDFController::class, 'generateBillPDF'])->name('bills.pdf');
    Route::get('/payments/{payment}/pdf', [PDFController::class, 'generatePaymentReceiptPDF'])->name('payments.pdf');
});
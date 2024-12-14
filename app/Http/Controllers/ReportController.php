<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function incomeStatement(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        // Income
        $income = Payment::whereHasMorph('payable', [Invoice::class])
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount');

        // Expenses
        $expenses = Payment::whereHasMorph('payable', [Bill::class])
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount');

        // Net Income
        $netIncome = $income - $expenses;

        return view('reports.income-statement', compact(
            'startDate',
            'endDate',
            'income',
            'expenses',
            'netIncome'
        ));
    }

    public function accountsReceivableAging()
    {
        $aging = [
            'current' => Invoice::where('due_date', '>=', now())->sum('balance_due'),
            '30days' => Invoice::whereBetween('due_date', [now()->subDays(30), now()])->sum('balance_due'),
            '60days' => Invoice::whereBetween('due_date', [now()->subDays(60), now()->subDays(31)])->sum('balance_due'),
            '90days' => Invoice::where('due_date', '<=', now()->subDays(61))->sum('balance_due')
        ];

        $customers = Customer::withSum(['invoices as total_due' => function($query) {
            $query->where('status', '!=', 'paid');
        }], 'balance_due')
        ->having('total_due', '>', 0)
        ->get();

        return view('reports.accounts-receivable', compact('aging', 'customers'));
    }

    public function accountsPayableAging()
    {
        $aging = [
            'current' => Bill::where('due_date', '>=', now())->sum('balance_due'),
            '30days' => Bill::whereBetween('due_date', [now()->subDays(30), now()])->sum('balance_due'),
            '60days' => Bill::whereBetween('due_date', [now()->subDays(60), now()->subDays(31)])->sum('balance_due'),
            '90days' => Bill::where('due_date', '<=', now()->subDays(61))->sum('balance_due')
        ];

        $vendors = Vendor::withSum(['bills as total_due' => function($query) {
            $query->where('status', '!=', 'paid');
        }], 'balance_due')
        ->having('total_due', '>', 0)
        ->get();

        return view('reports.accounts-payable', compact('aging', 'vendors'));
    }

    public function taxSummary(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfYear());
        $endDate = $request->get('end_date', now()->endOfYear());

        $taxCollected = Invoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('tax_amount');

        $taxPaid = Bill::whereBetween('bill_date', [$startDate, $endDate])
            ->sum('tax_amount');

        $netTax = $taxCollected - $taxPaid;

        return view('reports.tax-summary', compact(
            'startDate',
            'endDate',
            'taxCollected',
            'taxPaid',
            'netTax'
        ));
    }

    public function customerBalances()
    {
        $customers = Customer::withSum(['invoices as total_invoiced' => function($query) {
            $query->where('status', '!=', 'cancelled');
        }], 'total')
        ->withSum(['invoices as total_paid' => function($query) {
            $query->where('status', '!=', 'cancelled');
        }], 'amount_paid')
        ->withSum(['invoices as balance_due' => function($query) {
            $query->where('status', '!=', 'cancelled');
        }], 'balance_due')
        ->having('total_invoiced', '>', 0)
        ->get();

        return view('reports.customer-balances', compact('customers'));
    }

    public function vendorBalances()
    {
        $vendors = Vendor::withSum(['bills as total_billed' => function($query) {
            $query->where('status', '!=', 'cancelled');
        }], 'total')
        ->withSum(['bills as total_paid' => function($query) {
            $query->where('status', '!=', 'cancelled');
        }], 'amount_paid')
        ->withSum(['bills as balance_due' => function($query) {
            $query->where('status', '!=', 'cancelled');
        }], 'balance_due')
        ->having('total_billed', '>', 0)
        ->get();

        return view('reports.vendor-balances', compact('vendors'));
    }
}
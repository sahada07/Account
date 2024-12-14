<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get totals
        $totalReceivables = Invoice::where('status', '!=', 'paid')
                                  ->sum('balance_due');
        
        $totalPayables = Bill::where('status', '!=', 'paid')
                            ->sum('balance_due');

        // Get recent invoices
        $recentInvoices = Invoice::with('customer')
                                ->latest()
                                ->take(5)
                                ->get();

        // Get recent bills
        $recentBills = Bill::with('vendor')
                          ->latest()
                          ->take(5)
                          ->get();

        // Get monthly payment data for last 6 months
        $monthlyData = collect(); // Initialize as collection
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyData->push([
                'month' => $date->format('M'),
                'income' => Payment::whereHasMorph('payable', [Invoice::class])
                    ->whereYear('payment_date', $date->year)
                    ->whereMonth('payment_date', $date->month)
                    ->sum('amount'),
                'expenses' => Payment::whereHasMorph('payable', [Bill::class])
                    ->whereYear('payment_date', $date->year)
                    ->whereMonth('payment_date', $date->month)
                    ->sum('amount')
            ]);
        }

        // Get payment statistics
        $monthlyIncome = Payment::whereHasMorph('payable', [Invoice::class])
                               ->whereMonth('payment_date', now()->month)
                               ->sum('amount');

        $monthlyExpenses = Payment::whereHasMorph('payable', [Bill::class])
                                 ->whereMonth('payment_date', now()->month)
                                 ->sum('amount');

        // Get counts
        $customerCount = Customer::count();
        $vendorCount = Vendor::count();
        $overdueInvoices = Invoice::where('due_date', '<', now())
                                 ->where('status', '!=', 'paid')
                                 ->count();

        // Get top customers
        $topCustomers = Invoice::select('customer_id', DB::raw('SUM(total) as total_amount'))
            ->with('customer:id,name')
            ->groupBy('customer_id')
            ->orderByDesc('total_amount')
            ->take(5)
            ->get();

        // Get invoice status distribution
        $invoiceStatusCounts = collect(Invoice::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get());

        // Get aging receivables
        $agingReceivables = [
            'current' => Invoice::where('due_date', '>=', now())->sum('balance_due'),
            '30days' => Invoice::whereBetween('due_date', [now()->subDays(30), now()])->sum('balance_due'),
            '60days' => Invoice::whereBetween('due_date', [now()->subDays(60), now()->subDays(31)])->sum('balance_due'),
            '90days' => Invoice::where('due_date', '<=', now()->subDays(61))->sum('balance_due')
        ];

        return view('dashboard', compact(
            'totalReceivables',
            'totalPayables',
            'recentInvoices',
            'recentBills',
            'monthlyIncome',
            'monthlyExpenses',
            'customerCount',
            'vendorCount',
            'overdueInvoices',
            'monthlyData',
            'topCustomers',
            'invoiceStatusCounts',
            'agingReceivables'
        ));
    }
}

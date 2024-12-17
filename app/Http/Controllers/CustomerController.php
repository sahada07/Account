<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index(Request $request)
    // {
    //     $query = Customer::query();

    //     // Search functionality
    //     if ($request->has('search')) {
    //         $search = $request->get('search');
    //         $query->where(function($q) use ($search) {
    //             $q->where('name', 'like', "%{$search}%")
    //               ->orWhere('email', 'like', "%{$search}%")
    //               ->orWhere('phone', 'like', "%{$search}%");
    //         });
    //     }

    //     // Filter by status
    //     if ($request->has('status')) {
    //         $query->where('is_active', $request->status === 'active');
    //     }

    //     $customers = $query->latest()
    //                       ->withCount('invoices')
    //                       ->withSum('invoices', 'balance_due')
    //                       ->paginate(10);

    //     return view('customers.index', compact('customers'));
    // }

    public function index(Request $request)
{
    $query = Customer::query();

    // Search functionality
    if ($request->filled('search')) {
        $search = $request->get('search');
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    // Filter by status
    if ($request->filled('status')) {
        $query->where('is_active', $request->status === 'active');
    }

    $customers = $query->latest()
        ->withCount('invoices')
        ->withSum('invoices', 'balance_due')
        ->paginate(10);

    return view('customers.index', compact('customers'));
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'tax_number' => 'nullable|string|max:50',
            'is_active' => 'boolean'
        ]);

        Customer::create($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        $customer->load(['invoices' => function($query) {
            $query->latest()->with('payments');
        }]);

        $statistics = [
            'total_invoices' => $customer->invoices->count(),
            'total_amount' => $customer->invoices->sum('total'),
            'total_paid' => $customer->invoices->sum('amount_paid'),
            'total_due' => $customer->invoices->sum('balance_due')
        ];

        return view('customers.show', compact('customer', 'statistics'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email,' . $customer->id . '|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'tax_number' => 'nullable|string|max:50',
            'is_active' => 'boolean'
        ]); 

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        // Check if customer has any invoices
        if ($customer->invoices()->exists()) {
            return redirect()->route('customers.index')
                ->with('error', 'Cannot delete customer with existing invoices.');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    public function statements(Customer $customer)
    {
        $statements = $customer->invoices()
            ->with('payments')
            ->latest()
            ->get()
            ->groupBy(function($invoice) {
                return $invoice->invoice_date->format('Y-m');
            });

        return view('customers.statements', compact('customer', 'statements'));
    }
}

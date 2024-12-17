<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    // public function index(Request $request)
    // {
    //     $query = Vendor::query();

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

    //     $vendors = $query->latest()
    //                     ->withCount('bills')
    //                     ->withSum('bills', 'balance_due')
    //                     ->paginate(10);

    //     return view('vendors.index', compact('vendors'));
    // }

    public function index(Request $request)
    {
        $query = Vendor::withCount('bills')->withSum('bills', 'balance_due');
    
        // Search functionality
        if ($request->has('search') && $request->get('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
    
        // Filter by status
        if ($request->has('status') && $request->get('status')) {
            $query->where('is_active', $request->status === 'active');
        }
    
        // Pagination
        $vendors = $query->latest()->paginate(10);
    
        return view('vendors.index', compact('vendors'));
    }
    


    public function create()
    {
        return view('vendors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:vendors,email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'tax_number' => 'nullable|string|max:50',
            'is_active' => 'boolean'
        ]);

        $vendor = Vendor::create($validated);

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor created successfully.');
    }

    public function show(Vendor $vendor)
    {
        $vendor->load(['bills' => function($query) {
            $query->latest()->with('payments');
        }]);

        $statistics = [
            'total_bills' => $vendor->bills->count(),
            'total_amount' => $vendor->bills->sum('total'),
            'total_paid' => $vendor->bills->sum('amount_paid'),
            'total_due' => $vendor->bills->sum('balance_due')
        ];

        return view('vendors.show', compact('vendor', 'statistics'));
    }

    public function edit(Vendor $vendor)
    {
        return view('vendors.edit', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:vendors,email,' . $vendor->id . '|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'tax_number' => 'nullable|string|max:50',
            'is_active' => 'boolean'
        ]);

        $vendor->update($validated);

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Vendor $vendor)
    {
        // Check if vendor has any bills
        if ($vendor->bills()->exists()) {
            return redirect()->route('vendors.index')
                ->with('error', 'Cannot delete vendor with existing bills.');
        }

        $vendor->delete();

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor deleted successfully.');
    }

    public function statements(Vendor $vendor)
    {
        $statements = $vendor->bills()
            ->with('payments')
            ->latest()
            ->get()
            ->groupBy(function($bill) {
                return $bill->bill_date->format('Y-m');
            });

        return view('vendors.statements', compact('vendor', 'statements'));
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillController extends Controller
{
    // public function index(Request $request)
    // {
    //     $query = Bill::with(['vendor', 'createdBy']);

    //     // Search functionality
    //     if ($request->has('search')) {
    //         $search = $request->get('search');
    //         $query->where(function($q) use ($search) {
    //             $q->where('bill_number', 'like', "%{$search}%")
    //               ->orWhereHas('vendor', function($q) use ($search) {
    //                   $q->where('name', 'like', "%{$search}%");
    //               });
    //         });
    //     }

    //     // Filter by status
    //     if ($request->has('status')) {
    //         $query->where('status', $request->status);
    //     }

    //     // Filter by date range
    //     if ($request->has('start_date')) {
    //         $query->whereDate('bill_date', '>=', $request->start_date);
    //     }
    //     if ($request->has('end_date')) {
    //         $query->whereDate('bill_date', '<=', $request->end_date);
    //     }

    //     $bills = $query->latest()->paginate(10);

    //     return view('bills.index', compact('bills'));
    // }

    public function index(Request $request)
    {
        $query = Bill::with(['vendor', 'createdBy']);
    
        // Search functionality
        if ($request->has('search') && $request->get('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('bill_number', 'like', "%{$search}%")
                  ->orWhereHas('vendor', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
    
        // Filter by status
        if ($request->has('status') && $request->get('status')) {
            $query->where('status', $request->status);
        }
    
        // Filter by date range
        if ($request->has('start_date') && $request->get('start_date')) {
            $query->whereDate('bill_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->get('end_date')) {
            $query->whereDate('bill_date', '<=', $request->end_date);
        }
    
        // Paginate results
        $bills = $query->latest()->paginate(10);
    
        return view('bills.index', compact('bills'));
    }
    
    
    public function create()
    {
        $vendors = Vendor::where('is_active', true)
            ->orderBy('name')
            ->get();
            
        return view('bills.create', compact('vendors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'bill_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:bill_date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // Create bill
                $bill = Bill::create([
                    'vendor_id' => $validated['vendor_id'],
                    'bill_date' => $validated['bill_date'],
                    'due_date' => $validated['due_date'],
                    'notes' => $validated['notes'] ?? null,
                    'bill_number' => $this->generateBillNumber(),
                    'created_by' => 1, // Temporary until auth is implemented
                    'status' => 'draft'
                ]);

                // Create bill items
                foreach ($validated['items'] as $item) {
                    $subtotal = $item['quantity'] * $item['unit_price'];
                    $tax_amount = $subtotal * ($item['tax_rate'] / 100);
                    
                    $bill->items()->create([
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'tax_rate' => $item['tax_rate'],
                        'tax_amount' => $tax_amount,
                        'subtotal' => $subtotal,
                        'total' => $subtotal + $tax_amount
                    ]);
                }

                // Update bill totals
                $totals = $bill->items()->selectRaw('
                    SUM(subtotal) as subtotal,
                    SUM(tax_amount) as tax_amount,
                    SUM(total) as total
                ')->first();

                $bill->update([
                    'subtotal' => $totals->subtotal,
                    'tax_amount' => $totals->tax_amount,
                    'total' => $totals->total,
                    'balance_due' => $totals->total
                ]);
            });

            return redirect()->route('bills.index')
                ->with('success', 'Bill created successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create bill: ' . $e->getMessage());
        }
    }

    public function show(Bill $bill)
    {
        $bill->load(['vendor', 'items', 'payments', 'createdBy']);
        return view('bills.show', compact('bill'));
    }

    public function edit(Bill $bill)
    {
        if ($bill->status !== 'draft') {
            return redirect()->route('bills.index')
                ->with('error', 'Can only edit draft bills.');
        }

        $vendors = Vendor::where('is_active', true)->get();
        return view('bills.edit', compact('bill', 'vendors'));
    }

    public function update(Request $request, Bill $bill)
    {
        if ($bill->status !== 'draft') {
            return redirect()->route('bills.index')
                ->with('error', 'Can only edit draft bills.');
        }

        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'bill_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:bill_date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::transaction(function () use ($validated, $bill) {
                // Update bill
                $bill->update([
                    'vendor_id' => $validated['vendor_id'],
                    'bill_date' => $validated['bill_date'],
                    'due_date' => $validated['due_date'],
                    'notes' => $validated['notes'] ?? null
                ]);

                // Delete existing items
                $bill->items()->delete();

                // Create new items
                foreach ($validated['items'] as $item) {
                    $subtotal = $item['quantity'] * $item['unit_price'];
                    $tax_amount = $subtotal * ($item['tax_rate'] / 100);
                    
                    $bill->items()->create([
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'tax_rate' => $item['tax_rate'],
                        'tax_amount' => $tax_amount,
                        'subtotal' => $subtotal,
                        'total' => $subtotal + $tax_amount
                    ]);
                }

                // Update bill totals
                $totals = $bill->items()->selectRaw('
                    SUM(subtotal) as subtotal,
                    SUM(tax_amount) as tax_amount,
                    SUM(total) as total
                ')->first();

                $bill->update([
                    'subtotal' => $totals->subtotal,
                    'tax_amount' => $totals->tax_amount,
                    'total' => $totals->total,
                    'balance_due' => $totals->total
                ]);
            });

            return redirect()->route('bills.show', $bill)
                ->with('success', 'Bill updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update bill: ' . $e->getMessage());
        }
    }

    public function destroy(Bill $bill)
    {
        if ($bill->status !== 'draft') {
            return redirect()->route('bills.index')
                ->with('error', 'Can only delete draft bills.');
        }

        try {
            DB::transaction(function () use ($bill) {
                $bill->items()->delete();
                $bill->delete();
            });

            return redirect()->route('bills.index')
                ->with('success', 'Bill deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete bill: ' . $e->getMessage());
        }
    }

    public function markAsReceived(Bill $bill)
    {
        if ($bill->status !== 'draft') {
            return redirect()->route('bills.index')
                ->with('error', 'Bill is already received.');
        }

        $bill->update(['status' => 'received']);

        return redirect()->route('bills.show', $bill)
            ->with('success', 'Bill marked as received.');
    }

    private function generateBillNumber()
    {
        $prefix = 'BILL-' . date('Y');
        $lastBill = Bill::where('bill_number', 'like', $prefix . '%')
            ->orderBy('bill_number', 'desc')
            ->first();

        if ($lastBill) {
            $lastNumber = intval(substr($lastBill->bill_number, -6));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . sprintf('%06d', $newNumber);
    }
}
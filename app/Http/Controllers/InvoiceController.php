<?php

// namespace App\Http\Controllers;

// use App\Models\Invoice;
// use App\Models\Customer;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;

// class InvoiceController extends Controller
// {
//     public function index()
//     {
//         $invoices = Invoice::with('customer')->latest()->paginate(10);
//         return view('invoices.index', compact('invoices'));
//     }

//     public function create()
//     {
//         $customers = Customer::where('is_active', true)->get();
//         return view('invoices.create', compact('customers'));
//     }

//     public function store(Request $request)
//     {
//         DB::transaction(function () use ($request) {
//             $validated = $request->validate([
//                 'customer_id' => 'required|exists:customers,id',
//                 'invoice_date' => 'required|date',
//                 'due_date' => 'required|date|after_or_equal:invoice_date',
//                 'items' => 'required|array|min:1',
//                 'items.*.description' => 'required|string',
//                 'items.*.quantity' => 'required|numeric|min:0',
//                 'items.*.unit_price' => 'required|numeric|min:0',
//                 'items.*.tax_rate' => 'required|numeric|min:0',
//                 'notes' => 'nullable|string'
//             ]);

//             $invoice = Invoice::create([
//                 'customer_id' => $validated['customer_id'],
//                 'invoice_date' => $validated['invoice_date'],
//                 'due_date' => $validated['due_date'],
//                 'notes' => $validated['notes'],
//                 'invoice_number' => $this->generateInvoiceNumber(),
//                 'created_by' => auth()->id()
//             ]);

//             $subtotal = 0;
//             $tax_amount = 0;

//             foreach ($validated['items'] as $item) {
//                 $item_subtotal = $item['quantity'] * $item['unit_price'];
//                 $item_tax = $item_subtotal * ($item['tax_rate'] / 100);
                
//                 $invoice->items()->create([
//                     'description' => $item['description'],
//                     'quantity' => $item['quantity'],
//                     'unit_price' => $item['unit_price'],
//                     'tax_rate' => $item['tax_rate'],
//                     'tax_amount' => $item_tax,
//                     'subtotal' => $item_subtotal,
//                     'total' => $item_subtotal + $item_tax
//                 ]);

//                 $subtotal += $item_subtotal;
//                 $tax_amount += $item_tax;
//             }

//             $total = $subtotal + $tax_amount;

//             $invoice->update([
//                 'subtotal' => $subtotal,
//                 'tax_amount' => $tax_amount,
//                 'total' => $total,
//                 'balance_due' => $total
//             ]);
//         });

//         return redirect()->route('invoices.index')
//             ->with('success', 'Invoice created successfully');
//     }

//     private function generateInvoiceNumber()
//     {
//         $latest = Invoice::latest()->first();
//         $number = $latest ? intval(substr($latest->invoice_number, 3)) + 1 : 1;
//         return 'INV' . str_pad($number, 6, '0', STR_PAD_LEFT);
//     }

//     public function show(Invoice $invoice)
//     {
//         $invoice->load(['customer', 'items', 'payments']);
//         return view('invoices.show', compact('invoice'));
//     }

//     // Add other methods for edit, update, delete, etc.
//   //
    

//     /**
//      * Show the form for editing the specified resource.
//      */
//     public function edit($id)
//     {
//         // Retrieve the invoice by its ID
//         $invoice = Invoice::findOrFail($id);
        
//         // Fetch active customers for the dropdown
//         $customers = Customer::where('is_active', true)->get();
        
//         // Return the edit view with the current invoice data and customers
//         return view('invoices.edit', compact('invoice', 'customers'));
//     }
    

//     /**
//      * Update the specified resource in storage.
//      */
//     public function update(Request $request, $id)
//     {
//         // Validate the incoming request
//         $validated = $request->validate([
//             'customer_id' => 'required|exists:customers,id',
//             'invoice_date' => 'required|date',
//             'due_date' => 'required|date|after_or_equal:invoice_date',
//             'items' => 'required|array|min:1',
//             'items.*.description' => 'required|string',
//             'items.*.quantity' => 'required|numeric|min:1',
//             'items.*.unit_price' => 'required|numeric|min:0',
//             'items.*.tax_rate' => 'required|numeric|min:0',
//             'notes' => 'nullable|string'
//         ]);
    
//         // Find the invoice to update
//         $invoice = Invoice::findOrFail($id);
    
//         // Update the invoice data
//         $invoice->update([
//             'customer_id' => $validated['customer_id'],
//             'invoice_date' => $validated['invoice_date'],
//             'due_date' => $validated['due_date'],
//             'notes' => $validated['notes'],
//             'updated_by' => auth()->id(),
//         ]);
    
//         // Update invoice items (this assumes you need to delete old items and re-add them)
//         $invoice->items()->delete(); // Remove old items
    
//         $subtotal = 0;
//         $tax_amount = 0;
    
//         foreach ($validated['items'] as $item) {
//             $item_subtotal = $item['quantity'] * $item['unit_price'];
//             $item_tax = $item_subtotal * ($item['tax_rate'] / 100);
    
//             // Re-add the items
//             $invoice->items()->create([
//                 'description' => $item['description'],
//                 'quantity' => $item['quantity'],
//                 'unit_price' => $item['unit_price'],
//                 'tax_rate' => $item['tax_rate'],
//                 'tax_amount' => $item_tax,
//                 'subtotal' => $item_subtotal,
//                 'total' => $item_subtotal + $item_tax
//             ]);
    
//             $subtotal += $item_subtotal;
//             $tax_amount += $item_tax;
//         }
    
//         // Update totals
//         $total = $subtotal + $tax_amount;
//         $invoice->update([
//             'subtotal' => $subtotal,
//             'tax_amount' => $tax_amount,
//             'total' => $total,
//             'balance_due' => $total
//         ]);
    
//         return redirect()->route('invoices.index')
//             ->with('success', 'Invoice updated successfully');
//     }
    

//     /**
//      * Remove the specified resource from storage.
//      */
//     public function destroy($id)
//     {
//         // Find the invoice by its ID
//         $invoice = Invoice::findOrFail($id);
    
//         // Start a transaction to ensure safe deletion
//         DB::transaction(function () use ($invoice) {
//             // Delete related items
//             $invoice->items()->delete();
            
//             // Delete the invoice
//             $invoice->delete();
//         });
    
//         return redirect()->route('invoices.index')
//             ->with('success', 'Invoice deleted successfully');
//     }
    
// }




namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

// class InvoiceController extends Controller
// {
//     public function index()
//     {
//         $invoices = Invoice::with('customer')->latest()->paginate(10);
//         return view('invoices.index', compact('invoices'));
//     }

//     public function create()
//     {
//         $customers = Customer::where('is_active', true)->get();
//         return view('invoices.create', compact('customers'));
//     }

//     public function store(Request $request)
//     {
//         $validated = $request->validate([
//             'customer_id' => 'required|exists:customers,id',
//             'invoice_date' => 'required|date',
//             'due_date' => 'required|date|after_or_equal:invoice_date',
//             'items' => 'required|array|min:1',
//             'items.*.description' => 'required|string',
//             'items.*.quantity' => 'required|numeric|min:0',
//             'items.*.unit_price' => 'required|numeric|min:0',
//             'items.*.tax_rate' => 'required|numeric|min:0',
//             'notes' => 'nullable|string'
//         ]);

//         DB::transaction(function () use ($validated, $request) {
//             $invoice = Invoice::create([
//                 'customer_id' => $validated['customer_id'],
//                 'invoice_date' => $validated['invoice_date'],
//                 'due_date' => $validated['due_date'],
//                 'notes' => $validated['notes'] ?? null,
//                 'invoice_number' => 'INV-' . date('Y') . sprintf('%06d', Invoice::count() + 1),
//                 'created_by' => auth()->id(),
//                 'status' => 'draft'
//             ]);

//             foreach ($validated['items'] as $item) {
//                 $subtotal = $item['quantity'] * $item['unit_price'];
//                 $tax_amount = $subtotal * ($item['tax_rate'] / 100);
                
//                 $invoice->items()->create([
//                     'description' => $item['description'],
//                     'quantity' => $item['quantity'],
//                     'unit_price' => $item['unit_price'],
//                     'tax_rate' => $item['tax_rate'],
//                     'tax_amount' => $tax_amount,
//                     'subtotal' => $subtotal,
//                     'total' => $subtotal + $tax_amount
//                 ]);
//             }

//             // Update invoice totals
//             $totals = $invoice->items()->selectRaw('
//                 SUM(subtotal) as subtotal,
//                 SUM(tax_amount) as tax_amount,
//                 SUM(total) as total
//             ')->first();

//             $invoice->update([
//                 'subtotal' => $totals->subtotal,
//                 'tax_amount' => $totals->tax_amount,
//                 'total' => $totals->total,
//                 'balance_due' => $totals->total
//             ]);
//         });

//         return redirect()->route('invoices.index')
//             ->with('success', 'Invoice created successfully.');
//     }

//     public function show(Invoice $invoice)
//     {
//         $invoice->load(['customer', 'items', 'payments']);
//         return view('invoices.show', compact('invoice'));
//     }

//     public function markAsSent(Invoice $invoice)
//     {
//         $invoice->update(['status' => 'sent']);
//         return redirect()->back()->with('success', 'Invoice marked as sent.');
//     }
// }



class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'createdBy']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('invoice_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('invoice_date', '<=', $request->end_date);
        }

        $invoices = $query->latest()->paginate(10);

        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)
            ->orderBy('name')
            ->get();
            
        return view('invoices.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000'
        ]);

        DB::transaction(function () use ($validated, $request) {
            // Create invoice
            // $invoice = Invoice::create([
            //     'customer_id' => $validated['customer_id'],
            //     'invoice_date' => $validated['invoice_date'],
            //     'due_date' => $validated['due_date'],
            //     'notes' => $validated['notes'] ?? null,
            //     'invoice_number' => $this->generateInvoiceNumber(),
            //     //'created_by' => auth()->id(),
            //     'status' => 'draft'


            $invoice = Invoice::create([
                'customer_id' => $validated['customer_id'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'notes' => $validated['notes'] ?? null,
                'invoice_number' => $this->generateInvoiceNumber(),
                'created_by' => 1,  // Set a temporary value until auth is implemented
                'status' => 'draft'

            ]);

            // Create invoice items
            foreach ($validated['items'] as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                $tax_amount = $subtotal * ($item['tax_rate'] / 100);
                
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'],
                    'tax_amount' => $tax_amount,
                    'subtotal' => $subtotal,
                    'total' => $subtotal + $tax_amount
                ]);
            }

            // Update invoice totals
            $this->updateInvoiceTotals($invoice);
        });

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'items', 'payments', 'createdBy']);
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.index')
                ->with('error', 'Can only edit draft invoices.');
        }

        $customers = Customer::where('is_active', true)->get();
        return view('invoices.edit', compact('invoice', 'customers'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.index')
                ->with('error', 'Can only edit draft invoices.');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000'
        ]);

        DB::transaction(function () use ($validated, $invoice) {
            // Update invoice
            $invoice->update([
                'customer_id' => $validated['customer_id'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'notes' => $validated['notes'] ?? null
            ]);

            // Delete existing items
            $invoice->items()->delete();

            // Create new items
            foreach ($validated['items'] as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                $tax_amount = $subtotal * ($item['tax_rate'] / 100);
                
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'],
                    'tax_amount' => $tax_amount,
                    'subtotal' => $subtotal,
                    'total' => $subtotal + $tax_amount
                ]);
            }

            // Update invoice totals
            $this->updateInvoiceTotals($invoice);
        });

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.index')
                ->with('error', 'Can only delete draft invoices.');
        }

        DB::transaction(function () use ($invoice) {
            $invoice->items()->delete();
            $invoice->delete();
        });

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    public function markAsSent(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.index')
                ->with('error', 'Invoice is already sent.');
        }

        $invoice->update(['status' => 'sent']);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice marked as sent.');
    }

    public function downloadPdf(Invoice $invoice)
    {
        $invoice->load(['customer', 'items']);
        $pdf = PDF::loadView('invoices.pdf', compact('invoice'));
        
        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    private function generateInvoiceNumber()
    {
        $prefix = 'INV-' . date('Y');
        $lastInvoice = Invoice::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice->invoice_number, -6));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . sprintf('%06d', $newNumber);
    }

    private function updateInvoiceTotals(Invoice $invoice)
    {
        $totals = $invoice->items()
            ->selectRaw('
                SUM(subtotal) as subtotal,
                SUM(tax_amount) as tax_amount,
                SUM(total) as total
            ')
            ->first();

        $invoice->update([
            'subtotal' => $totals->subtotal,
            'tax_amount' => $totals->tax_amount,
            'total' => $totals->total,
            'balance_due' => $totals->total - $invoice->amount_paid
        ]);
    }
}

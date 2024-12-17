<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;

use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// class PaymentController extends Controller
// {
//     public function index()
//     {
//         $payments = Payment::with(['payable', 'createdBy'])
//             ->latest()
//             ->paginate(10);
//         return view('payments.index', compact('payments'));
//     }

//     // public function create(Request $request)
//     // {

        
//     //     $invoices = Invoice::whereIn('status', ['sent', 'partial'])
//     //         ->where('balance_due', '>', 0)
//     //         ->get();
//     //     $bills = Bill::whereIn('status', ['received', 'partial'])
//     //         ->where('balance_due', '>', 0)
//     //         ->get();
        
//     //     return view('payments.create', compact('invoices', 'bills'));
//     // }


//     public function create(Request $request)
// {
//     // Validate the invoice_id from the request
//     $request->validate([
//         'invoice_id' => 'required|exists:invoices,id'
//     ]);

//     // Get the invoice
//     $invoice = Invoice::findOrFail($request->invoice_id);

//     // Check if invoice can accept payments
//     if ($invoice->status === 'draft' || $invoice->status === 'paid') {
//         return redirect()->route('invoices.show', $invoice)
//             ->with('error', 'Cannot record payment for this invoice.');
//     }

//     return view('payments.create', compact('invoice'));
// }

//     public function store(Request $request)
//     {
//         $validated = $request->validate([
//             'payable_type' => 'required|in:Invoice,Bill',
//             'payable_id' => 'required|integer',
//             'amount' => 'required|numeric|min:0',
//             'payment_date' => 'required|date',
//             'payment_method' => 'required|string|in:cash,bank_transfer,check,credit_card',
//             'reference_number' => 'nullable|string|max:50',
//             'notes' => 'nullable|string|max:500'
//         ]);

//         DB::transaction(function () use ($validated) {
//             // Get the payable model (Invoice or Bill)
//             $payableType = $validated['payable_type'];
//             $payableModel = $payableType === 'Invoice' ? Invoice::class : Bill::class;
//             $payable = $payableModel::findOrFail($validated['payable_id']);

//             // Validate payment amount doesn't exceed balance due
//             if ($validated['amount'] > $payable->balance_due) {
//                 throw new \Exception('Payment amount cannot exceed balance due.');
//             }

//             // Create payment
//             $payment = new Payment([
//                 'payment_number' => $this->generatePaymentNumber(),
//                 'amount' => $validated['amount'],
//                 'payment_date' => $validated['payment_date'],
//                 'payment_method' => $validated['payment_method'],
//                 'reference_number' => $validated['reference_number'],
//                 'notes' => $validated['notes'],
//                 'created_by' => auth()->id()
//             ]);

//             $payable->payments()->save($payment);

//             // Update payable status and amounts
//             $totalPaid = $payable->payments()->sum('amount');
//             $newStatus = $this->determinePayableStatus($totalPaid, $payable->total);
            
//             $payable->update([
//                 'amount_paid' => $totalPaid,
//                 'balance_due' => $payable->total - $totalPaid,
//                 'status' => $newStatus
//             ]);

//             // Create journal entry for the payment
//             $this->createJournalEntry($payment);
//         });

//         return redirect()->route('payments.index')
//             ->with('success', 'Payment recorded successfully.');
//     }

//     public function show(Payment $payment)
//     {
//         $payment->load(['payable', 'createdBy']);
//         return view('payments.show', compact('payment'));
//     }

//     public function edit(Payment $payment)
//     {
//         if ($payment->payable->status === 'paid') {
//             return redirect()->route('payments.index')
//                 ->with('error', 'Cannot edit payment for fully paid invoice/bill.');
//         }

//         return view('payments.edit', compact('payment'));
//     }

//     public function update(Request $request, Payment $payment)
//     {
//         $validated = $request->validate([
//             'payment_date' => 'required|date',
//             'payment_method' => 'required|string|in:cash,bank_transfer,check,credit_card',
//             'reference_number' => 'nullable|string|max:50',
//             'notes' => 'nullable|string|max:500'
//         ]);

//         DB::transaction(function () use ($validated, $payment) {
//             $payment->update($validated);
            
//             // Recalculate payable totals
//             $payable = $payment->payable;
//             $totalPaid = $payable->payments()->sum('amount');
//             $newStatus = $this->determinePayableStatus($totalPaid, $payable->total);
            
//             $payable->update([
//                 'amount_paid' => $totalPaid,
//                 'balance_due' => $payable->total - $totalPaid,
//                 'status' => $newStatus
//             ]);
//         });

//         return redirect()->route('payments.index')
//             ->with('success', 'Payment updated successfully.');
//     }

//     public function destroy(Payment $payment)
//     {
//         if (!in_array($payment->payable->status, ['draft', 'sent', 'partial'])) {
//             return redirect()->route('payments.index')
//                 ->with('error', 'Cannot delete payment for completed invoice/bill.');
//         }

//         DB::transaction(function () use ($payment) {
//             // Update payable amounts and status
//             $payable = $payment->payable;
//             $payment->delete();
            
//             $totalPaid = $payable->payments()->sum('amount');
//             $newStatus = $this->determinePayableStatus($totalPaid, $payable->total);
            
//             $payable->update([
//                 'amount_paid' => $totalPaid,
//                 'balance_due' => $payable->total - $totalPaid,
//                 'status' => $newStatus
//             ]);
//         });

//         return redirect()->route('payments.index')
//             ->with('success', 'Payment deleted successfully.');
//     }

//     private function generatePaymentNumber()
//     {
//         $prefix = 'PAY-' . date('Y');
//         $lastPayment = Payment::where('payment_number', 'like', $prefix . '%')
//             ->orderBy('payment_number', 'desc')
//             ->first();

//         if ($lastPayment) {
//             $lastNumber = intval(substr($lastPayment->payment_number, -6));
//             $newNumber = $lastNumber + 1;
//         } else {
//             $newNumber = 1;
//         }

//         return $prefix . sprintf('%06d', $newNumber);
//     }

//     private function determinePayableStatus($totalPaid, $total)
//     {
//         if ($totalPaid >= $total) {
//             return 'paid';
//         } elseif ($totalPaid > 0) {
//             return 'partial';
//         }
//         return 'sent'; // or 'received' for bills
//     }

//     private function createJournalEntry(Payment $payment)
//     {
//         // Implementation depends on your accounting logic
//         // This would create the appropriate double-entry accounting records
//         // for the payment in your journal_entries and journal_lines tables
//     }

//     public function getPayableDetails(Request $request)
//     {
//         $validated = $request->validate([
//             'payable_type' => 'required|in:Invoice,Bill',
//             'payable_id' => 'required|integer'
//         ]);

//         $payableType = $validated['payable_type'];
//         $payableModel = $payableType === 'Invoice' ? Invoice::class : Bill::class;
//         $payable = $payableModel::findOrFail($validated['payable_id']);

//         return response()->json([
//             'total' => $payable->total,
//             'balance_due' => $payable->balance_due,
//             'currency' => 'GHC' // Or get from your settings
//         ]);
//     }
// }





//namespace App\Http\Controllers;

//use App\Models\Payment;
//use App\Models\Invoice;
//use App\Models\Bill;
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\DB;


DB::enableQueryLog();

class PaymentController extends Controller
{
    // public function index(Request $request)
    // {
    //     $query = Payment::with(['payable', 'createdBy']);

    //     // Search functionality
    //     if ($request->has('search')) {
    //         $search = $request->get('search');
    //         $query->where('payment_number', 'like', "%{$search}%")
    //               ->orWhere('reference_number', 'like', "%{$search}%");
    //     }

    //     // Filter by date range
    //     if ($request->has('start_date')) {
    //         $query->whereDate('payment_date', '>=', $request->start_date);
    //     }
    //     if ($request->has('end_date')) {
    //         $query->whereDate('payment_date', '<=', $request->end_date);
    //     }

    //     // Filter by payment method
    //     if ($request->has('payment_method')) {
    //         $query->where('payment_method', $request->payment_method);
    //     }

    //     $payments = $query->latest()->paginate(10);

    //     return view('payments.index', compact('payments'));
    // }

    // public function create(Request $request)
    // {
    //     // Validate the request
    //     $request->validate([
    //         'payable_type' => 'required|in:Invoice,Bill', // Ensures only valid types
    //         'payable_id' => 'required|integer'
    //     ]);
    
    //     // Dynamically determine the model (Invoice or Bill)
    //     $payableType = $request->payable_type;
    //     $payableModel = $payableType === 'Invoice' ? Invoice::class : Bill::class;
    
    //     // Retrieve the entity using the payable_id
    //     $payable = $payableModel::findOrFail($request->payable_id);
    
    //     // Check if the entity is eligible for payments
    //     if (in_array($payable->status, ['draft', 'paid'])) {
    //         return redirect()->route(strtolower($payableType) . 's.show', $payable->id)
    //             ->with('error', 'Cannot record payment for this ' . strtolower($payableType) . '.');
    //     }
    
    //     // Return the payment creation view
    //     return view('payments.create', [
    //         'payable' => $payable,
    //         'payableType' => $payableType
    //     ]);
    // }
    

    public function index(Request $request)
{
    $query = Payment::with(['payable', 'createdBy']);

    // Search by payment number or reference number
    if ($request->has('search') && $request->get('search')) {
        $search = $request->get('search');
        $query->where('payment_number', 'like', "%{$search}%")
              ->orWhere('reference_number', 'like', "%{$search}%");
    }

    // Filter by payment method
    if ($request->has('payment_method') && $request->get('payment_method')) {
        $query->where('payment_method', $request->payment_method);
    }

    // Filter by date range
    if ($request->has('start_date') && $request->get('start_date')) {
        $query->whereDate('payment_date', '>=', $request->start_date);
    }
    if ($request->has('end_date') && $request->get('end_date')) {
        $query->whereDate('payment_date', '<=', $request->end_date);
    }

    // Paginate results
    $payments = $query->latest()->paginate(10);

    return view('payments.index', compact('payments'));
}


    public function create(Request $request)
{
    // Validate the request
    $request->validate([
        'payable_type' => 'required|in:Invoice,Bill', // Ensures only valid types
        'payable_id' => 'required|integer'           // Ensures the ID is valid
    ]);

    // Dynamically determine the model (Invoice or Bill)
    $payableType = $request->payable_type;
    $payableModel = $payableType === 'Invoice' ? Invoice::class : Bill::class;

    // Retrieve the entity using the payable_id
    $payable = $payableModel::findOrFail($request->payable_id);

    // Check if the entity is eligible for payments
    if (in_array($payable->status, ['draft', 'paid'])) {
        return redirect()->route(strtolower($payableType) . 's.show', $payable->id)
            ->with('error', 'Cannot record payment for this ' . strtolower($payableType) . '.');
    }

    // Pass the payable entity (bill or invoice) and type to the view
    return view('payments.create', [
        'payable' => $payable,
        'payableType' => $payableType
    ]);
}


    // public function store(Request $request)
    // {
    //     //dd($request->all());
    //     $validated = $request->validate([
    //         'payable_type' => 'required|in:Invoice,Bill',
    //         'payable_id' => 'required|integer',
    //         'amount' => 'required|numeric|min:0.01',
    //         'payment_date' => 'required|date',
    //         'payment_method' => 'required|string|in:cash,bank_transfer,check,credit_card',
    //         'reference_number' => 'nullable|string|max:50',
    //         'notes' => 'nullable|string|max:500'
    //     ]);

    //     try {
    //         DB::transaction(function () use ($validated) {
    //             // Get the payable model (Invoice or Bill)
    //             $payableType = $validated['payable_type'];
    //             $payableModel = $payableType === 'Invoice' ? Invoice::class : Bill::class;
    //             $payable = $payableModel::findOrFail($validated['payable_id']);

    //             // Validate payment amount doesn't exceed balance due
    //             if ($validated['amount'] > $payable->balance_due) {
    //                 throw new \Exception('Payment amount cannot exceed balance due.');
    //             }

    //             // Create payment
    //             $payment = new Payment([
    //                 'payment_number' => $this->generatePaymentNumber(),
    //                 'amount' => $validated['amount'],
    //                 'payment_date' => $validated['payment_date'],
    //                 'payment_method' => $validated['payment_method'],
    //                 'reference_number' => $validated['reference_number'],
    //                 'notes' => $validated['notes'],
    //                 'created_by' => auth()->id() ?? 1 // Temporary until auth is implemented
    //             ]);

    //             $payable->payments()->save($payment);

    //             // Update payable status and amounts
    //             $totalPaid = $payable->payments()->sum('amount');
    //             $newStatus = $this->determinePayableStatus($totalPaid, $payable->total);
                
    //             $payable->update([
    //                 'amount_paid' => $totalPaid,
    //                 'balance_due' => $payable->total - $totalPaid,
    //                 'status' => $newStatus
    //             ]);
    //         });

    //         return redirect()->route('invoices.show', $validated['payable_id'])
    //             ->with('success', 'Payment recorded successfully.');

    //     } catch (\Exception $e) {
    //         return redirect()->back()
    //             ->withInput()
    //             ->with('error', $e->getMessage());
    //     }
    // }

 //cl
    // public function store(Request $request)
    // {

    //     \Log::info('Store method called.', ['request' => $request->all()]);
    //    // dd('Store method is being executed');

    //     try {
    //         DB::beginTransaction();
    
    //         // Validate request
    //         $validated = $request->validate([
    //             'payable_type' => 'required|in:Invoice,Bill',
    //             'payable_id' => 'required|integer',
    //             'amount' => 'required|numeric|min:0.01',
    //             'payment_date' => 'required|date',
    //             'payment_method' => 'required|string|in:cash,bank_transfer,check,credit_card',
    //             'reference_number' => 'nullable|string|max:50',
    //             'notes' => 'nullable|string|max:500'
    //         ]);
    
    //         // Get the invoice
    //         $invoice = Invoice::findOrFail($validated['payable_id']);
    
    //         // Validate payment amount doesn't exceed balance due
    //         if ($validated['amount'] > $invoice->balance_due) {
    //             throw new \Exception('Payment amount cannot exceed balance due.');
    //         }
    
    //         // Create payment
    //         $payment = new Payment();
    //         $payment->payment_number = $this->generatePaymentNumber();
    //         $payment->payable_type = 'App\Models\Invoice';
    //         $payment->payable_id = $validated['payable_id'];
    //         $payment->amount = $validated['amount'];
    //         $payment->payment_date = $validated['payment_date'];
    //         $payment->payment_method = $validated['payment_method'];
    //         $payment->reference_number = $validated['reference_number'];
    //         $payment->notes = $validated['notes'];
    //         $payment->created_by = 1;
    
    //         if (!$payment->save()) {
    //             throw new \Exception('Failed to save payment');
    //         }
    
    //         // Calculate new totals
    //         $totalPaid = $invoice->payments()->sum('amount') + $validated['amount'];
    //         $newBalance = $invoice->total - $totalPaid;
    //         $newStatus = $this->determinePayableStatus($totalPaid, $invoice->total);
    
    //         // Update invoice directly
    //         $invoice->amount_paid = $totalPaid;
    //         $invoice->balance_due = $newBalance;
    //         $invoice->status = $newStatus;
    //         if (!$invoice->save()) {
    //             throw new \Exception('Failed to update invoice');
    //         }
    
    //         DB::commit();
    
    //         return redirect()->route('invoices.show', $validated['payable_id'])
    //             ->with('success', 'Payment recorded successfully.');
    
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return redirect()->back()
    //             ->withInput()
    //             ->with('error', $e->getMessage());
    //     }
    // }

//worked by chart able to access page for bill or invoice
    // public function store(Request $request)
    // {
    //     try {
    //         DB::beginTransaction();
    
    //         // Validate the incoming request
    //         $validated = $request->validate([
    //             'payable_type' => 'required|in:Invoice,Bill', // Invoice or Bill
    //             'payable_id' => 'required|integer',          // ID of the payable entity
    //             'amount' => 'required|numeric|min:0.01',    // Amount to pay
    //             'payment_date' => 'required|date',          // Date of payment
    //             'payment_method' => 'required|string|in:cash,bank_transfer,check,credit_card',
    //             'reference_number' => 'nullable|string|max:50',
    //             'notes' => 'nullable|string|max:500'
    //         ]);
    
    //         // Dynamically fetch the payable model (Invoice or Bill)
    //         $payableType = $validated['payable_type'];
    //         $payableModel = $payableType === 'Invoice' ? Invoice::class : Bill::class;
    //         $payable = $payableModel::findOrFail($validated['payable_id']);
    
    //         // Check if payment amount exceeds the balance due
    //         if ($validated['amount'] > $payable->balance_due) {
    //             throw new \Exception('Payment amount cannot exceed balance due.');
    //         }
    
    //         // Record the payment
    //         $payment = Payment::create([
    //             'payment_number' => $this->generatePaymentNumber(),
    //             'payable_type' => $payableType,
    //             'payable_id' => $payable->id,
    //             'amount' => $validated['amount'],
    //             'payment_date' => $validated['payment_date'],
    //             'payment_method' => $validated['payment_method'],
    //             'reference_number' => $validated['reference_number'],
    //             'notes' => $validated['notes'],
    //             'created_by' => auth()->id() ?? 1
    //         ]);
    
    //         // Update the payable entity (invoice or bill)
    //         $totalPaid = $payable->payments()->sum('amount');
    //         $newBalance = $payable->total - $totalPaid;
    //         $newStatus = $this->determinePayableStatus($totalPaid, $payable->total);
    
    //         $payable->update([
    //             'amount_paid' => $totalPaid,
    //             'balance_due' => $newBalance,
    //             'status' => $newStatus
    //         ]);
    
    //         DB::commit();
    
    //         return redirect()->route(strtolower($payableType) . 's.show', $payable->id)
    //             ->with('success', ucfirst($payableType) . ' payment recorded successfully.');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         \Log::error('Payment Error: ' . $e->getMessage());
    //         return redirect()->back()
    //             ->withInput()
    //             ->with('error', 'An error occurred: ' . $e->getMessage());
    //     }
    // }
    

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
    
            // Validate the request
            $validated = $request->validate([
                'payable_type' => 'required|in:Invoice,Bill',
                'payable_id' => 'required|integer',
                'amount' => 'required|numeric|min:0.01',
                'payment_date' => 'required|date',
                'payment_method' => 'required|string|in:cash,bank_transfer,check,credit_card',
                'reference_number' => 'nullable|string|max:50',
                'notes' => 'nullable|string|max:500'
            ]);
    
            // Dynamically resolve the payable model
            $payableType = $validated['payable_type'] === 'Invoice' ? Invoice::class : Bill::class;
            $payable = $payableType::findOrFail($validated['payable_id']);
    
            // Validate payment amount
            if ($validated['amount'] > $payable->balance_due) {
                throw new \Exception('Payment amount cannot exceed balance due.');
            }
    
            // Create the payment
            $payment = Payment::create([
                'payment_number' => $this->generatePaymentNumber(),
                'payable_type' => $payableType,
                'payable_id' => $payable->id,
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'],
                'notes' => $validated['notes'],
                'created_by' => auth()->id() ?? 1 // Default to admin for testing
            ]);
    
            // Update the payable amounts and status
            $totalPaid = $payable->payments()->sum('amount');
            $balanceDue = $payable->total - $totalPaid;
    
            // Determine the new status
            $newStatus = $this->determinePayableStatus($totalPaid, $payable->total);
    
            $payable->update([
                'amount_paid' => $totalPaid,
                'balance_due' => $balanceDue,
                'status' => $newStatus
            ]);
    
            DB::commit();
    
            // Redirect to the appropriate view
            $redirectRoute = $validated['payable_type'] === 'Invoice' ? 'invoices.show' : 'bills.show';
            return redirect()->route($redirectRoute, $payable->id)
                ->with('success', 'Payment recorded successfully.');
    
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payment Error: ' . $e->getMessage());

            \Log::info('Payment Details', [
                'Total Paid' => $totalPaid,
                'Balance Due' => $balanceDue,
                'Payable Type' => $payableType,
                'New Status' => $newStatus
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
    
    

  //chat
//   public function store(Request $request)
// {
//     try {
//         DB::beginTransaction();

//         // Validate request
//         $validated = $request->validate([
//             'payable_type' => 'required|in:Invoice,Bill',
//             'payable_id' => 'required|integer',
//             'amount' => 'required|numeric|min:0.01',
//             'payment_date' => 'required|date',
//             'payment_method' => 'required|string|in:cash,bank_transfer,check,credit_card',
//             'reference_number' => 'nullable|string|max:50',
//             'notes' => 'nullable|string|max:500'
//         ]);

//         // Determine the model (Invoice or Bill)
//         $payableType = $validated['payable_type'];
//         $payableModel = $payableType === 'Invoice' ? Invoice::class : Bill::class;
//         $payable = $payableModel::findOrFail($validated['payable_id']);

//         // Ensure payment amount does not exceed balance due
//         if ($validated['amount'] > $payable->balance_due) {
//             throw new \Exception('Payment amount cannot exceed the balance due.');
//         }

//         // Record the payment
//         $payment = Payment::create([
//             'payment_number' => $this->generatePaymentNumber(),
//             'payable_type' => $payableType,
//             'payable_id' => $validated['payable_id'],
//             'amount' => $validated['amount'],
//             'payment_date' => $validated['payment_date'],
//             'payment_method' => $validated['payment_method'],
//             'reference_number' => $validated['reference_number'],
//             'notes' => $validated['notes'],
//             'created_by' => auth()->id() ?? 1, // Temporary fallback until authentication is implemented
//         ]);

//         // Update payable (Invoice or Bill) amounts
//         $totalPaid = $payable->payments()->sum('amount');
//         $balanceDue = $payable->total - $totalPaid;

//         // Determine the new status
//         $newStatus = $this->determinePayableStatus($totalPaid, $payable->total);

//         $payable->update([
//             'amount_paid' => $totalPaid,
//             'balance_due' => $balanceDue,
//             'status' => $newStatus,
//         ]);

//         DB::commit();

//         return redirect()->route('invoices.show', $payable->id)
//             ->with('success', 'Payment recorded successfully.');
//     } catch (\Exception $e) {
//         DB::rollBack();
//         \Log::error('Payment Error', ['message' => $e->getMessage()]);
//         return redirect()->back()
//             ->withInput()
//             ->with('error', 'An error occurred: ' . $e->getMessage());
//     }
// }

//
// public function store(Request $request)
// {
//     \Log::info('Store method started.');

//     // Validate request
//     $validated = $request->validate([
//         'payable_type' => 'required|in:Invoice,Bill',
//         'payable_id' => 'required|integer',
//         'amount' => 'required|numeric|min:0.01',
//         'payment_date' => 'required|date',
//         'payment_method' => 'required|string|in:cash,bank_transfer,check,credit_card',
//         'reference_number' => 'nullable|string|max:50',
//         'notes' => 'nullable|string|max:500'
//     ]);
//     \Log::info('Validation passed.', ['validated' => $validated]);

//     DB::transaction(function () use ($validated) {
//         \Log::info('Transaction started.');

//         // Fetch the payable model
//         $payableType = $validated['payable_type'];
//         $payableModel = $payableType === 'Invoice' ? Invoice::class : Bill::class;
//         $payable = $payableModel::findOrFail($validated['payable_id']);
//         \Log::info('Payable fetched.', ['payable' => $payable]);

//         // Ensure payment amount does not exceed balance due
//         if ($validated['amount'] > $payable->balance_due) {
//             \Log::error('Payment amount exceeds balance due.');
//             throw new \Exception('Payment amount cannot exceed balance due.');
//         }

//         // Create payment
//         $payment = Payment::create([
//             'payment_number' => $this->generatePaymentNumber(),
//             'payable_type' => $payableType,
//             'payable_id' => $validated['payable_id'],
//             'amount' => $validated['amount'],
//             'payment_date' => $validated['payment_date'],
//             'payment_method' => $validated['payment_method'],
//             'reference_number' => $validated['reference_number'],
//             'notes' => $validated['notes'],
//             'created_by' => auth()->id() ?? 1
//         ]);
//         \Log::info('Payment created.', ['payment' => $payment]);

//         // Update the payable's amounts
//         $totalPaid = $payable->payments()->sum('amount');
//         $balanceDue = $payable->total - $totalPaid;
//         $newStatus = $this->determinePayableStatus($totalPaid, $payable->total);

//         $payable->update([
//             'amount_paid' => $totalPaid,
//             'balance_due' => $balanceDue,
//             'status' => $newStatus
//         ]);
//         \Log::info('Payable updated.', [
//             'total_paid' => $totalPaid,
//             'balance_due' => $balanceDue,
//             'status' => $newStatus
//         ]);
//     });

//     DB::commit();
//     \Log::info('Transaction committed.');

//     return redirect()->route('invoices.show', $validated['payable_id'])
//         ->with('success', 'Payment recorded successfully.');
// }


// public function store(Request $request)
// {
//     try {
//         DB::beginTransaction();

//         // Validate request
//         $validated = $request->validate([
//             'payable_type' => 'required|in:Invoice,Bill',
//             'payable_id' => 'required|integer',
//             'amount' => 'required|numeric|min:0.01',
//             'payment_date' => 'required|date',
//             'payment_method' => 'required|string|in:cash,bank_transfer,check,credit_card',
//             'reference_number' => 'nullable|string|max:50',
//             'notes' => 'nullable|string|max:500'
//         ]);

//         // Determine the payable type
//         $payableType = $validated['payable_type'];
//         $payableModel = $payableType === 'Invoice' ? Invoice::class : Bill::class;
//         $payable = $payableModel::findOrFail($validated['payable_id']);

//         // Validate payment amount
//         if ($validated['amount'] > $payable->balance_due) {
//             throw new \Exception('Payment amount cannot exceed balance due.');
//         }

//         // Create the payment
//         $payment = Payment::create([
//             'payment_number' => $this->generatePaymentNumber(),
//             'payable_type' => $payableType,
//             'payable_id' => $validated['payable_id'],
//             'amount' => $validated['amount'],
//             'payment_date' => $validated['payment_date'],
//             'payment_method' => $validated['payment_method'],
//             'reference_number' => $validated['reference_number'],
//             'notes' => $validated['notes'],
//             'created_by' => auth()->id() ?? 1
//         ]);

//         // Update the payable (invoice or bill)
//         $totalPaid = $payable->payments()->sum('amount');
//         $balanceDue = $payable->total - $totalPaid;
//         $newStatus = $this->determinePayableStatus($totalPaid, $payable->total);

//         $payable->update([
//             'amount_paid' => $totalPaid,
//             'balance_due' => $balanceDue,
//             'status' => $newStatus
//         ]);

//         DB::commit();

//         return redirect()->route('invoices.show', $payable->id)
//             ->with('success', 'Payment recorded successfully.');
//     } catch (\Exception $e) {
//         DB::rollBack();
//         \Log::error('Payment Error', ['message' => $e->getMessage()]);
//         return redirect()->back()
//             ->withInput()
//             ->with('error', 'An error occurred while processing the payment: ' . $e->getMessage());
//     }
// }

    



    public function show(Payment $payment)
    {
        $payment->load(['payable', 'createdBy']);
        return view('payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        // Check if payment can be edited
        if ($payment->payable->status === 'paid') {
            return redirect()->route('payments.show', $payment)
                ->with('error', 'Cannot edit payment for fully paid invoice/bill.');
        }

        return view('payments.edit', compact('payment'));
    }

    public function update(Request $request, Payment $payment)
    {
        // Check if payment can be updated
        if ($payment->payable->status === 'paid') {
            return redirect()->route('payments.show', $payment)
                ->with('error', 'Cannot update payment for fully paid invoice/bill.');
        }

        $validated = $request->validate([
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|in:cash,bank_transfer,check,credit_card',
            'reference_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::transaction(function () use ($validated, $payment) {
                $payment->update($validated);
                
                // Recalculate payable totals
                $payable = $payment->payable;
                $totalPaid = $payable->payments()->sum('amount');
                $newStatus = $this->determinePayableStatus($totalPaid, $payable->total);
                
                $payable->update([
                    'amount_paid' => $totalPaid,
                    'balance_due' => $payable->total - $totalPaid,
                    'status' => $newStatus
                ]);
            });

            return redirect()->route('payments.show', $payment)
                ->with('success', 'Payment updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(Payment $payment)
    {
        // Check if payment can be deleted
        if (!in_array($payment->payable->status, ['draft', 'sent', 'partial'])) {
            return redirect()->route('payments.show', $payment)
                ->with('error', 'Cannot delete payment for fully paid invoice/bill.');
        }

        try {
            DB::transaction(function () use ($payment) {
                // Get payable before deleting payment
                $payable = $payment->payable;
                
                // Delete the payment
                $payment->delete();
                
                // Recalculate payable totals
                $totalPaid = $payable->payments()->sum('amount');
                $newStatus = $this->determinePayableStatus($totalPaid, $payable->total);
                
                $payable->update([
                    'amount_paid' => $totalPaid,
                    'balance_due' => $payable->total - $totalPaid,
                    'status' => $newStatus
                ]);
            });

            return redirect()->route('payments.index')
                ->with('success', 'Payment deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    private function generatePaymentNumber()
    {
        $prefix = 'PAY-' . date('Y');
        $lastPayment = Payment::where('payment_number', 'like', $prefix . '%')
            ->orderBy('payment_number', 'desc')
            ->first();

        if ($lastPayment) {
            $lastNumber = intval(substr($lastPayment->payment_number, -6));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . sprintf('%06d', $newNumber);
    }

    // private function determinePayableStatus($totalPaid, $total)
    // {
    //     if ($totalPaid >= $total) {
    //         return 'paid';
    //     } elseif ($totalPaid > 0) {
    //         return 'partial';
    //     }
    //     return 'sent'; // or 'received' for bills
    // }

//     private function determinePayableStatus($totalPaid, $total)
// {
//     \Log::info('Determining status', ['totalPaid' => $totalPaid, 'total' => $total]);

//     if ($totalPaid >= $total) {
//         return 'paid';
//     } elseif ($totalPaid > 0 && $totalPaid < $total) {
//         return 'partial';
//     }
//     return 'sent';
// }

private function determinePayableStatus($totalPaid, $total)
{
    if ($totalPaid >= $total) {
        return 'paid';
    } elseif ($totalPaid > 0 && $totalPaid < $total) {
        return 'partial'; // Handle partial payments properly
    }
    return 'sent'; // Default for unpaid amounts
}




//chat
// private function determinePayableStatus($totalPaid, $total)
// {
//     if ($totalPaid >= $total) {
//         return 'paid';
//     } elseif ($totalPaid > 0) {
//         return 'partial';
//     }
//     return 'sent'; // Default status for invoices with no payments
// }



}
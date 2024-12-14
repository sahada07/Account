<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Bill;
use App\Models\Payment;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PDFController extends Controller
{
    public function generateInvoicePDF(Invoice $invoice)
    {
        $invoice->load(['customer', 'items']);
        $pdf = PDF::loadView('pdfs.invoice', compact('invoice'));
        return $pdf->download('invoice-'.$invoice->invoice_number.'.pdf');
    }

    public function generateBillPDF(Bill $bill)
    {
        $bill->load(['vendor', 'items']);
        $pdf = PDF::loadView('pdfs.bill', compact('bill'));
        return $pdf->download('bill-'.$bill->bill_number.'.pdf');
    }

    public function generatePaymentReceiptPDF(Payment $payment)
    {
        $payment->load('payable');
        $pdf = PDF::loadView('pdfs.payment-receipt', compact('payment'));
        return $pdf->download('receipt-'.$payment->payment_number.'.pdf');
    }
}
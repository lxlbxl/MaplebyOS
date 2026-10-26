<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Finance\Entities\Payment;
use Modules\Quoting\Entities\Quote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(\Modules\Finance\Services\PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index(Request $request)
    {
        $query = Payment::with(['quote', 'quote.client', 'creator']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('transaction_reference', 'like', "%{$search}%")
                    ->orWhereHas('quote', function ($q2) use ($search) {
                        $q2->where('quote_number', 'like', "%{$search}%")
                            ->orWhereHas('client', function ($q3) use ($search) {
                                $q3->where('company_name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        $payments = $query->orderBy('payment_date', 'desc')
            ->paginate(10)
            ->withQueryString();

        $quotes = Quote::orderBy('quote_number')->select('id', 'quote_number', 'total_amount')->get();

        return Inertia::render('Finance/Payments/Index', [
            'payments' => $payments,
            'filters' => $request->only(['search']),
            'quotes' => $quotes,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'quote_id' => 'required|exists:quotes,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'transaction_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();

        $payment = Payment::create($validated);

        // Sync Status
        $quote = Quote::find($payment->quote_id);
        if ($quote) {
            // Auto-approve if needed
            if ($quote->status !== 'approved') {
                $quote->update(['status' => 'approved']);
            }
            $this->paymentService->syncQuoteStatus($quote);
        }

        return redirect()->back()->with('success', 'Payment recorded successfully.');
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'quote_id' => 'required|exists:quotes,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'transaction_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $payment->update($validated);

        $quote = Quote::find($payment->quote_id);
        if ($quote) {
            $this->paymentService->syncQuoteStatus($quote);
        }

        return redirect()->back()->with('success', 'Payment updated successfully.');
    }

    public function destroy(Payment $payment)
    {
        $quoteId = $payment->quote_id;
        $payment->delete();

        $quote = Quote::find($quoteId);
        if ($quote) {
            $this->paymentService->syncQuoteStatus($quote);
        }

        return redirect()->back()->with('success', 'Payment deleted successfully.');
    }
}


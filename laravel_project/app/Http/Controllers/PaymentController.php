<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Reservation;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Display a listing of the payments.
     */
    public function index()
    {
        $payments = Payment::with(['reservation.customer', 'reservation.room'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Payments/Index', [
            'payments' => $payments,
        ]);
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create(Request $request)
    {
        $request->validate([
            'reservation_id' => 'nullable|integer|exists:reservations,id',
        ]);

        $reservationId = $request->input('reservation_id');
        $reservation = $reservationId ? Reservation::findOrFail($reservationId) : null;

        return view('payments.create', compact('reservation'));
    }

    /**
     * Store a newly created payment in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:credit_card,bank_transfer,cash,digital_wallet',
            'payment_gateway' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $payment = $this->paymentService->createPayment($validated);

            if ($request->boolean('process_now')) {
                $this->paymentService->processPayment($payment);
            }

            return redirect()->route('payments.show', $payment)
                ->with('success', '決済を作成しました。');
        } catch (\Exception $e) {
            Log::error('Payment creation failed', ['error' => $e->getMessage()]);
            return back()->withInput()->withErrors(['error' => '決済の作成に失敗しました。']);
        }
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment)
    {
        $payment->load(['reservation.customer', 'reservation.room']);
        return Inertia::render('Payments/Show', [
            'payment' => $payment,
        ]);
    }

    /**
     * Process a payment
     */
    public function process(Payment $payment)
    {
        try {
            $this->paymentService->processPayment($payment);

            return redirect()->route('payments.show', $payment)
                ->with('success', '決済処理が完了しました。');
        } catch (\Exception $e) {
            Log::error('Payment processing failed', ['payment_id' => $payment->id, 'error' => $e->getMessage()]);
            return back()->withErrors(['error' => '決済処理に失敗しました。']);
        }
    }

    /**
     * Refund a payment
     */
    public function refund(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0|max:' . $payment->amount,
            'reason' => 'nullable|string',
        ]);

        try {
            $this->paymentService->refundPayment(
                $payment,
                $validated['amount'] ?? null,
                $validated['reason'] ?? null
            );

            return redirect()->route('payments.show', $payment)
                ->with('success', '返金処理が完了しました。');
        } catch (\Exception $e) {
            Log::error('Payment refund failed', ['payment_id' => $payment->id, 'error' => $e->getMessage()]);
            return back()->withErrors(['error' => '返金処理に失敗しました。']);
        }
    }

    /**
     * Cancel a payment
     */
    public function cancel(Payment $payment)
    {
        try {
            $this->paymentService->cancelPayment($payment);

            return redirect()->route('payments.show', $payment)
                ->with('success', '決済をキャンセルしました。');
        } catch (\Exception $e) {
            Log::error('Payment cancellation failed', ['payment_id' => $payment->id, 'error' => $e->getMessage()]);
            return back()->withErrors(['error' => '決済キャンセルに失敗しました。']);
        }
    }

    /**
     * Check payment status for a reservation
     */
    public function checkStatus(Reservation $reservation)
    {
        $status = $this->paymentService->checkPaymentStatus($reservation);

        return view('payments.status', compact('reservation', 'status'));
    }
}

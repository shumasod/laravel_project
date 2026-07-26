<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Customer;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReservationController extends Controller
{
    public function index()
    {
        Gate::authorize('admin');
        $reservations = Reservation::with('customer', 'room.accommodation')->paginate(10);
        return view('reservations.index', compact('reservations'));
    }

    public function create()
    {
        Gate::authorize('admin');
        $customers = Customer::all();
        $rooms = Room::where('is_available', true)->with('accommodation')->get();
        return view('reservations.create', compact('customers', 'rooms'));
    }

    public function store(Request $request)
    {
        Gate::authorize('admin');
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'status' => 'required|in:pending,confirmed',
            'total_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        Reservation::create($validated);

        return redirect()->route('reservations.index')
            ->with('success', '予約を登録しました。');
    }

    public function show(Reservation $reservation)
    {
        Gate::authorize('admin');
        $reservation->load('customer', 'room.accommodation');
        return view('reservations.show', compact('reservation'));
    }

    public function edit(Reservation $reservation)
    {
        Gate::authorize('admin');
        $customers = Customer::all();
        $rooms = Room::with('accommodation')->get();
        return view('reservations.edit', compact('reservation', 'customers', 'rooms'));
    }

    public function update(Request $request, Reservation $reservation)
    {
        Gate::authorize('admin');
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'status' => 'required|in:pending,confirmed,cancelled,completed',
            'total_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $reservation->update($validated);

        return redirect()->route('reservations.index')
            ->with('success', '予約を更新しました。');
    }

    public function destroy(Reservation $reservation)
    {
        Gate::authorize('admin');
        $reservation->delete();

        return redirect()->route('reservations.index')
            ->with('success', '予約を削除しました。');
    }
}

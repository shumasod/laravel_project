<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Customer;
use App\Models\Room;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with('customer', 'room.accommodation')->paginate(10);
        return view('reservations.index', compact('reservations'));
    }

    public function create()
    {
        $customers = Customer::all();
        $rooms = Room::where('is_available', true)->with('accommodation')->get();
        return view('reservations.create', compact('customers', 'rooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'room_id'        => 'required|exists:rooms,id',
            'check_in_date'  => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            // モデルの定数に合わせた値のみ許可 (total_amountはサービスが算出)
            'status'         => 'required|in:provisional,confirmed,cancelled',
            'notes'          => 'nullable|string|max:2000',
        ]);

        $room = \App\Models\Room::findOrFail($validated['room_id']);
        $nights = \Carbon\Carbon::parse($validated['check_in_date'])
            ->diffInDays(\Carbon\Carbon::parse($validated['check_out_date']));
        $validated['total_amount'] = $room->price_per_night * $nights;

        Reservation::create($validated);

        return redirect()->route('reservations.index')
            ->with('success', '予約を登録しました。');
    }

    public function show(Reservation $reservation)
    {
        $reservation->load('customer', 'room.accommodation');
        return view('reservations.show', compact('reservation'));
    }

    public function edit(Reservation $reservation)
    {
        $customers = Customer::all();
        $rooms = Room::with('accommodation')->get();
        return view('reservations.edit', compact('reservation', 'customers', 'rooms'));
    }

    public function update(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'room_id'        => 'required|exists:rooms,id',
            'check_in_date'  => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            // モデル定数に合わせた正しいステータス値 (total_amountは再計算)
            'status'         => 'required|in:provisional,confirmed,checked_in,checked_out,cancelled,no_show',
            'notes'          => 'nullable|string|max:2000',
        ]);

        $room = \App\Models\Room::findOrFail($validated['room_id']);
        $nights = \Carbon\Carbon::parse($validated['check_in_date'])
            ->diffInDays(\Carbon\Carbon::parse($validated['check_out_date']));
        $validated['total_amount'] = $room->price_per_night * $nights;

        $reservation->update($validated);

        return redirect()->route('reservations.index')
            ->with('success', '予約を更新しました。');
    }

    public function destroy(Reservation $reservation)
    {
        $reservation->delete();

        return redirect()->route('reservations.index')
            ->with('success', '予約を削除しました。');
    }
}

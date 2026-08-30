<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Accommodation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RoomController extends Controller
{
    public function index()
    {
        Gate::authorize('admin');

        $rooms = Room::with('accommodation')->paginate(10);
        return view('rooms.index', compact('rooms'));
    }

    public function create()
    {
        Gate::authorize('admin');

        $accommodations = Accommodation::all();
        return view('rooms.create', compact('accommodations'));
    }

    public function store(Request $request)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'accommodation_id' => 'required|exists:accommodations,id',
            'room_number' => 'required|string|max:255',
            'room_type' => 'required|string|max:255',
            'price_per_night' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'is_available' => 'boolean',
        ]);

        Room::create($validated);

        return redirect()->route('rooms.index')
            ->with('success', '部屋を登録しました。');
    }

    public function show(Room $room)
    {
        Gate::authorize('admin');

        $room->load('accommodation', 'reservations');
        return view('rooms.show', compact('room'));
    }

    public function edit(Room $room)
    {
        Gate::authorize('admin');

        $accommodations = Accommodation::all();
        return view('rooms.edit', compact('room', 'accommodations'));
    }

    public function update(Request $request, Room $room)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'accommodation_id' => 'required|exists:accommodations,id',
            'room_number' => 'required|string|max:255',
            'room_type' => 'required|string|max:255',
            'price_per_night' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'is_available' => 'boolean',
        ]);

        $room->update($validated);

        return redirect()->route('rooms.index')
            ->with('success', '部屋を更新しました。');
    }

    public function destroy(Room $room)
    {
        Gate::authorize('admin');

        $room->delete();

        return redirect()->route('rooms.index')
            ->with('success', '部屋を削除しました。');
    }
}

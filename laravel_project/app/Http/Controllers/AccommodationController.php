<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AccommodationController extends Controller
{
    public function index()
    {
        Gate::authorize('admin');

        $accommodations = Accommodation::with('rooms')->paginate(10);
        return view('accommodations.index', compact('accommodations'));
    }

    public function create()
    {
        Gate::authorize('admin');

        return view('accommodations.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        Accommodation::create($validated);

        return redirect()->route('accommodations.index')
            ->with('success', '宿泊施設を登録しました。');
    }

    public function show(Accommodation $accommodation)
    {
        Gate::authorize('admin');

        $accommodation->load('rooms');
        return view('accommodations.show', compact('accommodation'));
    }

    public function edit(Accommodation $accommodation)
    {
        Gate::authorize('admin');

        return view('accommodations.edit', compact('accommodation'));
    }

    public function update(Request $request, Accommodation $accommodation)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $accommodation->update($validated);

        return redirect()->route('accommodations.index')
            ->with('success', '宿泊施設を更新しました。');
    }

    public function destroy(Accommodation $accommodation)
    {
        Gate::authorize('admin');

        $accommodation->delete();

        return redirect()->route('accommodations.index')
            ->with('success', '宿泊施設を削除しました。');
    }
}

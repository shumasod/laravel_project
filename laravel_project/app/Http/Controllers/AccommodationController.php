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

    private function validationRules(): array
    {
        return [
            'name'                     => 'required|string|max:255',
            'address'                  => 'required|string|max:500',
            'description'              => 'nullable|string|max:5000',
            'phone'                    => 'nullable|string|max:20',
            'email'                    => 'nullable|email|max:255',
            'facility_type'            => 'nullable|string|max:100',
            'description_long'         => 'nullable|string',
            'check_in_time'            => 'nullable|string|max:10',
            'check_out_time'           => 'nullable|string|max:10',
            'check_in_end'             => 'nullable|string|max:10',
            'latitude'                 => 'nullable|numeric|between:-90,90',
            'longitude'                => 'nullable|numeric|between:-180,180',
            'area_id'                  => 'nullable|integer|exists:areas,id',
            'nearest_station_id'       => 'nullable|integer|exists:stations,id',
            'station_distance_minutes' => 'nullable|integer|min:0|max:9999',
            'star_rating'              => 'nullable|integer|min:1|max:5',
            'highlight_features'       => 'nullable|array',
            'highlight_features.*'     => 'nullable|string|max:200',
            'parking_info'             => 'nullable|array',
            'access_info'              => 'nullable|array',
            'min_price'                => 'nullable|integer|min:0',
            'max_price'                => 'nullable|integer|min:0|gte:min_price',
            'cancellation_policy_id'   => 'nullable|integer|exists:cancellation_policies,id',
        ];
    }

    public function store(Request $request)
    {
        Gate::authorize('admin');
        $validated = $request->validate($this->validationRules());
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'address'     => 'required|string|max:500',
            'description' => 'nullable|string|max:5000',
            'phone'       => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:255',
            'name' => 'required|string|max:255',
            'address'     => 'required|string|max:500',
            'description' => 'nullable|string|max:5000',
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
        $validated = $request->validate($this->validationRules());
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'address'     => 'required|string|max:500',
            'description' => 'nullable|string|max:5000',
            'phone'       => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:255',
            'name' => 'required|string|max:255',
            'address'     => 'required|string|max:500',
            'description' => 'nullable|string|max:5000',
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

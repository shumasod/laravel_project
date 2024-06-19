<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PechiPechiController extends Controller
{
    public function index()
    {
        return view('pechipechi.index');
    }

    public function check(Request $request)
    {
        $count = $request->input('count');
        $maxCount = 100;

        if ($count >= $maxCount) {
            return redirect()->route('punishment');
        }

        return response()->json(['count' => $count + 1]);
    }

    public function punishment()
    {
        return view('pechipechi.punishment');
    }
}

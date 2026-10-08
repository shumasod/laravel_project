<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Customer;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 顧客API (管理者のみ・レート制限付き)
Route::middleware(['auth:sanctum', 'can:admin', 'throttle:30,1'])->prefix('customers')->group(function () {
    // 顧客一覧取得
    Route::get('/', function (Request $request) {
        $validated = $request->validate([
            'search'   => 'nullable|string|max:100',
            'status'   => 'nullable|in:active,inactive,pending,suspended',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Customer::query();

        // 検索フィルター
        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // ステータスフィルター
        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        // ページネーション (上限100件)
        $perPage = $validated['per_page'] ?? 50;
        $customers = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $customers->items(),
            'meta' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
            ]
        ]);
    });

    // 顧客詳細取得
    Route::get('/{id}', function ($id) {
        $customer = Customer::with('reservations')->find($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => '顧客が見つかりません'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $customer
        ]);
    });

    // 顧客作成
    Route::post('/', function (Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'status' => 'nullable|in:active,inactive,pending,suspended'
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'success' => true,
            'data' => $customer,
            'message' => '顧客を作成しました'
        ], 201);
    });

    // 顧客更新
    Route::put('/{id}', function (Request $request, $id) {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => '顧客が見つかりません'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'email' => 'sometimes|required|email|max:255|unique:customers,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'status' => 'nullable|in:active,inactive,pending,suspended'
        ]);

        $customer->update($validated);

        return response()->json([
            'success' => true,
            'data' => $customer,
            'message' => '顧客情報を更新しました'
        ]);
    });

    // 顧客削除
    Route::delete('/{id}', function ($id) {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => '顧客が見つかりません'
            ], 404);
        }

        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => '顧客を削除しました'
        ]);
    });

    // 顧客統計
    Route::get('/stats/summary', function () {
        $total = Customer::count();
        $thisMonth = Customer::where('created_at', '>=', now()->startOfMonth())->count();
        $active = Customer::where('status', 'active')->count();
        $inactive = Customer::where('status', 'inactive')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'this_month' => $thisMonth,
                'active' => $active,
                'inactive' => $inactive
            ]
        ]);
    });
});

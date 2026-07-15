<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccommodationController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockTransactionController;
use App\Http\Controllers\ElectionController;
use App\Http\Controllers\TravelController;
use App\Http\Controllers\EventController;

Route::get('/', fn () => view('welcome'));

Route::middleware('auth')->group(function () {
    Route::resource('accommodations', AccommodationController::class);
    Route::resource('rooms', RoomController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('reservations', ReservationController::class);
    Route::resource('payments', PaymentController::class);
    Route::resource('reviews', ReviewController::class);

    // ===== 在庫管理 =====
    Route::get('/products/suggest', [ProductController::class, 'suggest'])->name('products.suggest');
    Route::resource('products', ProductController::class);
    Route::get('/products/{product}/qrcode', [ProductController::class, 'qrcode'])->name('products.qrcode');
    Route::get('/products/{product}/qrcode/download', [ProductController::class, 'qrcodeDownload'])->name('products.qrcode.download');
    Route::post('/products/{product}/stock-transactions', [StockTransactionController::class, 'store'])->name('stock-transactions.store');
    Route::get('/stock-transactions', [StockTransactionController::class, 'index'])->name('stock-transactions.index');

    Route::post('/payments/{payment}/process', [PaymentController::class, 'process'])->name('payments.process');
    Route::post('/payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');
    Route::post('/payments/{payment}/cancel', [PaymentController::class, 'cancel'])->name('payments.cancel');
    Route::post('/reviews/{review}/helpful', [ReviewController::class, 'addHelpfulVote'])->name('reviews.helpful');
    Route::post('/reviews/{review}/admin-response', [ReviewController::class, 'addAdminResponse'])->name('reviews.admin-response');
});

Route::middleware('auth')->group(function () {
    Route::get('/reports/dashboard', [ReportController::class, 'dashboard'])->name('reports.dashboard');
    Route::get('/reports/reservations', [ReportController::class, 'reservations'])->name('reports.reservations');
    Route::get('/reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
    Route::get('/reports/occupancy', [ReportController::class, 'occupancy'])->name('reports.occupancy');
    Route::get('/reports/reviews', [ReportController::class, 'reviews'])->name('reports.reviews');
    Route::get('/reports/customers', [ReportController::class, 'customers'])->name('reports.customers');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
});

Route::get('/travel', [TravelController::class, 'index'])->name('travel.index');
Route::get('/travel/search', [TravelController::class, 'search'])->name('travel.search');
Route::get('/travel/suggest', [TravelController::class, 'suggest'])->name('travel.suggest');
Route::get('/travel/accommodations/{id}', [TravelController::class, 'show'])->name('travel.show');
Route::get('/travel/accommodations/{id}/plans', [TravelController::class, 'searchPlans'])->name('travel.plans');
Route::get('/travel/accommodations/{id}/reviews', [TravelController::class, 'reviews'])->name('travel.reviews');
Route::get('/travel/booking/{plan}', [TravelController::class, 'booking'])->name('travel.booking');
Route::post('/travel/booking/confirm', [TravelController::class, 'confirmBooking'])->name('travel.booking.confirm');
Route::post('/travel/booking/complete', [TravelController::class, 'completeBooking'])->name('travel.booking.complete');
Route::post('/travel/favorites', [TravelController::class, 'addFavorite'])->name('travel.favorites.add');
Route::delete('/travel/favorites/{accommodation}', [TravelController::class, 'removeFavorite'])->name('travel.favorites.remove');
Route::get('/travel/areas', [TravelController::class, 'areas'])->name('travel.areas');
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/search', [EventController::class, 'search'])->name('events.search');
Route::get('/events/calendar', [EventController::class, 'calendar'])->name('events.calendar');
Route::get('/events/{id}', [EventController::class, 'show'])->name('events.show');
Route::get('/events/my/favorites', [EventController::class, 'favorites'])->name('events.favorites');
Route::post('/events/favorites', [EventController::class, 'addFavorite'])->name('events.favorites.add');
Route::delete('/events/favorites/{eventId}', [EventController::class, 'removeFavorite'])->name('events.favorites.remove');
Route::get('/api/events/search', [EventController::class, 'apiSearch'])->name('events.api.search');
Route::get('/elections/dashboard', [ElectionController::class, 'dashboard'])->name('elections.dashboard');
Route::get('/elections', [ElectionController::class, 'index'])->name('elections.index');
Route::post('/elections', [ElectionController::class, 'store'])->name('elections.store');
Route::get('/elections/{election}', [ElectionController::class, 'show'])->name('elections.show');
Route::post('/elections/{election}/predict', [ElectionController::class, 'predict'])->name('elections.predict');
Route::get('/elections/{election}/predictions', [ElectionController::class, 'getPredictions'])->name('elections.predictions');
Route::get('/elections/{election}/validate-accuracy', [ElectionController::class, 'validateAccuracy'])->name('elections.validate-accuracy');
Route::get('/elections-compare', [ElectionController::class, 'compare'])->name('elections.compare');
Route::post('/elections/{election}/results', [ElectionController::class, 'storeResult'])->name('elections.results.store');
Route::get('/poll-data', [ElectionController::class, 'pollData'])->name('poll-data.index');
Route::post('/poll-data', [ElectionController::class, 'storePollData'])->name('poll-data.store');
Route::get('/parties', [ElectionController::class, 'parties'])->name('parties.index');
Route::post('/parties', [ElectionController::class, 'storeParty'])->name('parties.store');
Route::get('/parties/{party}/trend', [ElectionController::class, 'partyTrend'])->name('parties.trend');
Route::get('/election-districts', [ElectionController::class, 'districts'])->name('districts.index');
Route::post('/elections/import-csv', [ElectionController::class, 'importCsv'])->name('elections.import-csv');
Route::get('/elections/{election}/export', [ElectionController::class, 'exportReport'])->name('elections.export');

<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockTransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/api/v1/products', [ProductController::class, 'apiSearch'])->name('api.products.search');
Route::get('/api/v1/products/low-stock', [ProductController::class, 'apiLowStock'])->name('api.products.low-stock');

Route::middleware('auth')->group(function () {
    Route::get('/', fn() => redirect()->route('products.index'));

    // Static product routes BEFORE resource
    Route::get('/products/reorder-list', [ProductController::class, 'reorderList'])->name('products.reorder-list');
    Route::get('/products/suggest', [ProductController::class, 'suggest'])->name('products.suggest');
    Route::get('/products/import', [ProductController::class, 'importForm'])->name('products.import.form');
    Route::post('/products/import', [ProductController::class, 'importCsv'])->name('products.import');

    Route::resource('products', ProductController::class);

    Route::get('/products/{product}/qrcode', [ProductController::class, 'qrcode'])->name('products.qrcode');
    Route::get('/products/{product}/qrcode/download', [ProductController::class, 'qrcodeDownload'])->name('products.qrcode.download');
    Route::get('/products/{product}/qrcode/download/svg', [ProductController::class, 'qrcodeSvgDownload'])->name('products.qrcode.download.svg');
    Route::get('/products/{product}/label', [ProductController::class, 'label'])->name('products.label');
    Route::post('/products/{product}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate');

    Route::get('/stock-transactions/export', [StockTransactionController::class, 'export'])->name('stock-transactions.export');
    Route::get('/stock-transactions/bulk', [StockTransactionController::class, 'bulk'])->name('stock-transactions.bulk');
    Route::post('/stock-transactions/bulk', [StockTransactionController::class, 'bulkStore'])->name('stock-transactions.bulk.store');
    Route::get('/stock-transactions', [StockTransactionController::class, 'index'])->name('stock-transactions.index');
    Route::post('/products/{product}/stock', [StockTransactionController::class, 'store'])->name('stock-transactions.store');
    Route::post('/products/{product}/quick-adjust', [StockTransactionController::class, 'quickAdjust'])->name('stock-transactions.quick-adjust');
});

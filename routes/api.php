<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CsvUploadController;

// CSV upload endpoint (guest)
Route::post('/upload-csv', [CsvUploadController::class, 'upload']);
Route::get('/uploads', [CsvUploadController::class, 'uploadHistory']);
Route::get('/products', [CsvUploadController::class, 'productList']);
Route::post('/uploads/clear', [CsvUploadController::class, 'clearUploads']);

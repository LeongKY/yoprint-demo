<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CsvUploadController;

// Redirect to upload
Route::get('/', function () {
    return redirect()->route('csv.upload.view');
});

// Public upload
Route::get('/', [CsvUploadController::class, 'uploadView'])->name('csv.upload.view');

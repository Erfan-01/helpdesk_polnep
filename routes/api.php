<?php

use App\Http\Controllers\Api\WebsiteRequestController;
use App\Http\Controllers\Api\ApplicationRequestController;
use App\Http\Controllers\Api\DataRequestController;
use App\Http\Controllers\Api\EmployeeQuestionController;
use App\Http\Controllers\Api\MasterDataController;
use App\Http\Controllers\Api\RemunerationQuestionController;
use App\Http\Controllers\Api\StatusController;
use App\Http\Controllers\Api\WifiInternetRequestController;
use Illuminate\Support\Facades\Route;


// MASTER DATA
Route::get('/units', [MasterDataController::class,'units']);
Route::get('/request-categories', [MasterDataController::class,'requestCategories']);
Route::get('/announcements', [MasterDataController::class,'announcements']);

// PERMINTAAN DATA
Route::post('/requests', [DataRequestController::class,'store']);

// KEPEGAWAIAN
Route::post('/kepegawaian', [EmployeeQuestionController::class,'store']);

// REMUNERASI
Route::post('/remunerasi',[RemunerationQuestionController::class,'store',]);

// CEK STATUS
Route::post('/status/requests', [StatusController::class,'requestStatus']);
Route::post('/status/kepegawaian', [StatusController::class,'employeeQuestionStatus']);

// LAYANAN APLIKASI
Route::post('/aplikasi',[ApplicationRequestController::class,'store',]);

// WEBSITE
Route::post('/website',[WebsiteRequestController::class,'store',]);

// WIFI / INTERNET
Route::post('/wifi-internet',[WifiInternetRequestController::class,'store',]);
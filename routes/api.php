<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\SalesSuspensionController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('login', [AuthenticationController::class, 'login'])->name('login')->middleware('guest');
Route::get('logout', [AuthenticationController::class, 'logout'])->name('logout');

Route::middleware('superadmin')->group(function () {
    Route::get('user', [UserController::class, 'index'])->name('user');
    Route::post('user/store', [UserController::class, 'store'])->name('user.store');
    Route::put('user/update/{id}', [UserController::class, 'update'])->name('user.update');
    Route::delete('user/delete/{id}', [UserController::class, 'destroy'])->name('user.delete');

});
Route::get('suspension', [SalesSuspensionController::class, 'index'])->name('suspension');
Route::post('suspension/store', [SalesSuspensionController::class, 'store'])->name('suspension.store');
Route::put('suspension/update/{id}', [SalesSuspensionController::class, 'update'])->name('suspension.update');
Route::delete('suspension/delete/{id}', [SalesSuspensionController::class, 'destroy'])->name('suspension.delete');

Route::middleware('cs')->group(function () {
    Route::get('leads', [LeadController::class, 'index'])->name('leads');
    Route::post('leads/store', [LeadController::class, 'store'])->name('leads.store');
    Route::put('leads/transfer/{id}', [LeadController::class, 'transferLeads'])->name('leads.transferLeads');
    Route::delete('leads/delete/{id}', [LeadController::class, 'destroy'])->name('leads.delete');
});

Route::middleware('sales')->group(function () {
    Route::post('follow-up/store/{lead_id}', [FollowUpController::class, 'store'])->name('follow-up.store');
});

Route::middleware('operational')->group(function () {
    Route::get('lead-survey', [LeadController::class, 'operationalLead'])->name('lead.survey');
    Route::post('survey/store/{lead_id}', [SurveyController::class, 'survey'])->name('survey.store');
});

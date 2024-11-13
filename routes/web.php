<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MerchantController;
use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebController::class,'homeView']);



Route::get('/merchant/onboarding',[MerchantController::class,'merchantOnboardingView']);
Route::post('/merchant/onboarding/step-{id}',[MerchantController::class,'merchantOnboardingStepsAJAX']);
Route::post('/merchant/onboarding/check-{type}',[MerchantController::class,'merchantOnboardingDataCheckAJAX']);

Route::get('/login',[WebController::class,'loginView']);
Route::post('/login/submit',[AuthController::class,'loginSubmit']);
Route::get('/logout',[WebController::class,'logout']);

Route::get('/dashboard',[AuthController::class,'navigateToDashboard']);

Route::get('/merchant/dashboard',[MerchantController::class,'merchantDashboardView']);

Route::get('/admin/dashboard',[AdminController::class,'adminDashboardView']);
Route::get('/admin/merchant/approval',[AdminController::class,'adminMerchantApprovalView']);
Route::get('/admin/account/details',[AdminController::class,'adminAccountDetailsView']);
Route::get('/admin/url/whitelisting',[AdminController::class,'adminUrlWhitelistingView']);
Route::get('/admin/settings',[AdminController::class,'adminSettingsView']);
Route::get('/admin/logs',[AdminController::class,'adminLogsView']);


// Route::get('/make/first-admin',[AdminController::class,'makeFirstAdmin']);
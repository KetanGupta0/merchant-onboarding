<?php

use App\Http\Controllers\MerchantController;
use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebController::class,'homeView']);

Route::get('/login',[WebController::class,'loginView']);
Route::get('/admin/dashboard',[WebController::class,'adminDashboardView']);
Route::get('/admin/merchant/approval',[WebController::class,'adminMerchantApprovalView']);
Route::get('/admin/account/details',[WebController::class,'adminAccountDetailsView']);
Route::get('/admin/url/whitelisting',[WebController::class,'adminUrlWhitelistingView']);
Route::get('/admin/settings',[WebController::class,'adminSettingsView']);
Route::get('/admin/logs',[WebController::class,'adminLogsView']);

Route::get('/logout',[WebController::class,'logout']);

Route::get('/merchant/onboarding',[MerchantController::class,'merchantOnboardingView']);
Route::post('/merchant/onboarding/step-{id}',[MerchantController::class,'merchantOnboardingStepsAJAX']);
Route::post('/merchant/onboarding/check-{type}',[MerchantController::class,'merchantOnboardingDataCheckAJAX']);
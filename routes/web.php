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

Route::get('/merchant/account/details',[MerchantController::class,'merchantAccountDetailsView']);
Route::post('/merchant/account/details/update',[MerchantController::class,'merchantAccountDetailsUpdate']);

Route::get('/merchant/url/whitelisting',[MerchantController::class,'merchantUrlWhitelistingView']);
Route::post('/merchant/url/whitelisting/request-{type}',[MerchantController::class,'merchantUrlWhitelistingRequest']);

Route::get('/merchant/settlement/report',[MerchantController::class,'merchantSettlementReportsView']);
Route::get('/merchant/settings',[MerchantController::class,'merchantSettingsView']);
Route::post('/merchant/settings/update',[MerchantController::class,'merchantSettingsUpdate']);

Route::get('/merchant/logs',[MerchantController::class,'merchantLogsView']);


Route::get('/admin/dashboard',[AdminController::class,'adminDashboardView']);

Route::get('/admin/merchant/approval',[AdminController::class,'adminMerchantApprovalView']);
Route::get('/admin/merchant/approval/view-{id}',[AdminController::class,'adminMerchantView']);
Route::post('/admin/merchant/approval/update/merchant-info',[AdminController::class,'adminMerchantInfoUpdate']);
Route::post('/admin/merchant/approval/update/business-info',[AdminController::class,'adminMerchantBusinessInfoUpdate']);
Route::post('/admin/merchant/approval/update/kyc-doc',[AdminController::class,'adminMerchantKycDocUpdate']);

Route::get('/admin/merchant/fetch',[AdminController::class,'adminMerchantFetchAJAX']);
Route::post('/admin/merchant/delete',[AdminController::class,'adminMerchantDeleteAJAX']);
Route::post('/admin/merchant/approval-{action}',[AdminController::class,'adminMerchantApprovalAJAX']);

Route::get('/admin/account/details',[AdminController::class,'adminAccountDetailsView']);
Route::get('/admin/account/details/view-{id}',[AdminController::class,'adminAccountDetailsEditView']);
Route::get('/admin/account/details/status/{status}-{id}',[AdminController::class,'adminAccountDetailsChangeStatus']);
Route::post('/admin/account/details/update',[AdminController::class,'adminAccountDetailsUpdate']);

Route::get('/admin/url/whitelisting',[AdminController::class,'adminUrlWhitelistingView']);
Route::get('/admin/url/whitelisting/request-active-{id}',[AdminController::class,'adminUrlWhitelistingRequestActive']);
Route::get('/admin/url/whitelisting/request-inactive{id}',[AdminController::class,'adminUrlWhitelistingRequestInactive']);
Route::get('/admin/url/whitelisting/request-delete-{id}',[AdminController::class,'adminUrlWhitelistingRequestDelete']);

Route::get('/admin/settlement/report',[AdminController::class,'adminSettlementReportsView']);

Route::get('/admin/settings',[AdminController::class,'adminSettingsView']);
Route::post('/admin/settings/update-admin',[AdminController::class,'adminSettingsUpdateAdmin']);

Route::get('/admin/logs',[AdminController::class,'adminLogsView']);


// Route::get('/make/first-admin',[AdminController::class,'makeFirstAdmin']);
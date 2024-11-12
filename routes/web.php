<?php

use App\Http\Controllers\MerchantController;
use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebController::class,'homeView']);

Route::get('/merchant/onboarding',[MerchantController::class,'merchantOnboardingView']);

Route::post('/merchant/onboarding/step-{id}',[MerchantController::class,'merchantOnboardingStepsAJAX']);
Route::post('/merchant/onboarding/check-{type}',[MerchantController::class,'merchantOnboardingDataCheckAJAX']);
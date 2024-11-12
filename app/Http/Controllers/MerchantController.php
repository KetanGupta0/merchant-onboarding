<?php

namespace App\Http\Controllers;

use App\Models\BusinessDetail;
use App\Models\KYCDocument;
use App\Models\MerchantInfo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MerchantController extends Controller
{
    private function page($pagename, $data = [])
    {
        return view('header') . view($pagename, $data) . view('footer');
    }
    public function merchantOnboardingView()
    {
        return $this->page('Onboarding.index');
    }

    public function merchantOnboardingStepsAJAX(Request $request, $id)
    {
        switch ($id) {
            case 1:
                $request->validate([
                    'merchant_name' => 'required',
                    'merchant_phone' => 'required|numeric|digits:10',
                    'merchant_email' => 'required|email',
                    'merchant_aadhar_no' => 'required|numeric|digits:12',
                    'merchant_pan_no' => 'required|min:10|max:10',
                    'merchant_password' => 'required|min:6',
                    'merchant_confirm_password' => 'required|same:merchant_password'
                ], [
                    'merchant_name.required' => 'The merchant name field is required.',
                    'merchant_phone.required' => 'The phone number field is required.',
                    'merchant_phone.numeric' => 'The phone number must be numeric.',
                    'merchant_phone.digits' => 'The phone number must be exactly 10 digits.',
                    'merchant_email.required' => 'The email field is required.',
                    'merchant_email.email' => 'Please enter a valid email address.',
                    'merchant_aadhar_no.required' => 'The Aadhar number field is required.',
                    'merchant_aadhar_no.numeric' => 'The Aadhar number must be numeric.',
                    'merchant_aadhar_no.digits' => 'The Aadhar number must be exactly 12 digits.',
                    'merchant_pan_no.required' => 'The PAN number field is required.',
                    'merchant_pan_no.min' => 'The PAN number must be exactly 10 characters.',
                    'merchant_pan_no.max' => 'The PAN number must be exactly 10 characters.',
                    'merchant_password.required' => 'The password field is required.',
                    'merchant_password.min' => 'The password must be at least 6 characters.',
                    'merchant_confirm_password.required' => 'The confirm password field is required.',
                    'merchant_confirm_password.same' => 'The confirm password must match the password.'
                ]);
                try {
                    $uniqueChecks = [
                        'merchant_aadhar_no' => 'Aadhar number',
                        'merchant_pan_no' => 'PAN number',
                        'merchant_email' => 'Email',
                        'merchant_phone' => 'Mobile'
                    ];
                    foreach ($uniqueChecks as $field => $fieldName) {
                        $existingRecord = MerchantInfo::where($field, $request->input($field))->first();
                        if ($existingRecord) {
                            if ($existingRecord->merchant_is_onboarded === "Yes") {
                                return response()->json(['message' => "$fieldName is already registered!"], 400);
                            }
                            elseif ($existingRecord->merchant_is_onboarded === "No") {
                                return response()->json(['status' => true, 'merchant_id' => $existingRecord->merchant_id]);
                            }
                        }
                    }
                    $hashedPassword = Hash::make($request->merchant_password);
                    $check = MerchantInfo::create([
                        'merchant_name' => $request->merchant_name,
                        'merchant_phone' => $request->merchant_phone,
                        'merchant_email' => $request->merchant_email,
                        'merchant_aadhar_no' => $request->merchant_aadhar_no,
                        'merchant_pan_no' => $request->merchant_pan_no,
                        'merchant_password' => $hashedPassword,
                        'merchant_plain_password' => $request->merchant_password,
                    ]);
                    if ($check) {
                        return response()->json(['status' => true, 'merchant_id' => $check->merchant_id]);
                    }
                    else {
                        return response()->json(['message' => 'Unable to process merchant data!'], 400);
                    }
                } catch (Exception $e) {
                    return response()->json(['message', $e->getMessage()], 400);
                }
                // break;
            case 2:
                $request->validate([
                    'business_name' => 'required',
                    'business_type' => 'required',
                    'business_address' => 'required',
                    'business_website' => 'required',
                    'merchant_id' => 'required|numeric',
                    'business_id' => 'sometimes|numeric'
                ], [
                    'business_name.required' => 'The business name field is required.',
                    'business_type.required' => 'The business type field is required.',
                    'business_address.required' => 'The business address field is required.',
                    'business_website.required' => 'The business website field is required.',
                    'merchant_id.required' => 'Something went wrong!',
                    'merchant_id.numeric' => 'Something went wrong!',
                    'business_id.numeric' => 'Something went wrong!'
                ]);
                if (isset($request->business_id) && $request->business_id != 0) {
                    $business = BusinessDetail::where('business_merchant_id', '=', $request->merchant_id)->where('business_status', '=', 'Active')->find($request->business_id);
                }
                else {
                    $business = BusinessDetail::where('business_merchant_id', '=', $request->merchant_id)->where('business_status', '=', 'Active')->first();
                    if (!$business) {
                        $business = new BusinessDetail();
                    }
                }
                $business->business_merchant_id = $request->merchant_id;
                $business->business_name = $request->business_name;
                $business->business_type = $request->business_type;
                $business->business_address = $request->business_address;
                $business->business_website = $request->business_website;
                if ($business->save()) {
                    return response()->json(['status' => true, 'merchant_id' => $request->merchant_id, 'business_id' => $business->business_id]);
                }
                else {
                    return response()->json(['message' => 'Unable to process business details!'], 400);
                }
                // break;
            case 3:
                try {
                    // Define business type
                    $businessType = $request->input('business_type');

                    // Validation rules based on business type
                    $rules = [
                        'gst' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',  // 2MB max
                        'msme' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
                        'merchant_id' => 'required|numeric',
                        'business_id' => 'required|numeric'
                    ];

                    if ($businessType !== 'Individual' && $businessType !== 'Solo Proprietorship') {
                        $rules['pan'] = 'required|file|mimes:jpeg,png,jpg,pdf|max:2048';
                        $rules['cin'] = 'required|file|mimes:jpeg,png,jpg,pdf|max:2048';
                    }

                    // Validate request
                    $request->validate($rules);

                    if ($request->merchant_id == 0 || $request->business_id == 0) {
                        return response()->json(['message' => 'Cannot process your request right now!'], 400);
                    }

                    $merchant = MerchantInfo::find($request->merchant_id);
                    if ($merchant && $merchant->merchant_is_onboarded == 'No') {
                        $merchant->merchant_is_onboarded = 'Yes';
                    }
                    else {
                        return response()->json(['message' => 'Merchant is already onboarded or not found!'], 400);
                    }

                    foreach (['pan', 'cin', 'gst', 'msme'] as $field) {
                        if ($request->hasFile($field)) {
                            $document = new KYCDocument();
                            $file = $request->file($field);
                            $filename = time() . '_' . $file->getClientOriginalName();
                            $file->move(public_path('uploads/kyc/docs'), $filename);
                            $document->kyc_merchant_id = $request->merchant_id;
                            $document->kyc_business_id = $request->business_id;
                            $document->kyc_document_name = $filename;
                            $document->kyc_document_type = $field;
                            $document->kyc_document_path = 'uploads/kyc/docs';
                            $document->save();
                        }
                    }
                    $merchant->save();
                    return response()->json(true);
                } catch (Exception $exception) {
                    return response()->json(['message' => $exception->getMessage()], 400);
                }
                // break;
            default:
                return response()->json(['message' => 'Unknown Step'], 400);
        }
    }

    public function merchantOnboardingDataCheckAJAX(Request $request, $type)
    {
        try {
            switch ($type) {
                case 'phone':
                    $data = MerchantInfo::select('merchant_id', 'merchant_name', 'merchant_phone', 'merchant_email', 'merchant_aadhar_no', 'merchant_pan_no')
                        ->where('merchant_phone', '=', $request->merchant_phone)
                        ->where('merchant_is_onboarded', '=', 'No')
                        ->first();
                    break;
                case 'email':
                    $data = MerchantInfo::select('merchant_id', 'merchant_name', 'merchant_phone', 'merchant_email', 'merchant_aadhar_no', 'merchant_pan_no')
                        ->where('merchant_email', '=', $request->merchant_email)
                        ->where('merchant_is_onboarded', '=', 'No')
                        ->first();
                    break;
                case 'aadhar':
                    $data = MerchantInfo::select('merchant_id', 'merchant_name', 'merchant_phone', 'merchant_email', 'merchant_aadhar_no', 'merchant_pan_no')
                        ->where('merchant_aadhar_no', '=', $request->merchant_aadhar_no)
                        ->where('merchant_is_onboarded', '=', 'No')
                        ->first();
                    break;
                case 'pan':
                    $data = MerchantInfo::select('merchant_id', 'merchant_name', 'merchant_phone', 'merchant_email', 'merchant_aadhar_no', 'merchant_pan_no')
                        ->where('merchant_pan_no', '=', $request->merchant_pan_no)
                        ->where('merchant_is_onboarded', '=', 'No')
                        ->first();
                    break;
                default:
                    return response()->json(['message' => 'Link not found!'], 404);
            }
            $businessData = BusinessDetail::select('business_id', 'business_name', 'business_type', 'business_address', 'business_website')
                ->where('business_merchant_id', '=', $data->merchant_id)
                ->first();
            return response()->json(['status' => true, 'data' => $data, 'businessData' => $businessData]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}

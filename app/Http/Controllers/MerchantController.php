<?php

namespace App\Http\Controllers;

use App\Models\AccountDetail;
use App\Models\BusinessDetail;
use App\Models\KYCDocument;
use App\Models\Log;
use App\Models\MerchantInfo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class MerchantController extends Controller
{
    private function saveLog($event, $description, $ip = null, $userAgent = null){
        Log::create([
            'log_user_id' => Session::get('userId'),
            'log_user_type' => Session::get('userType'),
            'log_event_type' => $event,
            'log_description' => $description,
            'log_ip_address' => $ip,
            'log_user_agent' => $userAgent,
        ]);
    }
    private function checkLoginStatus()
    {
        if (Session::has('is_loggedin') && Session::has('userType') && Session::get('is_loggedin') && (Session::get('userType') == 'Merchant')) {
            return true;
        }
        else {
            return false;
        }
    }
    private function dashboardPage($pagename, $data = [])
    {
        if ($this->checkLoginStatus()) {
            return view('same.header') . view($pagename, $data) . view('same.footer');
        }
        else {
            return redirect()->to('/login')->with('error', 'Login is required!');
        }
    }

    public function merchantDashboardView()
    {
        $merchant = MerchantInfo::select('merchant_is_verified')->find(Session::get('userId'));
        return $this->dashboardPage('merchant.dashboard',compact('merchant'));
    }
    public function merchantAccountDetailsView()
    {
        $account = AccountDetail::where('acc_merchant_id','=',Session::get('userId'))->where('acc_status','!=','Deleted')->first();
        return $this->dashboardPage('merchant.account-details',compact('account'));
    }
    public function merchantAccountDetailsUpdate(Request $request)
    {
        if (!$this->checkLoginStatus()) {
            return redirect()->to('/login')->with('error', 'Login is required!');
        }
        // dd($request);exit;
        $request->validate([
            'merchant_id' => 'required|exists:merchant_infos,merchant_id',
            'business_id' => 'nullable|exists:business_details,business_id',
            'acc_bank_name' => 'required|string|max:255',
            'acc_branch_name' => 'nullable|string|max:255', // Optional
            'acc_account_number' => 'required|digits_between:8,20', // Must be 8-20 digits
            'acc_ifsc_code' => [
                'required',
                'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/', // Matches standard IFSC code pattern
                'max:11'
            ],
            'acc_micr_code' => 'nullable|digits:9', // Optional, must be 9 digits if provided
            'acc_swift_code' => 'nullable|string|max:11', // Optional, max length 11 characters
            'acc_account_type' => 'required|in:Business,Current,Savings,Other',
        ], [
            'merchant_id.required' => 'Merchant ID is required.',
            'merchant_id.exists' => 'Invalid Merchant ID.',
            'business_id.exists' => 'Invalid Business ID.',
            'acc_bank_name.required' => 'The Bank Name is required.',
            'acc_bank_name.string' => 'The Bank Name must be a valid string.',
            'acc_bank_name.max' => 'The Bank Name must not exceed 255 characters.',
            'acc_branch_name.string' => 'The Branch Name must be a valid string.',
            'acc_branch_name.max' => 'The Branch Name must not exceed 255 characters.',
            'acc_account_number.required' => 'The Account Number is required.',
            'acc_account_number.digits_between' => 'The Account Number must be between 8 and 20 digits.',
            'acc_ifsc_code.required' => 'The IFSC Code is required.',
            'acc_ifsc_code.regex' => 'The IFSC Code must be a valid 11-character code.',
            'acc_ifsc_code.max' => 'The IFSC Code must not exceed 11 characters.',
            'acc_micr_code.digits' => 'The MICR Code must be exactly 9 digits.',
            'acc_swift_code.string' => 'The Swift Code must be a valid string.',
            'acc_swift_code.max' => 'The Swift Code must not exceed 11 characters.',
            'acc_account_type.required' => 'The Account Type is required.',
            'acc_account_type.in' => 'The Account Type must be one of Business, Current, Savings, or Other.',
        ]);
        try{
            $merchant = MerchantInfo::find($request->merchant_id);
            if($merchant){
                $business = BusinessDetail::where('business_merchant_id','=',$merchant->merchant_id)->where('business_status','=','Active')->first();
                if($business){
                    $account = AccountDetail::where('acc_merchant_id','=',$request->merchant_id)
                        ->where('acc_business_id','=',$request->business_id)
                        ->where('acc_account_number','=',$request->acc_account_number)
                        ->where('acc_status','!=','Deleted')
                        ->first();
                    $temp = null;
                    if(!$account){
                        $account = new AccountDetail();
                        $account->acc_status = 'Inactive';
                        $account->acc_merchant_id = $merchant->merchant_id;
                        $account->acc_business_id = $business->business_id;
                        $account->acc_bank_name = $request->acc_bank_name;
                        $account->acc_account_number = $request->acc_account_number;
                        $account->acc_ifsc_code = $request->acc_ifsc_code;
                        $account->acc_account_type = $request->acc_account_type;
                    }else{
                        $temp = $account->replicate();
                    }
                    $account->acc_branch_name = $request->acc_branch_name;
                    $account->acc_micr_code = $request->acc_micr_code;
                    $account->acc_swift_code = $request->acc_swift_code;
                    if($account->save()){
                        $logDescription = [
                            'pastInfo' => $temp,
                            'presentInfo' => $account,
                            'message' => $temp ? "Account updated successfully!" : "Account created successfully!"
                        ];
                        $this->saveLog('Account Details Update', json_encode($logDescription),$request->ip(),$request->userAgent());
                        return redirect()->back()->with('success',$temp ? "Account updated successfully!" : "Account created successfully!");
                    }else{
                        $logDescription = [
                            'message' => "Unable to save/update data into database!"
                        ];
                        $this->saveLog('Account Details Update', json_encode($logDescription),$request->ip(),$request->userAgent());
                        return redirect()->back()->with('error','An unecpected error occured! Please try after sometimes.');
                    }
                }else{
                    $logDescription = [
                        'message' => 'Business info not found!'
                    ];
                    $this->saveLog('Account Details Update', json_encode($logDescription),$request->ip(),$request->userAgent());
                    return redirect()->back()->with('error','An unecpected error occured! Please try after sometimes.');
                }
            }else{
                $logDescription = [
                    'message' => 'Merchant info not found!'
                ];
                $this->saveLog('Account Details Update', json_encode($logDescription),$request->ip(),$request->userAgent());
                return redirect()->back()->with('error','An unecpected error occured! Please try after sometimes.');
            }
        }catch(Exception $e){
            $logDescription = [
                'message' => $e->getMessage()
            ];
            $this->saveLog('Account Details Update Exception', json_encode($logDescription),$request->ip(),$request->userAgent());
            return redirect()->back()->with('error','Something went wrong! Please check activity log for more details.');
        }
    }

    public function merchantUrlWhitelistingView()
    {
        return $this->dashboardPage('merchant.url-white-listing');
    }
    public function merchantSettlementReportsView()
    {
        return $this->dashboardPage('merchant.settlement-report');
    }
    public function merchantSettingsView()
    {
        $merchant = MerchantInfo::find(Session::get('userId'));
        return $this->dashboardPage('merchant.settings',compact('merchant'));
    }
    public function merchantSettingsUpdate(Request $request)
    {
        if (!$this->checkLoginStatus()) {
            return redirect()->to('/login')->with('error', 'Login is required!');
        }
        $request->validate([
            'merchant_phone2' => 'nullable|numeric|digits:10',
            'merchant_zip' => 'nullable|numeric|digits:6',
            'merchant_profile' => 'nullable|mimes:png,jpg,jpeg,gif,svg,bmp|max:2048',
            'merchant_password' => 'required', // Old password
            'merchant_password_new' => 'nullable|min:8|different:merchant_password', // New password must be different
            'merchant_password_new_confirmed' => 'required_with:merchant_password_new|same:merchant_password_new',
        ], [
            'merchant_phone2.numeric' => 'The alternate phone number must be a numeric value.',
            'merchant_phone2.digits' => 'The alternate phone number must be exactly 10 digits.',
            'merchant_zip.numeric' => 'The zip code must be a numeric value.',
            'merchant_zip.digits' => 'The zip code must be exactly 6 digits.',
            'merchant_profile.mimes' => 'The profile picture must be of type: PNG, JPG, JPEG, GIF, SVG, or BMP.',
            'merchant_profile.max' => 'The profile picture size must not exceed 2MB.',
            'merchant_password.required' => 'The old password is required.',
            'merchant_password_new.min' => 'The new password must be at least 8 characters.',
            'merchant_password_new.different' => 'The new password must be different from the old password.',
            'merchant_password_new_confirmed.required_with' => 'Please confirm the new password.',
            'merchant_password_new_confirmed.same' => 'The new password confirmation does not match.',
        ]);
              
        try{
            $merchant = MerchantInfo::find(Session::get('userId'));
            if($merchant){
                if(!Hash::check($request->merchant_password,$merchant->merchant_password)){
                    return redirect()->back()->with('error','Password is not correct!');
                }
                $temp = $merchant->replicate(['merchant_plain_password','merchant_password']);
                if ($request->hasFile('merchant_profile')) {
                    $file = $request->file('merchant_profile');
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->move(public_path('uploads/merchant/profile'), $filename);
                    $merchant->merchant_profile = $filename;
                }
                $merchant->merchant_phone2 = $request->merchant_phone2;
                $merchant->merchant_city = $request->merchant_city;
                $merchant->merchant_state = $request->merchant_state;
                $merchant->merchant_country = $request->merchant_country;
                $merchant->merchant_zip = $request->merchant_zip;
                $merchant->merchant_landmark = $request->merchant_landmark;
                if($request->merchant_password_new){
                    $merchant->merchant_password = Hash::make($request->merchant_password_new);
                    $merchant->merchant_plain_password = $request->merchant_password_new;
                }
                if($merchant->save()){
                    Session::forget('userPic');
                    Session::put('userPic',$merchant->merchant_profile);
                    $logDescription = [
                        'pastInfo' => $temp,
                        'presentInfo' => $merchant,
                        'message' => 'Profile updated successfully!'
                    ];
                    $this->saveLog('Profile Update', json_encode($logDescription),$request->ip(),$request->userAgent());
                    return redirect()->back()->with('success','Profile updated successfully!');
                }else{
                    $logDescription = [
                        'message' => 'Unable to updata profile data into database right now! Please try again after sometimes.'
                    ];
                    $this->saveLog('Profile Update', json_encode($logDescription),$request->ip(),$request->userAgent());
                    return redirect()->back()->with('error','Something went wrong! Please check activity log for more details.');
                }
            }else{
                $logDescription = [
                    'message' => 'Merchant not found!'
                ];
                $this->saveLog('Profile Update', json_encode($logDescription),$request->ip(),$request->userAgent());
                return redirect()->back()->with('error','Something went wrong! Please check activity log for more details.');
            }
        }catch(Exception $e){
            $logDescription = [
                'message' => $e->getMessage()
            ];
            $this->saveLog('Profile Update Exception', json_encode($logDescription),$request->ip(),$request->userAgent());
            return redirect()->back()->with('error','Something went wrong! Please check activity log for more details.');
        }
    }
    public function merchantLogsView()
    {
        $logs = Log::where('log_user_id','=',Session::get('userId'))->where('log_user_type','=',Session::get('userType'))->orderBy('created_at','desc')->get();
        return $this->dashboardPage('merchant.logs',compact('logs'));
    }

    // File Break
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

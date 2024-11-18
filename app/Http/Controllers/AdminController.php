<?php

namespace App\Http\Controllers;

use App\Models\AccountDetail;
use App\Models\Admin;
use App\Models\BusinessDetail;
use App\Models\KYCDocument;
use App\Models\Log;
use App\Models\MerchantInfo;
use App\Models\UrlWhiteListing;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    private function saveLog($event, $description, $ip = null, $userAgent = null)
    {
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
        if (Session::has('is_loggedin') && Session::has('userType') && Session::get('is_loggedin') && (Session::get('userType') != 'Merchant')) {
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

    public function adminDashboardView()
    {
        return $this->dashboardPage('admin.dashboard');
    }
    public function adminMerchantApprovalView()
    {
        $merchants = MerchantInfo::select('merchant_id', 'merchant_name', 'merchant_phone', 'merchant_email', 'created_at', 'merchant_is_verified', 'merchant_is_onboarded')->where('merchant_status', '!=', 'Deleted')->get();
        return $this->dashboardPage('admin.merchant-approval', compact('merchants'));
    }
    public function adminMerchantDeleteAJAX(Request $request)
    {
        if ($this->checkLoginStatus()) {
            $request->validate([
                'merchant_id' => 'required|numeric'
            ], [
                'merchant_id.required' => 'Unable to process your request right now! Please reload the page and try again.',
                'merchant_id.numeric' => 'Unable to process your request right now! Please reload the page and try again.',
            ]);
            try {
                $merchant = MerchantInfo::where('merchant_status', '!=', 'Deleted')->find($request->merchant_id);
                if ($merchant) {
                    $merchant->merchant_status = 'Deleted';
                    if ($merchant->save()) {
                        $logDescription = [
                            'deleted merchant' => $merchant,
                            'message' => 'Merchant ' . $merchant->merchant_name . ' Deleted successfully'
                        ];
                        $this->saveLog(event: 'Merchant Deleted', description: json_encode($logDescription), ip: $request->ip(), userAgent: $request->userAgent());
                        return response()->json(true);
                    }
                    else {
                        return response()->json(false);
                    }
                }
                else {
                    return response()->json(['message' => 'Merchant not found! Please reload the page and try again.'], 404);
                }
            } catch (Exception $e) {
                $logDescription = [
                    'message' => $e->getMessage()
                ];
                $this->saveLog('Exception', json_encode($logDescription), $request->ip(), $request->userAgent());
                return response()->json(['message' => 'Something went wrong! Please check the log for more details.'], 400);
            }
        }
        else {
            return response()->json(['message' => 'Unable to process your request right now! Please reload the page and try again.'], 400);
        }
    }
    public function adminMerchantFetchAJAX()
    {
        if (!$this->checkLoginStatus()) {
            return response()->json(['message' => 'Unable to process your request right now! Please reload the page and try again.'], 400);
        }
        $data = MerchantInfo::select('merchant_id', 'merchant_name', 'merchant_phone', 'merchant_email', 'created_at', 'merchant_is_verified', 'merchant_is_onboarded')->where('merchant_status', '!=', 'Deleted')->get();
        if ($data) {
            return response()->json(['status' => true, 'data' => $data]);
        }
        else {
            return response()->json(['status' => false]);
        }
    }
    public function adminMerchantApprovalAJAX(Request $request, $action)
    {
        if (!$this->checkLoginStatus()) {
            return response()->json(['message' => 'Unable to process your request right now! Please reload the page and try again.'], 400);
        }
        $request->validate([
            'merchant_id' => 'required|numeric'
        ], [
            'merchant_id.required' => 'Unable to process your request right now! Please reload the page and try again.',
            'merchant_id.numeric' => 'Unable to process your request right now! Please reload the page and try again.',
        ]);
        try {
            $merchant = MerchantInfo::where('merchant_status', '!=', 'Deleted')->find($request->merchant_id);
            if ($merchant) {
                switch ($action) {
                    case 'approve':
                        $merchant->merchant_is_verified = 'Approved';
                        $logDescription = [
                            'merchant approved' => $merchant,
                            'message' => 'Merchant ' . $merchant->merchant_name . ' Approved successfully'
                        ];
                        break;
                    case 'revoke':
                        $merchant->merchant_is_verified = 'Not Approved';
                        $logDescription = [
                            'merchant revoked' => $merchant,
                            'message' => 'Merchant ' . $merchant->merchant_name . ' Revoked successfully'
                        ];
                        break;
                    default:
                        return response()->json(['message' => 'URL not found!'], 404);
                }
                if ($merchant->save()) {
                    $this->saveLog(event: 'Merchant Approval', description: json_encode($logDescription), ip: $request->ip(), userAgent: $request->userAgent());
                    return response()->json(data: true);
                }
                else {
                    return response()->json(false);
                }
            }
            else {
                return response()->json(['message' => 'Merchant not found! Please reload the page and try again.'], 404);
            }
        } catch (Exception $e) {
            $logDescription = [
                'message' => $e->getMessage()
            ];
            $this->saveLog('Merchant Approval Exception', json_encode($logDescription), $request->ip(), $request->userAgent());
            return response()->json(['message' => 'Something went wrong! Please check the log for more details.'], 400);
        }
    }
    public function adminMerchantView(Request $request, $id)
    {
        if (!$this->checkLoginStatus()) {
            return redirect()->to('logout')->with('error', 'Please login again.');
        }
        try {
            $merchant = MerchantInfo::where('merchant_status', '!=', 'Deleted')->find($id);
            if ($merchant) {
                $business = BusinessDetail::where('business_merchant_id', '=', $merchant->merchant_id)->where('business_status', '!=', 'Deleted')->first();
                if ($business) {
                    $documents = KYCDocument::where('kyc_merchant_id', '=', $merchant->merchant_id)->where('kyc_business_id', '=', $business->business_id)->where('kyc_status', '!=', 'Deleted')->get();
                    return $this->dashboardPage('admin.merchant-view', compact('merchant', 'business', 'documents'));
                }
            }
            else {
                return redirect()->back()->with('error', 'Merchant not found!');
            }
        } catch (Exception $e) {
            $logDescription = [
                'message' => $e->getMessage()
            ];
            $this->saveLog('Merchant View Exception', json_encode($logDescription), $request->ip(), $request->userAgent());
            return redirect()->back()->with('error', 'Something went wrong! Please check the log for more details.');
        }
    }
    public function adminMerchantInfoUpdate(Request $request)
    {
        if (!$this->checkLoginStatus()) {
            return redirect()->to('logout')->with('error', 'Please login again.');
        }
        $validator = Validator::make($request->all(), [
            'merchant_name' => 'required|string|max:255',
            'merchant_email' => 'required|email',
            'merchant_phone' => 'required|numeric|digits:10',
            'merchant_aadhar_no' => 'required|numeric|digits:12',
            'merchant_pan_no' => 'required|string|size:10|alpha_num',
            'merchant_is_onboarded' => 'required|in:Yes,No',
            'merchant_is_verified' => 'required|in:Approved,Not Approved',
            'merchant_status' => 'required|in:Active,Blocked',
            'merchant_id' => 'required|numeric',
            'merchant_zip' => 'nullable|numeric|digits:6'
        ], [
            'merchant_name.required' => 'Please enter the merchant name.',
            'merchant_email.required' => 'Please enter the merchant email address.',
            'merchant_email.email' => 'The email address must be a valid email format.',
            'merchant_phone.required' => 'Please enter the primary phone number.',
            'merchant_phone.numeric' => 'The phone number should contain only numbers.',
            'merchant_phone.digits' => 'The primary phone number must be exactly 10 digits.',
            'merchant_aadhar_no.required' => 'Please enter the merchant Aadhar number.',
            'merchant_aadhar_no.numeric' => 'The Aadhar number should contain only numbers.',
            'merchant_aadhar_no.digits' => 'The Aadhar number must be exactly 12 digits.',
            'merchant_pan_no.required' => 'Please enter the PAN number.',
            'merchant_pan_no.size' => 'The PAN number must be exactly 10 characters.',
            'merchant_pan_no.alpha_num' => 'The PAN number should contain only alphanumeric characters.',
            'merchant_is_onboarded.required' => 'Please select if the merchant is onboarded.',
            'merchant_is_onboarded.in' => 'Invalid value selected for onboarding status.',
            'merchant_is_verified.required' => 'Please select the approval status for the merchant.',
            'merchant_is_verified.in' => 'Invalid value selected for approval status.',
            'merchant_status.required' => 'Please select the status for the merchant.',
            'merchant_status.in' => 'Invalid value selected for merchant status.',
            'merchant_id.required' => 'Someting went wrong!',
            'merchant_id.numeric' => 'Someting went wrong!',
            'merchant_zip.numeric' => 'The zip code should contain only numbers.',
            'merchant_zip.digits' => 'The zip code must be exactly 6 digits.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        try {
            $merchant = MerchantInfo::where('merchant_status', '!=', 'Deleted')->find($request->merchant_id);
            if ($merchant) {
                $temp = $merchant->replicate();
                $merchant->merchant_name = $request->merchant_name;
                $merchant->merchant_phone = $request->merchant_phone;
                $merchant->merchant_phone2 = $request->merchant_phone2;
                $merchant->merchant_email = $request->merchant_email;
                $merchant->merchant_aadhar_no = $request->merchant_aadhar_no;
                $merchant->merchant_pan_no = $request->merchant_pan_no;
                $merchant->merchant_is_onboarded = $request->merchant_is_onboarded;
                $merchant->merchant_is_verified = $request->merchant_is_verified;
                $merchant->merchant_status = $request->merchant_status;
                $merchant->merchant_city = $request->merchant_city;
                $merchant->merchant_state = $request->merchant_state;
                $merchant->merchant_country = $request->merchant_country;
                $merchant->merchant_zip = $request->merchant_zip;
                $merchant->merchant_landmark = $request->merchant_landmark;
                if ($merchant->save()) {
                    $logDescription = [
                        'pastInfo' => $temp,
                        'presentInfo' => $merchant,
                        'message' => 'Merchant info updated successfully!'
                    ];
                    $this->saveLog(event: 'Merchant Info Update', description: json_encode($logDescription), ip: $request->ip(), userAgent: $request->userAgent());
                    return redirect()->back()->with('success', 'Merchant info updated successfully!');
                }
                else {
                    return redirect()->back()->with('error', 'Unable to update merchant info right now!');
                }
            }
            else {
                return redirect()->back()->with('error', 'Merchant not found!');
            }
        } catch (Exception $e) {
            $logDescription = [
                'message' => $e->getMessage()
            ];
            $this->saveLog('Merchant Info Update Exception', json_encode($logDescription), $request->ip(), $request->userAgent());
            return redirect()->back()->with('error', 'Something went wrong! Please check the log for more details.');
        }
    }
    public function adminMerchantBusinessInfoUpdate(Request $request)
    {
        if (!$this->checkLoginStatus()) {
            return redirect()->to('logout')->with('error', 'Please login again.');
        }
        $validator = Validator::make($request->all(), [
            'business_name' => 'required|string|max:255',
            'business_type' => 'required|in:Individual,Limited,OPC,Private Limited,Solo Proprietorship',
            'business_address' => 'required|string|max:500',
            'business_website' => 'required|url',
            'business_is_verified' => 'required|in:Verified,Not Verified',
            'business_status' => 'required|in:Active,Blocked',
            'business_id' => 'required|numeric',
            'business_merchant_id' => 'required|numeric',
        ], [
            'business_name.required' => 'Please enter the business name.',
            'business_type.required' => 'Please select the business type.',
            'business_type.in' => 'The selected business type is invalid.',
            'business_address.required' => 'Please enter the business address.',
            'business_address.max' => 'The business address may not exceed 500 characters.',
            'business_website.required' => 'Please enter the business website URL.',
            'business_website.url' => 'The business website must be a valid URL.',
            'business_is_verified.required' => 'Please select the verification status.',
            'business_is_verified.in' => 'The selected verification status is invalid.',
            'business_status.required' => 'Please select the business status.',
            'business_status.in' => 'The selected business status is invalid.',
            'business_id.required' => 'Someting went wrong!',
            'business_id.numeric' => 'Someting went wrong!',
            'business_merchant_id.required' => 'Someting went wrong!',
            'business_merchant_id.numeric' => 'Someting went wrong!',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        try {
            $business = BusinessDetail::where('business_status', '!=', 'Deleted')->find($request->business_id);
            if ($business) {
                $temp = $business->replicate();
                $business->business_name = $request->business_name;
                $business->business_type = $request->business_type;
                $business->business_address = $request->business_address;
                $business->business_website = $request->business_website;
                $business->business_is_verified = $request->business_is_verified;
                $business->business_status = $request->business_status;
                if ($business->save()) {
                    $logDescription = [
                        'pastInfo' => $temp,
                        'presentInfo' => $business,
                        'message' => 'Business info updated successfully!'
                    ];
                    $this->saveLog(event: 'Merchant Business Info Update', description: json_encode($logDescription), ip: $request->ip(), userAgent: $request->userAgent());
                    return redirect()->back()->with('success', 'Business info updated successfully!');
                }
                else {
                    return redirect()->back()->with('error', 'Unable to update business info right now!');
                }
            }
            else {
                return redirect()->back()->with('error', 'Business not found!');
            }
        } catch (Exception $e) {
            $logDescription = [
                'message' => $e->getMessage()
            ];
            $this->saveLog('Merchant Business Info Update Exception', json_encode($logDescription), $request->ip(), $request->userAgent());
            return redirect()->back()->with('error', 'Something went wrong! Please check the log for more details.');
        }
    }
    public function adminMerchantKycDocUpdate(Request $request)
    {
        if (!$this->checkLoginStatus()) {
            return redirect()->to('logout')->with('error', 'Please login again.');
        }
        $validator = Validator::make($request->all(), [
            'kyc_document_name' => 'required|file|mimes:jpeg,jpg,png|max:2048',
            'kyc_document_type' => 'required|string|in:pan,cin,msme,gst',
            'kyc_id' => 'required|numeric',
            'kyc_merchant_id' => 'required|numeric',
            'kyc_business_id' => 'required|numeric',
        ], [
            'kyc_document_name.required' => 'Please upload a document.',
            'kyc_document_name.file' => 'The uploaded file must be a valid file.',
            'kyc_document_name.mimes' => 'The document must be a file of type: jpeg, jpg, png.',
            'kyc_document_name.max' => 'The document may not be larger than 2MB.',
            'kyc_document_type.required' => 'Document type is required.',
            'kyc_document_type.in' => 'The selected document type is invalid.',
            'kyc_id.required' => 'Someting went wrong!',
            'kyc_id.numeric' => 'Someting went wrong!',
            'kyc_merchant_id.required' => 'Someting went wrong!',
            'kyc_merchant_id.numeric' => 'Someting went wrong!',
            'kyc_business_id.required' => 'Someting went wrong!',
            'kyc_business_id.numeric' => 'Someting went wrong!',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        try {
            $kycDocument = KYCDocument::where('kyc_merchant_id', '=', $request->kyc_merchant_id)
                ->where('kyc_business_id', '=', $request->kyc_business_id)
                ->where('kyc_status', '!=', 'Deleted')
                ->find($request->kyc_id);
            if ($kycDocument) {
                $temp = $kycDocument->replicate();
                if ($request->hasFile('kyc_document_name')) {
                    $file = $request->file('kyc_document_name');
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->move(public_path('uploads/kyc/docs'), $filename);
                    $kycDocument->kyc_document_name = $filename;
                    $kycDocument->kyc_document_path = 'uploads/kyc/docs';
                    $kycDocument->kyc_document_type = $request->kyc_document_type;
                    if ($kycDocument->save()) {
                        $logDescription = [
                            'pastInfo' => $temp,
                            'presentInfo' => $kycDocument,
                            'message' => 'KYC Document updated successfully!'
                        ];
                        $this->saveLog(event: 'Merchant KYC Docuemnt Update', description: json_encode($logDescription), ip: $request->ip(), userAgent: $request->userAgent());
                        return redirect()->back()->with('success', 'KYC Document updated successfully!');
                    }
                    else {
                        return redirect()->back()->with('error', 'Unable to update KYC Document right now!');
                    }
                }
            }
            else {
                return redirect()->back()->with('error', 'KYC Document not found!');
            }
        } catch (Exception $e) {
            $logDescription = [
                'message' => $e->getMessage()
            ];
            $this->saveLog('Merchant KYC Docuemnt Update Exception', json_encode($logDescription), $request->ip(), $request->userAgent());
            return redirect()->back()->with('error', 'Something went wrong! Please check the log for more details.');
        }
    }

    public function adminAccountDetailsView()
    {
        if (!$this->checkLoginStatus()) {
            return redirect()->to('logout')->with('error', 'Please login again.');
        }
        $accounts = DB::table('merchant_infos')
            ->join('account_details', 'merchant_infos.merchant_id', '=', 'account_details.acc_merchant_id')
            ->select(
                'merchant_infos.merchant_name',
                'merchant_infos.merchant_phone',
                'merchant_infos.merchant_id',
                'merchant_infos.merchant_email',
                'account_details.acc_merchant_id',
                'account_details.acc_business_id',
                'account_details.acc_account_number',
                'account_details.acc_bank_name',
                'account_details.acc_branch_name',
                'account_details.acc_ifsc_code',
                'account_details.acc_micr_code',
                'account_details.acc_swift_code',
                'account_details.acc_account_type',
                'account_details.acc_status',
                'account_details.acc_id'
            )->where('account_details.acc_status', '!=', 'Deleted')
            ->get();
        return $this->dashboardPage('admin.account-details', compact('accounts'));
    }
    public function adminAccountDetailsEditView(Request $request, $id)
    {
        $account = AccountDetail::find($id);
        if ($account) {
            return $this->dashboardPage('admin.account-details-view', compact('account'));
        }
        else {
            $logDescription = ["message" => "Account id - $id dose not exists or might be deleted!"];
            $this->saveLog("Account not found", json_encode($logDescription), $request->ip(), $request->userAgent());
            return redirect()->back()->with('error', 'Account not found!');
        }
    }
    public function adminAccountDetailsChangeStatus(Request $request, $status, $id)
    {
        if (!$this->checkLoginStatus()) {
            if ($status == "delete") {
                return response()->json(['message' => 'Please login again.'], 400);
            }
            return redirect()->to('logout')->with('error', 'Please login again.');
        }
        $account = AccountDetail::find($id);
        if ($account) {
            $oldstatus = $account->acc_status;
            try {
                switch ($status) {
                    case 'active':
                        $account->acc_status = 'Active';
                        break;
                    case 'inactive':
                        $account->acc_status = 'Inactive';
                        break;
                    case 'suspend':
                        $account->acc_status = 'Suspended';
                        break;
                    case 'close':
                        $account->acc_status = 'Closed';
                        break;
                    case 'delete':
                        $account->acc_status = 'Deleted';
                        break;
                    default:
                        $logDescription = ["message" => "Requested URL does not exists!"];
                        $this->saveLog("Account $status exception", json_encode($logDescription), $request->ip(), $request->userAgent());
                        return redirect()->back()->with('error', 'URL not found!');
                }
                if ($account->save()) {
                    // Delete case is primary problem
                    if ($status == "delete") {
                        $logDescription = [
                            "account" => $account,
                            "oldStatus" => $oldstatus,
                            "message" => "Account deleted successfully!"
                        ];
                        $this->saveLog("Account $status", json_encode($logDescription), $request->ip(), $request->userAgent());
                        return response()->json(true);
                    }
                    else {
                        $logDescription = [
                            "account" => $account,
                            "oldStatus" => $oldstatus,
                            "message" => "Account status updated to $status successfully!"
                        ];
                        $this->saveLog("Account $status", json_encode($logDescription), $request->ip(), $request->userAgent());
                        return redirect()->back()->with('success', 'Account status updated successfully!');
                    }
                }
            } catch (Exception $e) {
                $logDescription = [
                    "message" => $e->getMessage()
                ];
                $this->saveLog("Account $status exception", json_encode($logDescription), $request->ip(), $request->userAgent());
                return redirect()->back()->with('error', 'Something went wrong! Please check the log for more details.');
            }
        }
        else {
            $logDescription = [
                "message" => "Account not found for id: $id! Unable to perform $status right now."
            ];
            $this->saveLog("Account $status", json_encode($logDescription), $request->ip(), $request->userAgent());
            return redirect()->back()->with('error', 'Account not found!');
        }
    }
    public function adminAccountDetailsUpdate(Request $request)
    {
        if (!$this->checkLoginStatus()) {
            return redirect()->to('logout')->with('error', 'Please login again.');
        }
        $request->validate([
            'acc_merchant_id' => 'required|exists:merchant_infos,merchant_id',
            'acc_business_id' => 'nullable|exists:business_details,business_id',
            'acc_id' => 'required|numeric|exists:account_details,acc_id',
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
            'acc_status' => 'required',
        ], [
            'acc_id.required' => 'Account ID is required.',
            'acc_id.numeric' => 'Invalid Account ID.',
            'acc_id.exists' => 'Invalid Account ID.',
            'acc_merchant_id.required' => 'Merchant ID is required.',
            'acc_merchant_id.exists' => 'Invalid Merchant ID.',
            'acc_business_id.exists' => 'Invalid Business ID.',
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
            'acc_status.required' => 'Please select account status before proceeding!',
        ]);
        try {
            $merchant = MerchantInfo::find($request->acc_merchant_id);
            if ($merchant) {
                $business = BusinessDetail::where('business_merchant_id', '=', $merchant->merchant_id)->where('business_status', '=', 'Active')->first();
                if ($business) {
                    $account = AccountDetail::where('acc_merchant_id', '=', $merchant->merchant_id)
                        ->where('acc_business_id', '=', $business->business_id)
                        ->where('acc_status', '!=', 'Deleted')
                        ->first();
                    $temp = null;
                    if (!$account) {
                        return redirect()->back()->with('error', "Account not found for account number: $request->acc_account_number");
                    }
                    else {
                        $temp = $account->replicate();
                    }
                    $account->acc_bank_name = $request->acc_bank_name;
                    $account->acc_account_number = $request->acc_account_number;
                    $account->acc_ifsc_code = $request->acc_ifsc_code;
                    $account->acc_account_type = $request->acc_account_type;
                    $account->acc_branch_name = $request->acc_branch_name;
                    $account->acc_micr_code = $request->acc_micr_code;
                    $account->acc_swift_code = $request->acc_swift_code;
                    $account->acc_status = $request->acc_status;
                    if ($account->save()) {
                        $logDescription = [
                            'pastInfo' => $temp,
                            'presentInfo' => $account,
                            'message' => $temp ? "Account updated successfully!" : "Account created successfully!"
                        ];
                        $this->saveLog('Account Details Update', json_encode($logDescription), $request->ip(), $request->userAgent());
                        return redirect()->back()->with('success', $temp ? "Account updated successfully!" : "Account created successfully!");
                    }
                    else {
                        $logDescription = [
                            'message' => "Unable to save/update data into database!"
                        ];
                        $this->saveLog('Account Details Update', json_encode($logDescription), $request->ip(), $request->userAgent());
                        return redirect()->back()->with('error', 'An unecpected error occured! Please try after sometimes.');
                    }
                }
                else {
                    $logDescription = [
                        'message' => 'Business info not found!'
                    ];
                    $this->saveLog('Account Details Update', json_encode($logDescription), $request->ip(), $request->userAgent());
                    return redirect()->back()->with('error', 'An unecpected error occured! Please try after sometimes.');
                }
            }
            else {
                $logDescription = [
                    'message' => 'Merchant info not found!'
                ];
                $this->saveLog('Account Details Update', json_encode($logDescription), $request->ip(), $request->userAgent());
                return redirect()->back()->with('error', 'An unecpected error occured! Please try after sometimes.');
            }
        } catch (Exception $e) {
            $logDescription = [
                'message' => $e->getMessage()
            ];
            $this->saveLog('Account Details Update Exception', json_encode($logDescription), $request->ip(), $request->userAgent());
            return redirect()->back()->with('error', 'Something went wrong! Please check activity log for more details.');
        }
    }

    public function adminUrlWhitelistingView()
    {
        $urls = DB::table('url_white_listings')
            ->join('merchant_infos', 'url_white_listings.uwl_merchant_id', '=', 'merchant_infos.merchant_id')
            ->select(
                'url_white_listings.*',
                'merchant_infos.merchant_name',
                'merchant_infos.merchant_email',
                'merchant_infos.merchant_phone',
                'merchant_infos.merchant_id'
            )
            ->where('url_white_listings.uwl_status', '!=', 'Deleted')
            ->orderBy('url_white_listings.uwl_merchant_id', 'ASC')
            ->orderBy('url_white_listings.created_at', 'DESC')
            ->get();
        return $this->dashboardPage('admin.url-whitelist', compact('urls'));
    }
    public function adminUrlWhitelistingRequestActive(Request $request, $id){
        if (!$this->checkLoginStatus()) {
            return redirect()->to('/login')->with('error', 'Login is required!');
        }
        try{
            $url = UrlWhiteListing::where('uwl_status','!=','delete')->find($id);
            if($url){
                $url->uwl_status = 'Active';
                if($url->save()){
                    return redirect()->back()->with('success','URL status updated successfully!');
                }else{
                    return redirect()->back()->with('error','Unable to complete your request right now! Please try again later.');
                }
            }else{
                return redirect()->back()->with('error','URL not found!');
            }
        }catch(Exception $e){
            $logDescription = [
                'message' => $e->getMessage()
            ];
            $this->saveLog('URL Whitelist Exception',json_encode($logDescription),$request->ip(),$request->userAgent());
            return redirect()->back()->with('error','Something went wrong! Please check the log for more details.');
        }
    }
    public function adminUrlWhitelistingRequestInactive(Request $request, $id){
        if (!$this->checkLoginStatus()) {
            return redirect()->to('/login')->with('error', 'Login is required!');
        }
        try{
            $url = UrlWhiteListing::where('uwl_status','!=','delete')->find($id);
            if($url){
                $url->uwl_status = 'Inactive';
                if($url->save()){
                    return redirect()->back()->with('success','URL status updated successfully!');
                }else{
                    return redirect()->back()->with('error','Unable to complete your request right now! Please try again later.');
                }
            }else{
                return redirect()->back()->with('error','URL not found!');
            }
        }catch(Exception $e){
            $logDescription = [
                'message' => $e->getMessage()
            ];
            $this->saveLog('URL Whitelist Exception',json_encode($logDescription),$request->ip(),$request->userAgent());
            return redirect()->back()->with('error','Something went wrong! Please check the log for more details.');
        }
    }
    public function adminUrlWhitelistingRequestDelete(Request $request, $id){
        if (!$this->checkLoginStatus()) {
            return response()->json(['message' => 'Login is required!'],400);
        }
        try{
            $url = UrlWhiteListing::where('uwl_status','!=','delete')->find($id);
            if($url){
                $url->uwl_status = 'Deleted';
                if($url->save()){
                    return response()->json(['message'=>'URL deleted sucessfully!','status'=>true],200);
                }else{
                    return response()->json(['message'=>'Unable to complete your request right now! Please try again later.'],400);
                }
            }else{
                return response()->json(['message'=>'URL not found!','status'=>false],400);
            }
        }catch(Exception $e){
            $logDescription = [
                'message' => $e->getMessage()
            ];
            $this->saveLog('URL Whitelist Exception',json_encode($logDescription),$request->ip(),$request->userAgent());
            return response()->json(['message' => 'Something went wrong! Please check the log for more details.'],400);
        }
    }

    public function adminSettlementReportsView()
    {
        return $this->dashboardPage('admin.settlement-report');
    }
    public function adminSettingsView()
    {
        $admin = Admin::find(Session::get('userId'));
        return $this->dashboardPage('admin.settings', compact('admin'));
    }
    public function adminSettingsUpdateAdmin(Request $request)
    {
        if (!$this->checkLoginStatus()) {
            return redirect()->to('logout')->with('error', 'Login is required!');
        }
        $request->validate([
            'admin_name' => 'required',
            'admin_email' => 'required|email',
            'admin_phone' => 'required|numeric|digits:10',
            'admin_phone2' => 'nullable|numeric|digits:10',
            'admin_profile_pic' => 'nullable|mimes:jpg,bmp,png,jpeg,gif|file|max:2048',
            'admin_zip_code' => 'nullable|numeric|digits:6',
            'admin_password' => 'required',
            'admin_password_new' => 'nullable|same:admin_password_new_confirmed',
            'admin_password_new_confirmed' => 'required_with:admin_password_new|same:admin_password_new'
        ], [
            'admin_name.required' => 'Admin name is required.',
            'admin_email.required' => 'Admin email is required.',
            'admin_email.email' => 'Admin email must be a valid email address.',
            'admin_phone.required' => 'Admin phone number is required.',
            'admin_phone.numeric' => 'Admin phone number must be numeric.',
            'admin_phone.digits' => 'Admin phone number must be exactly 10 digits.',
            'admin_phone2.numeric' => 'Alternate phone number must be numeric.',
            'admin_phone2.digits' => 'Alternate phone number must be exactly 10 digits.',
            'admin_profile_pic.mimes' => 'Profile picture must be a file of type: jpg, bmp, png, jpeg, gif.',
            'admin_profile_pic.file' => 'Profile picture must be a valid file.',
            'admin_profile_pic.max' => 'Profile picture size must not exceed 2MB.',
            'admin_zip_code.numeric' => 'ZIP code must be numeric.',
            'admin_zip_code.digits' => 'ZIP code must be exactly 6 digits.',
            'admin_password.required' => 'Admin password is required.',
            'admin_password_new.same' => 'New password must match the confirmation password.',
            'admin_password_new_confirmed.required_with' => 'Password confirmation is required when setting a new password.',
            'admin_password_new_confirmed.same' => 'Password confirmation must match the new password.'
        ]);

        try {
            $admin = Admin::find(Session::get('userId'));
            if ($admin) {
                if (Hash::check($request->admin_password, $admin->admin_password)) {
                    $temp = $admin->replicate();
                    $admin->admin_name = $request->admin_name;
                    $admin->admin_email = $request->admin_email;
                    $admin->admin_phone = $request->admin_phone;
                    $admin->admin_phone2 = $request->admin_phone2;
                    $admin->admin_city = $request->admin_city;
                    $admin->admin_state = $request->admin_state;
                    $admin->admin_country = $request->admin_country;
                    $admin->admin_zip_code = $request->admin_zip_code;
                    $admin->admin_landmark = $request->admin_landmark;
                    if ($request->hasFile('admin_profile_pic')) {
                        $file = $request->file('admin_profile_pic');
                        $filename = time() . '_' . $file->getClientOriginalName();
                        $destinationPath = public_path('uploads/admin/profile');
                        $file->move($destinationPath, $filename);
                        $admin->admin_profile_pic = $filename;
                    }
                    if ($request->admin_password_new) {
                        $hashedPassword = Hash::make($request->admin_password_new);
                        $admin->admin_password = $hashedPassword;
                        $admin->admin_plain_password = $request->admin_password_new;
                    }
                    if ($admin->save()) {
                        Session::forget('userName');
                        Session::put('userName', $admin->admin_name);
                        if ($request->hasFile('admin_profile_pic')) {
                            Session::forget('userPic');
                            Session::put('userPic', $filename);
                        }
                        $logDescription = [
                            'pastInfo' => $temp,
                            'presentInfo' => $admin,
                            'message' => 'Profile updated successfully!'
                        ];
                        $this->saveLog('Admin Profile Update', json_encode($logDescription), $request->ip(), $request->userAgent());
                        return redirect()->back()->with('success', 'Profile updated successfully!');
                    }
                }
                else {
                    $logDescription = [
                        'message' => 'Password is wrong!'
                    ];
                    $this->saveLog('Admin Profile Update', json_encode($logDescription), $request->ip(), $request->userAgent());
                    return redirect()->back()->with('error', 'Password is wrong!');
                }
            }
            else {
                $logDescription = [
                    'message' => 'Admin not found!'
                ];
                $this->saveLog('Admin Profile Update', json_encode($logDescription), $request->ip(), $request->userAgent());
                return redirect()->back()->with('error', 'Admin not found!');
            }
        } catch (Exception $e) {
            $logDescription = [
                'message' => $e->getMessage()
            ];
            $this->saveLog('Admin Profile Update Exception', json_encode($logDescription), $request->ip(), $request->userAgent());
            return redirect()->back()->with('error', 'Something went wrong! Please check the log for more details.');
        }
    }

    public function adminLogsView()
    {
        $logs = Log::orderBy('log_id', 'desc')->get();
        return $this->dashboardPage('admin.logs', compact('logs'));
    }
    // public function makeFirstAdmin(){
    //     $name = 'Admin';
    //     $phone = '1234567890';
    //     $email = 'admin@gmail.com';
    //     $password = '1234';
    //     $type = 'Super Admin';

    //     $hashedPassword = Hash::make($password);
    //     $check = Admin::create([
    //         'admin_name' => $name,
    //         'admin_phone' => $phone,
    //         'admin_email' => $email,
    //         'admin_password' => $hashedPassword,
    //         'admin_plain_password' => $password,
    //         'admin_type' => $type,
    //     ]);
    //     return response()->json($check);
    // }
}

<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\BusinessDetail;
use App\Models\KYCDocument;
use App\Models\Log;
use App\Models\MerchantInfo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    private function saveLog($event, $description, $userId = null, $userType = null, $ip = null, $userAgent = null){
        Log::create([
            'log_user_id' => $userId,
            'log_user_type' => $userType,
            'log_event_type' => $event,
            'log_description' => $description,
            'log_ip_address' => $ip,
            'log_user_agent' => $userAgent,
        ]);
    }
    private function checkLoginStatus()
    {
        if (Session::has('is_loggedin') && Session::get('is_loggedin')) {
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
                    if($merchant->save()){
                        $logDescription = [
                            'deleted merchant' => $merchant,
                            'message' => 'Merchant '.$merchant->merchant_name.' Deleted successfully'
                        ];
                        $this->saveLog(event: 'Merchant Deleted',description: json_encode($logDescription), userId: Session::get('userId'), userType: Session::get('userType'), ip:$request->ip(), userAgent:$request->userAgent());
                        return response()->json(true);
                    }else{
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
                $this->saveLog('Exception',json_encode($logDescription),Session::get('userId'),Session::get('userType'),$request->ip(),$request->userAgent());
                return response()->json(['message' => 'Something went wrong! Please check the log for more details.'], 400);
            }
        }
        else {
            return response()->json(['message' => 'Unable to process your request right now! Please reload the page and try again.'], 400);
        }
    }
    public function adminMerchantFetchAJAX(){
        if (!$this->checkLoginStatus()){
            return response()->json(['message' => 'Unable to process your request right now! Please reload the page and try again.'], 400);
        }
        $data = MerchantInfo::select('merchant_id', 'merchant_name', 'merchant_phone', 'merchant_email', 'created_at', 'merchant_is_verified', 'merchant_is_onboarded')->where('merchant_status','!=','Deleted')->get();
        if($data){
            return response()->json(['status'=>true,'data'=>$data]);
        }else{
            return response()->json(['status'=>false]);
        }
    }
    public function adminMerchantApprovalAJAX(Request $request, $action){
        if(!$this->checkLoginStatus()){
            return response()->json(['message' => 'Unable to process your request right now! Please reload the page and try again.'], 400);
        }
        $request->validate([
            'merchant_id' => 'required|numeric'
        ], [
            'merchant_id.required' => 'Unable to process your request right now! Please reload the page and try again.',
            'merchant_id.numeric' => 'Unable to process your request right now! Please reload the page and try again.',
        ]);
        try{
            $merchant = MerchantInfo::where('merchant_status', '!=', 'Deleted')->find($request->merchant_id);
            if ($merchant) {
                switch($action){
                    case 'approve':
                        $merchant->merchant_is_verified = 'Approved';
                        $logDescription = [
                            'merchant approved' => $merchant,
                            'message' => 'Merchant '.$merchant->merchant_name.' Approved successfully'
                        ];
                        break;
                    case 'revoke':
                        $merchant->merchant_is_verified = 'Not Approved';
                        $logDescription = [
                            'merchant revoked' => $merchant,
                            'message' => 'Merchant '.$merchant->merchant_name.' Revoked successfully'
                        ];
                        break;
                    default: return response()->json(['message'=>'URL not found!'],404);
                }
                if($merchant->save()){
                    $this->saveLog(event: 'Merchant Approval',description: json_encode($logDescription), userId: Session::get('userId'), userType: Session::get('userType'), ip:$request->ip(), userAgent:$request->userAgent());
                    return response()->json(true);
                }else{
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
            $this->saveLog('Exception',json_encode($logDescription),Session::get('userId'),Session::get('userType'),$request->ip(),$request->userAgent());
            return response()->json(['message' => 'Something went wrong! Please check the log for more details.'], 400);
        }
    }
    public function adminMerchantView(Request $request,$id){
        if(!$this->checkLoginStatus()){
            return redirect()->to('logout')->with('error','Please login again.');
        }
        try{
            $merchant = MerchantInfo::where('merchant_status', '!=', 'Deleted')->find($id);
            if($merchant){
                $business = BusinessDetail::where('business_merchant_id','=',$merchant->merchant_id)->where('business_status','!=','Deleted')->first();
                if($business){
                    $documents = KYCDocument::where('kyc_merchant_id','=',$merchant->merchant_id)->where('kyc_business_id','=',$business->business_id)->where('kyc_status','!=','Deleted')->get();
                    return $this->dashboardPage('admin.merchant-view',compact('merchant','business','documents'));
                }
            }else{
                return redirect()->back()->with('error','Merchant not found!');
            }
        }catch (Exception $e) {
            $logDescription = [
                'message' => $e->getMessage()
            ];
            $this->saveLog('Exception',json_encode($logDescription),Session::get('userId'),Session::get('userType'),$request->ip(),$request->userAgent());
            return redirect()->back()->with('error','Something went wrong! Please check the log for more details.');
        }
    }
    public function adminMerchantInfoUpdate(Request $request){
        if(!$this->checkLoginStatus()){
            return redirect()->to('logout')->with('error','Please login again.');
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
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        try{
            $merchant = MerchantInfo::where('merchant_status','!=','Deleted')->find($request->merchant_id);
            if($merchant){
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
                if($merchant->save()){
                    $logDescription = [
                        'pastInfo' => $temp,
                        'presentInfo' => $merchant,
                        'message' => 'Merchant info updated successfully!'
                    ];
                    $this->saveLog(event: 'Merchant Info Update',description: json_encode($logDescription), userId: Session::get('userId'), userType: Session::get('userType'), ip:$request->ip(), userAgent:$request->userAgent());
                    return redirect()->back()->with('success','Merchant info updated successfully!');
                }else{
                    return redirect()->back()->with('error','Unable to update merchant info right now!');
                }
            }else{
                return redirect()->back()->with('error','Merchant not found!');
            }
        }catch (Exception $e) {
            $logDescription = [
                'message' => $e->getMessage()
            ];
            $this->saveLog('Exception',json_encode($logDescription),Session::get('userId'),Session::get('userType'),$request->ip(),$request->userAgent());
            return redirect()->back()->with('error','Something went wrong! Please check the log for more details.');
        }
    }
    public function adminMerchantBusinessInfoUpdate(Request $request){
        if(!$this->checkLoginStatus()){
            return redirect()->to('logout')->with('error','Please login again.');
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
        try{
            $business = BusinessDetail::where('business_status','!=','Deleted')->find($request->business_id);
            if($business){
                $temp = $business->replicate();
                $business->business_name = $request->business_name;
                $business->business_type = $request->business_type;
                $business->business_address = $request->business_address;
                $business->business_website = $request->business_website;
                $business->business_is_verified = $request->business_is_verified;
                $business->business_status = $request->business_status;
                if($business->save()){
                    $logDescription = [
                        'pastInfo' => $temp,
                        'presentInfo' => $business,
                        'message' => 'Business info updated successfully!'
                    ];
                    $this->saveLog(event: 'Merchant Business Info Update',description: json_encode($logDescription), userId: Session::get('userId'), userType: Session::get('userType'), ip:$request->ip(), userAgent:$request->userAgent());
                    return redirect()->back()->with('success','Business info updated successfully!');
                }else{
                    return redirect()->back()->with('error','Unable to update business info right now!');
                }
            }else{
                return redirect()->back()->with('error','Business not found!');
            }
        }catch (Exception $e) {
            $logDescription = [
                'message' => $e->getMessage()
            ];
            $this->saveLog('Exception',json_encode($logDescription),Session::get('userId'),Session::get('userType'),$request->ip(),$request->userAgent());
            return redirect()->back()->with('error','Something went wrong! Please check the log for more details.');
        }
    }
    public function adminMerchantKycDocUpdate(Request $request){
        if(!$this->checkLoginStatus()){
            return redirect()->to('logout')->with('error','Please login again.');
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
        try{
            $kycDocument = KYCDocument::where('kyc_merchant_id','=',$request->kyc_merchant_id)
                                    ->where('kyc_business_id','=',$request->kyc_business_id)
                                    ->where('kyc_status','!=','Deleted')
                                    ->find($request->kyc_id);
            if($kycDocument){
                $temp = $kycDocument->replicate();
                if ($request->hasFile('kyc_document_name')) {
                    $file = $request->file('kyc_document_name');
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->move(public_path('uploads/kyc/docs'), $filename);
                    $kycDocument->kyc_document_name = $filename;
                    $kycDocument->kyc_document_path = 'uploads/kyc/docs';
                    $kycDocument->kyc_document_type = $request->kyc_document_type;
                    if($kycDocument->save()){
                        $logDescription = [
                            'pastInfo' => $temp,
                            'presentInfo' => $kycDocument,
                            'message' => 'KYC Document updated successfully!'
                        ];
                        $this->saveLog(event: 'Merchant KYC Docuemnt Update',description: json_encode($logDescription), userId: Session::get('userId'), userType: Session::get('userType'), ip:$request->ip(), userAgent:$request->userAgent());
                        return redirect()->back()->with('success','KYC Document updated successfully!');
                    }else{
                        return redirect()->back()->with('error','Unable to update KYC Document right now!');
                    }
                }
            }else{
                return redirect()->back()->with('error','KYC Document not found!');
            }
        }catch (Exception $e) {
            $logDescription = [
                'message' => $e->getMessage()
            ];
            $this->saveLog('Exception',json_encode($logDescription),Session::get('userId'),Session::get('userType'),$request->ip(),$request->userAgent());
            return redirect()->back()->with('error','Something went wrong! Please check the log for more details.');
        }
    }

    public function adminAccountDetailsView()
    {
        return $this->dashboardPage('admin.account-details');
    }
    public function adminUrlWhitelistingView()
    {
        return $this->dashboardPage('admin.url-whitelist');
    }
    public function adminSettlementReportsView()
    {
        return $this->dashboardPage('admin.settlement-report');
    }
    public function adminSettingsView()
    {
        $admin = Admin::find(Session::get('userId'));
        return $this->dashboardPage('admin.settings',compact('admin'));
    }
    public function adminSettingsUpdateAdmin(Request $request)
    {
        if(!$this->checkLoginStatus()){
            return redirect()->to('logout')->with('error','Login is required!');
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

        try{
            $admin = Admin::find(Session::get('userId'));
            if($admin){
                if(Hash::check($request->admin_password, $admin->admin_password)){
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
                    if($request->admin_password_new){
                        $hashedPassword = Hash::make($request->admin_password_new);
                        $admin->admin_password = $hashedPassword;
                        $admin->admin_plain_password = $request->admin_password_new;
                    }
                    if($admin->save()){
                        Session::forget('userName');
                        Session::put('userName',$admin->admin_name);
                        if ($request->hasFile('admin_profile_pic')){
                            Session::forget('userPic');
                            Session::put('userPic',$filename);
                        }
                        $logDescription = [
                            'pastInfo' => $temp,
                            'presentInfo' => $admin,
                            'message' => 'Profile updated successfully!'
                        ];
                        $this->saveLog('Admin Profile Update',json_encode($logDescription),Session::get('userId'),Session::get('userType'),$request->ip(),$request->userAgent());
                        return redirect()->back()->with('success','Profile updated successfully!');
                    }
                }else{
                    $logDescription = [
                        'message' => 'Password is wrong!'
                    ];
                    $this->saveLog('Admin Profile Update',json_encode($logDescription),Session::get('userId'),Session::get('userType'),$request->ip(),$request->userAgent());
                    return redirect()->back()->with('error','Password is wrong!');
                }
            }else{
                $logDescription = [
                    'message' => 'Admin not found!'
                ];
                $this->saveLog('Admin Profile Update',json_encode($logDescription),Session::get('userId'),Session::get('userType'),$request->ip(),$request->userAgent());
                return redirect()->back()->with('error','Admin not found!');
            }
        }catch(Exception $e){
            $logDescription = [
                'message' => $e->getMessage()
            ];
            $this->saveLog('Exception',json_encode($logDescription),Session::get('userId'),Session::get('userType'),$request->ip(),$request->userAgent());
            return redirect()->back()->with('error','Something went wrong! Please check the log for more details.');
        }
    }

    public function adminLogsView()
    {
        $logs = Log::orderBy('log_id','desc')->get();
        return $this->dashboardPage('admin.logs',compact('logs'));
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

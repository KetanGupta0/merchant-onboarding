<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Log;
use App\Models\MerchantInfo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

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
                            'message' => 'Deleted successfully'
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
                return response()->json(['message' => $e->getMessage()], 400);
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
                            'message' => 'Approved successfully'
                        ];
                        break;
                    case 'revoke':
                        $merchant->merchant_is_verified = 'Not Approved';
                        $logDescription = [
                            'merchant revoked' => $merchant,
                            'message' => 'Revoked successfully'
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
            return response()->json(['message' => $e->getMessage()], 400);
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
    public function adminSettingsView()
    {
        return $this->dashboardPage('admin.settings');
    }
    public function adminLogsView()
    {
        return $this->dashboardPage('admin.logs');
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

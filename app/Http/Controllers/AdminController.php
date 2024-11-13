<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\MerchantInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AdminController extends Controller
{
    private function dashboardPage($pagename, $data = []){
        if (Session::has('is_loggedin') && Session::get('is_loggedin')){
            return view('same.header').view($pagename,$data).view('same.footer');
        }else{
            return redirect()->to('/login')->with('error','Login is required!');
        }
    }
    public function adminDashboardView(){
        return $this->dashboardPage('admin.dashboard');
    }
    public function adminMerchantApprovalView(){
        $merchants = MerchantInfo::select('merchant_name','merchant_phone','merchant_email','created_at','merchant_is_verified','merchant_is_onboarded')->where('merchant_status','!=','Deleted')->get();
        return $this->dashboardPage('admin.merchant-approval',compact('merchants'));
    }
    public function adminAccountDetailsView(){
        return $this->dashboardPage('admin.account-details');
    }
    public function adminUrlWhitelistingView(){
        return $this->dashboardPage('admin.url-whitelist');
    }
    public function adminSettingsView(){
        return $this->dashboardPage('admin.settings');
    }
    public function adminLogsView(){
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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebController extends Controller
{
    private function page($pagename, $data = []){
        return view('header').view($pagename,$data).view('footer');
    }
    private function dashboardPage($pagename, $data = []){
        return view('same.header').view($pagename,$data).view('same.footer');
    }
    public function homeView(){
        return $this->page('welcome');
    }
    public function loginView(){
        return $this->page('login');
    }
    public function adminDashboardView(){
        return $this->dashboardPage('admin.dashboard');
    }
    public function adminMerchantApprovalView(){
        return $this->dashboardPage('admin.merchant-approval');
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
}

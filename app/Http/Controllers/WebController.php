<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebController extends Controller
{
    private function page($pagename, $data = []){
        return view('header').view($pagename,$data).view('footer');
    }
    public function homeView(){
        return $this->page('welcome');
    }
}

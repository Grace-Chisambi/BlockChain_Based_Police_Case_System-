<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PagesController extends Controller
{
    public function admin(){
        return view('pages.admin');
    }
    public function index(){
        return view('pages.index');
    }
    public function user_management(){
        return view('pages.user_management');
    }
    public function register_case(){
        return view('pages.register_case');
    }
    public function assignment(){
        return view('pages.assignment');
    }

}

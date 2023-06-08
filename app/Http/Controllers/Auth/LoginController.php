<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{  
    public function __construct()
    {
        $this->middleware('auth')->except('logout');
    }
    
    public function login()
    {
        return view('login');
    }

    public function processLogin(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $credentials = $request->except(['_token']);

        if (auth()->attempt($credentials)) {
            return redirect()->route('home');
        }

        return redirect()->back()->with('message','Invalid credentials');
    }
    public function logout(Request $request)
    {
          Auth::logout();
         $request->session()->invalidate();
         $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
class FormController extends Controller
{
    public function index()
    {
        return view('form');
    }
 
    public function store(Request $request)
    {
         
        $validatedData = $request->validate([
          'name' => 'required|unique:users',
          'email' => 'required|unique:users|max:255',
          'password' => 'required',
        ]);
 
        $user = new User;
 
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = $request->password;
 
        $user->save();
 
        return redirect('form')->with('status', 'Form Data Has Been Inserted');
 
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserFormController extends Controller
{
    public function index()
    {
        $users = User::select('id', 'name', 'email', 'email_verified_at', 'created_at', 'updated_at')
            ->get();

        // dd($users);

        return view('home', compact('users'));
    }


    //Add User Function
    public function create()
    {
        return view('add');
    }
    public function add(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ]);

        $user = new User;
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));
        $user->save();

        $users = User::select('id', 'name', 'email', 'email_verified_at', 'created_at', 'updated_at')
            ->get();
        return view('home')->with(['users' => $users, 'Success', 'User created successfully.']);

        // return redirect()->back()
    }

    // Edit User Function
    public function edit($id)
    {
        $user = User::find($id);

        return view('edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required|email'
        ]);

        $user->update($validatedData);

        return redirect()->route('home', ['user' => $user->id]);
    }


    //Delete User Function
    public function destroy(User $user)
    {

        $user->delete();

        return redirect()->back()->with('Success', 'User Deleted Successfully.');
    }
}

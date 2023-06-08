<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;

class UserFormController extends Controller
{
    public function index()
    {
        $users = User::select('id', 'name', 'email', 'email_verified_at', 'created_at', 'updated_at')
            ->get();

        return view('home', ['users' => $users]);
    }

    public function destroy(User $user)
    {
    
        $user->delete();

        return redirect()->back()->with('Success', 'User Deleted Successfully.');
    }
}

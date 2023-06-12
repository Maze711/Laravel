<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Validator;


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

        $file = public_path('Downloads/Accounts.xlsx');
        $excel = IOFactory::load($file);

        $worksheet = $excel->getActiveSheet();
        $rows = $worksheet->toArray();
        // dd($rows);
        $newData = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
        ];
        // dd($newData);
        $headers = $rows[0]; // Get the headers from the first row
        $dataRows = array_slice($rows, 1); // Remove the first row from the data

        $existingData = [];
        foreach ($rows as $row) {
            $existingData = [
                'name' => $row[1],
                'email' => $row[2],
            ];

            // Compare the existing data with the new data
            if ($existingData['name'] === $newData['name'] && $existingData['email'] === $newData['email']) {
                // Data already exists in the Excel file
                return redirect()->back()->with('error', 'Data already exists in the Excel file.');
            }
        }

        $validatedData = $request->validate([
            'name' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ]);

        $user = new User;
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = $request->input('password');
        $user->save();
        $id = $user->id;
        $created = $user->created_at;
        $updated = $user->updated_at;


        $file = public_path('Downloads/Accounts.xlsx');
        $excel = IOFactory::load($file);

        $worksheet = $excel->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();
        $worksheet->setCellValue('A' . ($highestRow + 1), $id);
        $worksheet->setCellValue('B' . ($highestRow + 1), $newData['name']);
        $worksheet->setCellValue('C' . ($highestRow + 1), $newData['email']);
        $worksheet->setCellValue('D' . ($highestRow + 1), ' ');
        $worksheet->setCellValue('E' . ($highestRow + 1), $created);
        $worksheet->setCellValue('F' . ($highestRow + 1), $updated);

        $writer = IOFactory::createWriter($excel, 'Xlsx');

        $writer->save($file);

        return redirect()->route('home')->with('success', 'User created successfully.');
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

        return redirect()->route('home')->with('success', 'User edit successfully.');
    }


    //Delete User Function
    public function destroy(User $user)
    {

        $user->delete();

        return redirect()->route('home')->with('success', 'User deleted successfully.');
    }
}

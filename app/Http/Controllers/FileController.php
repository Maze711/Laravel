<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\User;

class FileController extends Controller
{
    public function export()
    {
        $data = User::select('id', 'name', 'email', 'email_verified_at', 'created_at', 'updated_at')->get();
        $spreadsheet = new Spreadsheet();

        // Add data to the spreadsheet
        $sheet = $spreadsheet->getActiveSheet();
        $headers = ['id', 'name', 'email', 'email_verified_at', 'created_at', 'updated_at'];
        $sheet->fromArray($headers, null, 'A1');

        // Insert data from the database
        $dataRows = $data->toArray();
        $sheet->fromArray($dataRows, null, 'A2');

        // Save the spreadsheet as an Excel file
        $writer = new Xlsx($spreadsheet);
        $fileName = 'Accounts.xlsx';
        $filePath = public_path('Downloads/' . $fileName);

        $writer->save($filePath);

        // Download the file
        if (file_exists($filePath)) {
            return response()->download($filePath);
        } else {
            abort(404, 'The file could not be found.');
        }
    }
}

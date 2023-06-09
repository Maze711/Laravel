<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Http\Controllers\ExcelController;
use App\Models\User;

class FileController extends Controller
{
    public function export()
    {
        $data = User::select('id', 'name', 'email', 'email_verified_at', 'password', 'remember_token', 'created_at', 'updated_at')->get();
        $spreadsheet = new Spreadsheet();

        // Add data to the spreadsheet
        $sheet = $spreadsheet->getActiveSheet();
        $headers = ['id', 'name', 'email', 'email_verified_at', 'password', 'remember_token', 'created_at', 'updated_at'];
        $sheet->fromArray($headers, null, 'A1');

        // Insert data from the database
        $dataRows = $data->toArray();
        $sheet->fromArray($dataRows, null, 'A2');

        // Save the spreadsheet as an Excel file
        $writer = new Xlsx($spreadsheet);
        $fileName = 'export.xlsx';
        $writer->save($fileName);

        // Download the file
        return response()->download($fileName)->deleteFileAfterSend(true);
    }
}

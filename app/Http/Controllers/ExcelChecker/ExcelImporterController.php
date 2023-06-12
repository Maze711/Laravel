<?php

namespace App\Http\Controllers\ExcelChecker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelImporterController extends Controller
{

    public function index(){
        return view('view', ['rows' => []]);
    }
    public function Import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xls,xlsx'
        ]);

        $file = $request->file('excel_file');
        $filePath = $file->getPathname();

        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        return view('view', ['rows' => $rows])->with('success', 'Excel file imported successfully.');
    }
}

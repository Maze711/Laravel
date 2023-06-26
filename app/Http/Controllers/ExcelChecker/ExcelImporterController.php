<?php

namespace App\Http\Controllers\ExcelChecker;

use App\Http\Controllers\Controller;
use App\Jobs\ExcelQueue;
use App\Models\Catalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use League\CommonMark\Extension\Table\Table;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelImporterController extends Controller
{
    public function index(Request $request)
    {
        $rows = Catalog::paginate(10);
        // dd($rows);
        if (empty($rows)) {
            return view('view',  ['empty' => 'The Database is empty.']);
        } else {
            $databaseColumnNames = Schema::getColumnListing('catalogs');

            return view('view', ['rows' => $rows, 'columns' => $databaseColumnNames]);
        }
    }
    public function Import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:csv,xls,xlsx'
        ]);

        $file = $request->file('excel_file');
        $filePath = $file->getPathname();

        ExcelQueue::dispatch($filePath)->onQueue('imports');
    }
    public function export(Request $request)
    {
        $hiddenColumns = $request->input('hidden_columns', []);

        $table = new Catalog();
        $visibleColumns = array_diff($table->getFillable(), $hiddenColumns);

        $tableData = Catalog::select($visibleColumns)->get();

        // Create a new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set the column headings
        $columnHeadings = array_keys($tableData->first()->toArray());
        $sheet->fromArray($columnHeadings, null, 'A1');

        // Set the table data
        $tableRows = $tableData->map(function ($row) use ($visibleColumns) {
            return collect($row->toArray())->only($visibleColumns)->values()->all();
        })->toArray();
        $sheet->fromArray($tableRows, null, 'A2');

        $writer = new Xlsx($spreadsheet);
        $filename = 'table_export.xlsx';

        // Save the spreadsheet to a file
        $writer->save($filename);

        // Download the spreadsheet
        return response()->download($filename)->deleteFileAfterSend(true);
    }
}

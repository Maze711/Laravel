<?php

namespace App\Http\Controllers\ExcelChecker;

use App\Http\Controllers\Controller;
use App\Imports\CatalogImport;
use App\Jobs\ExcelImportJob;
use App\Jobs\ExcelQueue;
use App\Models\Catalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\HtmlString;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;


class ExcelImporterController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 20; // Number of rows per page
        $page = $request->query('page', 1);

        // Fetch the data from the database using pagination
        $rows = DB::table('catalogs')->paginate($perPage, ['*'], 'page', $page);

        if ($rows->isEmpty()) {
            $page = 1;
            return view('view', ['empty' => 'The Database is empty.']);
        } else {
            $schemaManager = Schema::getConnection()->getDoctrineSchemaManager();
            $tableDetails = $schemaManager->listTableDetails('catalogs');
            $databaseColumnNames = $tableDetails->getColumns();

            // Extract the column names from the column objects
            $columnNames = array_map(function ($column) {
                return $column->getName();
            }, $databaseColumnNames);

            // Pass the pagination data along with the rows and column names
            $totalRows = DB::table('catalogs')->count();

            // Calculate the range of rows being shown
            $startRow = ($page - 1) * $perPage + 1;
            $endRow = min($page * $perPage, $totalRows);

            return view('view', [
                'rows' => $rows,
                'columns' => $columnNames,
                'totalRows' => $totalRows,
                'startRow' => $startRow,
                'endRow' => $endRow,
            ]);
        }
    }

    public function Import(Request $request)
    {
        ini_set('memory_limit', '50G');
        $request->validate([
            'excel_file' => 'required|mimes:csv,xls,xlsx'
        ]);

        $file = $request->file('excel_file');
        $dbHeaders = DB::getSchemaBuilder()->getColumnListing('catalogs');
        array_shift($dbHeaders);

        $spreadsheet = IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        $dataRows = $worksheet->toArray();

        $headerRow = array_shift($dataRows);
        $collection = collect($headerRow);
        $dataIndexNames = $collection->values()->toArray();

        $areColumnsEqual = empty(array_diff_assoc($dataIndexNames, $dbHeaders));

        if (!$areColumnsEqual) {
            $error = 'There is an error in the column header';
            $fileName = 'text.txt';
            Storage::put($fileName, $error);
            return redirect()->back()->with(['error' => 'Error']);
        } 

        $temporaryPath = 'excel_chunks/' . $file->getClientOriginalName();
        Storage::disk('local')->put($temporaryPath, file_get_contents($file));

        ExcelQueue::dispatch($temporaryPath, $dbHeaders)->onQueue('imports');
        return redirect()->back()->with(['success' => 'File import process has been initiated.']);

        // ini_set('max_execution_time', 500);
        // ini_set('memory_limit', '50G');
        // $request->validate([
        //     'excel_file' => 'required|mimes:csv,xls,xlsx'
        // ]);

        // $file = $request->file('excel_file');
        // $filePath = $file->getPathname();

        // $spreadsheet = IOFactory::load($filePath);
        // $worksheet = $spreadsheet->getActiveSheet();
        // $dataRows = $worksheet->toArray();

        // $headerRow = array_shift($dataRows);
        // $collection = collect($headerRow);
        // $dataIndexNames = $collection->values()->toArray();
        // $dataIndexNamesString = implode(', ', $dataIndexNames);

        // $dbHeaders = DB::getSchemaBuilder()->getColumnListing('catalogs');
        // array_shift($dbHeaders);
        // $indexNamesString = implode(', ', $dbHeaders);

        // $areColumnsEqual = ($dataIndexNamesString === $indexNamesString);

        // if (!$areColumnsEqual) {
        //     return redirect()->back()->with(['error' => 'There is an error in the column header']);
        // }

        // foreach ($dataRows as $rowData) {
        //     $data = array_combine($dataIndexNames, $rowData);
        //     $primaryKey = [
        //         'brand' => $data['brand'],
        //         'mspn' => $data['mspn'],
        //     ];

        //     // dd($primaryKey);

        //     // Check if both brand and mspn values are not empty or null
        //     if (!empty($primaryKey['brand']) && !empty($primaryKey['mspn'])) {
        //         Catalog::updateOrCreate($primaryKey, $data);
        //     }
        // }

        // $emptyCells = [];

        // foreach ($dataRows as $rowIndex => $rowData) {
        //     foreach ($rowData as $cellIndex => $cellData) {
        //         if (in_array($dataIndexNames[$cellIndex], ['category', 'brand', 'mspn']) && empty($cellData)) {
        //             $emptyCells[] = "In row " . ($rowIndex + 2) . " from " . $dataIndexNames[$cellIndex] . " is empty";
        //         }
        //     }
        // }

        // if (!empty($emptyCells)) {
        //     $errorMessage = implode('<br>', $emptyCells);
        //     return redirect()->back()->with(['error' => new HtmlString($errorMessage)]);
        // }

        // return redirect()->back()->with(['success' => 'File is importing']);
    }

    // public function Import(Request $request)
    // {
    //     $request->validate([
    //         'excel_file' => 'required|mimes:csv,xls,xlsx'
    //     ]);

    //     $file = $request->file('excel_file');

    //     $temporaryPath = 'ExcelFolder/' . $file->getClientOriginalName();
    //     // dd($temporaryPath);
    //     Storage::disk('local')->put($temporaryPath, file_get_contents($file));
    //     ExcelImportJob::dispatch($temporaryPath)->onQueue('imports');

    //     return redirect()->back()->with(['success' => 'File is importing']);

    //     // ...

    // }

    public function export(Request $request)
    {
        $hiddenColumns = $request->input('hidden_columns', []);

        $table = new Catalog();
        $visibleColumns = array_diff($table->getFillable(), $hiddenColumns);

        // Get the column headings from the database (excluding "id" column)
        $columnHeadings = $table->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing($table->getTable());
        $columnHeadings = array_filter($columnHeadings, function ($column) {
            return $column !== 'id';
        });
        // Create a new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set the column headings
        $sheet->fromArray([$columnHeadings], null, 'A1');

        // Set dropdown filters and bold font for column headings
        $lastColumnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($columnHeadings));
        $filterRange = 'A1:' . $lastColumnLetter . '1';
        $sheet->setAutoFilter($filterRange);

        $boldFont = new Font();
        $boldFont->setBold(true);
        $sheet->getStyle($filterRange)->getFont()->setBold(true);

        // Set the width of the columns
        foreach (range('A', $lastColumnLetter) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'Catalog Template.xlsx';

        // Save the spreadsheet to a file
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($filename);

        // Download the spreadsheet
        return response()->download($filename)->deleteFileAfterSend(true);
    }
}

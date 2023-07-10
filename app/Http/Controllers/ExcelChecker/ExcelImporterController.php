<?php

namespace App\Http\Controllers\ExcelChecker;

use App\Http\Controllers\Controller;
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

use Maatwebsite\Excel\Facades\Excel;


class ExcelImporterController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 10; // Number of rows per page
        $page = $request->query('page', 1);

        // Fetch the data from the database using pagination
        $rows = DB::table('catalogs')->paginate($perPage, ['*'], 'page', $page);

        if ($rows->isEmpty()) {
            return view('view', ['empty' => 'The Database is empty.']);
        } else {
            $databaseColumnNames = Schema::getColumnListing('catalogs');

            // Pass the pagination data along with the rows and column names
            $totalRows = DB::table('catalogs')->count();

            // Calculate the range of rows being shown
            $startRow = ($page - 1) * $perPage + 1;
            $endRow = min($page * $perPage, $totalRows);

            return view('view', [
                'rows' => $rows,
                'columns' => $databaseColumnNames,
                'pagination' => $rows->links()->toHtml(),
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
        $filePath = Excel::toArray([], $file)[0];
        $headerRow = array_slice($filePath, 0, 1)[0];
        $collection = collect($headerRow);
        $dataIndexNames = $collection->values()->toArray();
        $dataIndexNamesString = implode(', ', $dataIndexNames);

        $dbHeaders = DB::getSchemaBuilder()->getColumnListing('catalogs');
        array_shift($dbHeaders);
        $indexNamesString = implode(', ', $dbHeaders);

        $areColumnsEqual = ($dataIndexNamesString === $indexNamesString);

        if (!$areColumnsEqual) {
            return redirect()->back()->with(['error' => 'There is error in the column header']);
        }
        $temporaryPath = $file->store('excel_chunks');

        ExcelQueue::dispatch($temporaryPath)->onQueue('imports');

        return redirect()->back()->with(['success' => 'File is importing']);
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

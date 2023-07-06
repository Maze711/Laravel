<?php

namespace App\Http\Controllers\ExcelChecker;

use App\Http\Controllers\Controller;
use App\Jobs\ExcelImportJob;
use App\Jobs\ExcelQueue;
use App\Models\Catalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;


class ExcelImporterController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 10; // Number of rows per page
        $page = $request->query('page', 1);

        // Fetch the data from the database using pagination
        $rows = Catalog::paginate($perPage, ['*'], 'page', $page);

        if ($rows->isEmpty()) {
            return view('view', ['empty' => 'The Database is empty.']);
        } else {
            $databaseColumnNames = Schema::getColumnListing('catalogs');

            // Pass the pagination data along with the rows and column names
            return view('view', [
                'rows' => $rows,
                'columns' => $databaseColumnNames,
                'pagination' => $rows->links()->toHtml()
            ]);
        }
    }

    public function getData($page)
    {
        $perPage = 10; // Number of rows per page

        // Fetch the data from the database using pagination
        $rows = Catalog::paginate($perPage, ['*'], 'page', $page);

        // Render the view and pass the data
        $html = view('partials.rows')->with('rows', $rows)->render();
        $pagination = $rows->links()->toHtml();

        // Return the JSON response with the data and pagination links
        return response()->json([
            'data' => $html,
            'pagination' => $pagination,
        ]);
    }

    public function Import(Request $request)
    {
        set_time_limit(500);
        ini_set('memory_limit', '50G');
        $request->validate([
            'excel_file' => 'required|mimes:csv,xls,xlsx'
        ]);

        $file = $request->file('excel_file');
        $filePath = $file->getPathname();

        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        $rows = $worksheet->toArray();
        $highestRow = $rows[0];
        $sliceHighestRow = array_slice($rows, 1);
        $collection = collect($highestRow);

        $dataIndexNames = $collection->values()->toArray();
        $dataIndexNamesString = implode(', ', $dataIndexNames);
        // dd($dataIndexNamesString);

        $databaseColumnNames = Schema::getColumnListing('catalogs');
        array_shift($databaseColumnNames);
        $indexNamesString = implode(', ', $databaseColumnNames);
        // dd($indexNamesString);

        $areColumnsEqual = ($dataIndexNamesString === $indexNamesString);
        // dd($areColumnsEqual);

        if (!$areColumnsEqual) {
            return redirect()->back()->with(['error' => 'There is error in the column header']);
        }
        $temporaryPath = 'excel_chunks/' . $file->getClientOriginalName();
        Storage::disk('local')->put($temporaryPath, file_get_contents($file));

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

<?php

namespace App\Http\Controllers\ExcelChecker;

use App\Http\Controllers\Controller;
use App\Jobs\ExcelQueue;
use App\Models\Catalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use League\CommonMark\Extension\Table\Table;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Pagination\Paginator;


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
        ini_set('post_max_size', '1000G');
        ini_set('upload_max_filesize', '1000G');
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
<?php

namespace App\Http\Controllers\ExcelChecker;

use App\Http\Controllers\Controller;
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

        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        $rows = $worksheet->toArray();
        $worksheet = $rows[0];
        $dataRows = array_slice($rows, 1);
        $collection = collect($worksheet);
        $dataIndexNames = $collection->values()->toArray();
        $dataIndexNamesString = implode(', ', $dataIndexNames);
        // dd($dataIndexNamesString);


        $databaseColumnNames = Schema::getColumnListing('catalogs');
        array_shift($databaseColumnNames); // Remove the first element from the array
        $indexNamesString = implode(', ', $databaseColumnNames);
        // dd($indexNamesString);

        $areColumnsEqual = ($dataIndexNamesString === $indexNamesString);
        // dd($areColumnsEqual);

        if ($areColumnsEqual) {
            $collection = collect($dataRows);
            $results = $collection->map(function ($row) use ($worksheet) {
                return array_combine($worksheet, $row);
            });

            $chunks = $results->chunk(10);

            foreach ($chunks as $chunk) {
                $chunk->map(function ($row) use ($dataIndexNames) {
                    return array_combine($dataIndexNames, $row);
                })->each(function ($row) {
                    $primaryKey = ['brand' => $row['brand'], 'mspn' => $row['mspn']];
                    Catalog::updateOrCreate($primaryKey, $row);
                });
            }
            return redirect()->back()->with(['match' => 'Excel imported successfully', 'rows' => $rows]);
        } else {
            return redirect()->back()->with(['error' => 'Excel is not match from database columns']);
        }
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

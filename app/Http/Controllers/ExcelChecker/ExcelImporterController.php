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
        set_time_limit(300);
        ini_set('memory_limit', '10G'); 
        $request->validate([
            'excel_file' => 'required|mimes:csv,xls,xlsx'
        ]);

        $file = $request->file('excel_file');
        $filePath = $file->getPathname();

        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        $rowCount = $worksheet->getHighestRow();
        $rowsPerChunk = ceil($rowCount / 10);

        $chunks = [];
        for ($chunkIndex = 1; $chunkIndex <= 200; $chunkIndex++) {
            $chunkSpreadsheet = new Spreadsheet();
            $chunkWorksheet = $chunkSpreadsheet->getActiveSheet();

            // Set the headers in the first row of each chunk
            $headers = $worksheet->rangeToArray('A1:' . $worksheet->getHighestColumn() . '1', null, true, false);
            $chunkWorksheet->fromArray($headers[0], null, 'A1');

            // Set the rows for the current chunk
            $startRow = ($chunkIndex - 1) * $rowsPerChunk + 2;
            $endRow = min($startRow + $rowsPerChunk - 1, $rowCount);
            $rows = $worksheet->rangeToArray('A' . $startRow . ':' . $worksheet->getHighestColumn() . $endRow, null, true, false);
            $chunkWorksheet->fromArray($rows, null, 'A2');

            // Save the chunk as a temporary file
            $tempFilePath = 'excel_chunks/' . uniqid('excel_chunk_') . '.xlsx';
            $writer = IOFactory::createWriter($chunkSpreadsheet, 'Xlsx');
            $writer->save(storage_path('app/' . $tempFilePath));

            $chunks[] = storage_path('app/' . $tempFilePath);
        }
        foreach ($chunks as $chunkPath) {
            $this->importChunkToDatabase($chunkPath);
            // Storage::delete($chunkPath);
        }
    }

    private function importChunkToDatabase($chunkPath)
    {
        set_time_limit(200);
        ini_set('memory_limit', '10G'); 
        $chunkSpreadsheet = IOFactory::load($chunkPath);
        $chunkWorksheet = $chunkSpreadsheet->getActiveSheet();
        $requiredColumns = ['brand', 'category'];

        $rows = $chunkWorksheet->toArray();
        $headerRow = array_shift($rows); // Remove the header row from the rows array

        foreach ($rows as $row) {
            $data = array_combine($headerRow, $row);
            if ($this->validateRequiredColumns($data, $requiredColumns, $chunkPath)) {
                $primaryKey = ['brand' => $data['brand'], 'mspn' => $data['mspn']];
                Catalog::updateOrCreate($primaryKey, $data);
            }
        }
        File::delete($chunkPath);
        return redirect()->back()->with(['match' => 'Excel imported successfully', 'rows' => $rows]);
    }

    private function validateRequiredColumns($data, $requiredColumns, $chunkPath)
    {
        foreach ($requiredColumns as $column) {
            if (empty($data[$column])) {
                return false;
            }
        }
        Storage::delete($chunkPath);
        return redirect()->back()->with(['error' => 'Excel is not match from database columns']);
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

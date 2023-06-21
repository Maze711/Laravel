<?php

namespace App\Http\Controllers\ExcelChecker;

use App\Http\Controllers\Controller;
use App\Models\Catalog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelImporterController extends Controller
{
    public function index(Request $request)
    {
        $rows = Catalog::paginate(10);
        // dd($rows);
        if (empty($rows)) {
            return view('view',  ['empty' => 'The Database is empty.']);
        } else {
            return view('view', ['rows' => $rows]);
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


        $databaseColumnNames = Catalog::find(1)->toArray();
        $indexNames = array_keys($databaseColumnNames);
        $indexNamesString = implode(', ', $indexNames);
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
}

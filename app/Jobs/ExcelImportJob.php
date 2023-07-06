<?php

namespace App\Jobs;

use App\Models\Catalog;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Illuminate\Support\Facades\Storage;

class ExcelImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $temporaryPath;

    /**
     * Create a new job instance.
     *
     * @param string $temporaryPath
     * @return void
     */
    public function __construct(string $temporaryPath)
    {
        $this->temporaryPath = $temporaryPath;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // $temporaryPath = storage_path('app/ExcelFolder/catalog.xlsx');
        // dd($temporaryPath);
        ini_set('memory_limit', '50G');
        if (!Storage::disk('local')->exists($this->temporaryPath)) {
            return;
        }
        $filePath = storage_path('app/' . $this->temporaryPath);
        $spreadsheet = IOFactory::load($filePath);

        $worksheet = $spreadsheet->getActiveSheet();

        $rows = $worksheet->toArray();
        $headerRow = $rows[0];
        $dataRows = array_slice($rows, 1);

        $databaseColumnNames = Schema::getColumnListing('catalogs');
        array_shift($databaseColumnNames);

        $areColumnsEqual = $this->areColumnsEqual($headerRow, $databaseColumnNames);

        if (!$areColumnsEqual) {
            return;
        }

        $chunkSize = 1000;
        $dataChunks = array_chunk($dataRows, $chunkSize);

        // Move each data chunk to the excel_chunks directory
        foreach ($dataChunks as $chunk) {
            $this->moveChunkToDirectory($chunk);
        }

        // Cleanup: Delete the original file
        Storage::disk('local')->delete($this->temporaryPath);
    }

    private function areColumnsEqual($headerRow, $databaseColumnNames)
    {
        $headerRow = array_map('strtolower', $headerRow);
        $databaseColumnNames = array_map('strtolower', $databaseColumnNames);

        // Check if the header row matches the database columns
        return count(array_diff($headerRow, $databaseColumnNames)) === 0;
    }

    private function moveChunkToDirectory(array $chunk)
    {
        ini_set('memory_limit', '50G');
        // Generate a unique file name for the chunk
        $chunkFileName = 'chunk_' . uniqid() . '.xlsx';

        // Determine the new directory path for the chunk
        $chunkDirectory = 'excel_chunks/';

        // Create the directory if it doesn't exist
        if (!Storage::disk('local')->exists($chunkDirectory)) {
            Storage::disk('local')->makeDirectory($chunkDirectory);
        }

        // Create a new Excel file for the chunk
        $chunkSpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $chunkWorksheet = $chunkSpreadsheet->getActiveSheet();

        // Add the header row
        $headerRow = array_keys($chunk[0]);
        $chunkWorksheet->fromArray([$headerRow], null, 'A1');

        // Add the data rows
        $dataRows = array_map(function ($row) {
            return array_values($row);
        }, $chunk);
        $chunkWorksheet->fromArray($dataRows, null, 'A2');

        // Save the chunk file
        $chunkFilePath = $chunkDirectory . $chunkFileName;
        $writer = IOFactory::createWriter($chunkSpreadsheet, 'Xlsx');
        $writer->save($chunkFilePath);

        return $chunkFilePath;
    }
}

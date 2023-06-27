<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Catalog;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $spreadsheet = IOFactory::load($this->filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        $rowCount = $worksheet->getHighestRow();
        $rowsPerChunk = ceil($rowCount / 10);

        $chunks = [];
        for ($chunkIndex = 1; $chunkIndex <= 10; $chunkIndex++) {
            $chunkSpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
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
            Storage::delete($chunkPath);
        }
    }

    private function importChunkToDatabase($chunkPath)
    {
        $chunkSpreadsheet = IOFactory::load($chunkPath);
        $chunkWorksheet = $chunkSpreadsheet->getActiveSheet();
        $requiredColumns = ['brand'];

        $rows = $chunkWorksheet->toArray();
        $headerRow = array_shift($rows); // Remove the header row from the rows array

        foreach ($rows as $row) {
            $data = array_combine($headerRow, $row);
            if ($this->validateRequiredColumns($data, $requiredColumns)) {
                $primaryKey = ['brand' => $data['brand'], 'mspn' => $data['mspn']];
                Catalog::updateOrCreate($primaryKey, $data);
            }
        }
        File::delete($chunkPath);
        throw new \Exception('Excel Imported Successfully');
    }

    private function validateRequiredColumns($data, $requiredColumns)
    {
        foreach ($requiredColumns as $column) {
            if (empty($data[$column])) {
                return false;
            }
        }
        return true;
    }
}

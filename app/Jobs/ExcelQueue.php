<?php

namespace App\Jobs;

use App\Models\Catalog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use PhpOffice\PhpSpreadsheet\IOFactory;


use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;


class ExcelQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $temporaryPath;
    protected $dbHeaders;
    /**
     * Create a new job instance.
     *
     * @param string $temporaryPath
     */
    public function __construct($temporaryPath, $dbHeaders)
    {
        $this->temporaryPath = $temporaryPath;
        $this->dbHeaders = $dbHeaders;
    }

    /**
     * Execute the job.
     *
     * @return void
     */

    public function handle()
    {
        ini_set('memory_limit', '50G');

        $temporaryPath = $this->temporaryPath;
        $temporaryPath = storage_path('app/' . $temporaryPath);

        $spreadsheet = IOFactory::load($temporaryPath);
        $worksheet = $spreadsheet->getActiveSheet();
        $dataRows = $worksheet->toArray();

        $headerRow = array_shift($dataRows);
        $collection = collect($headerRow);
        $dataIndexNames = $collection->values()->toArray();
        $emptyCells = [];
        foreach ($dataRows as $rowIndex => $rowData) {
            $data = array_combine($dataIndexNames, $rowData);
            $primaryKey = [
                'brand' => $data['brand'],
                'mspn' => $data['mspn'],
            ];
            if (!empty($primaryKey['brand']) && !empty($primaryKey['mspn'])) {
                Catalog::updateOrCreate($primaryKey, $data);
            } else {
                // Add to empty cells error array
                $rowNumber = $rowIndex + 2;
                $emptyCellErrors = [];

                foreach (['category', 'brand', 'mspn'] as $columnName) {
                    if (empty($data[$columnName])) {
                        $emptyCellErrors[] = "In row " . ($rowIndex + 2) . " from " . $columnName . " is empty";
                    }
                }

                if (!empty($emptyCellErrors)) {
                    $emptyCells[] = implode(', ', $emptyCellErrors);
                }
            }
        }

        Storage::delete($temporaryPath);

        if (!empty($emptyCells)) {
            $errorMessage = implode('<br>', $emptyCells);
            $this->markAsFailed($errorMessage);
            return;
        }
    }


    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed($exception)
    {
        // Log the failure, send notification, etc.
    }

    /**
     * Mark the job as failed with the given error message.
     *
     * @param string $errorMessage
     * @return void
     */
    protected function markAsFailed(string $errorMessage)
    {
        throw new \Exception('An error occurred during task processing.' . $errorMessage);
        // $response = new HtmlString($errorMessage);
        // Log::error('Import job failed: ' . $errorMessage);

        // Store the response in cache or database for retrieval
        // You can customize the storage mechanism based on your requirements
        // Example: Cache::put('import_error', $response, 60);

        // Log the failure, send notification, etc.
    }

    // private function validateRequiredColumns($data, $requiredColumns)
    // {
    //     foreach ($requiredColumns as $column) {
    //         if (empty($data[$column])) {
    //             return false;
    //         }
    //     }
    //     return true;
    // }
}

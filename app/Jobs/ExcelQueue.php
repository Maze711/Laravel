<?php

namespace App\Jobs;

use App\Models\Catalog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Support\Facades\Log;

class ExcelQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The path of the batch chunks.
     *
     * @var array
     */
    protected $temporaryPath;

    public function __construct($temporaryPath)
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
        ini_set('memory_limit', '50G');
        $temporaryPath = $this->temporaryPath;
        $filePath = storage_path('app/' . $temporaryPath);

        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $requiredColumns = ['brand'];

        $rows = $worksheet->toArray();
        $headerRow = array_shift($rows); // Remove the header row from the rows array

        $progressOutput = new ConsoleOutput();
        $progressBar = new ProgressBar($progressOutput, count($rows));
        $progressBar->setFormat('debug');
        $progressBar->start();

        $chunkSize = 1000; // Set the desired chunk size
        $chunks = array_chunk($rows, $chunkSize);

        foreach ($chunks as $chunk) {
            $rowsToInsert = [];
            foreach ($chunk as $row) {
                $data = array_combine($headerRow, $row);
                if ($this->validateRequiredColumns($data, $requiredColumns)) {
                    $primaryKey = ['brand' => $data['brand'], 'mspn' => $data['mspn']];

                    $existingRow = Catalog::where($primaryKey)->first();
                    if ($existingRow) {
                        $hasUpdates = false;
                        foreach ($data as $column => $value) {
                            if ($existingRow->$column != $value) {
                                $hasUpdates = true;
                                break;
                            }
                        }
                        if (!$hasUpdates) {
                            continue; // Skip the row if no updated cells
                        }
                    }

                    $rowsToInsert[] = $data;
                }

                $progressBar->advance();
                Log::info('Progress: ' . $progressBar->getProgress());
            }

            Catalog::upsert($rowsToInsert, ['brand', 'mspn']);
        }

        $progressBar->finish();
        $progressOutput->writeln('');

        Storage::disk('local')->delete($temporaryPath);
        // throw new \Exception('Excel Imported Successfully');
    }

    /**
     * Validate the presence of required columns in the data.
     *
     * @param  array  $data
     * @param  array  $requiredColumns
     * @return bool
     */
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

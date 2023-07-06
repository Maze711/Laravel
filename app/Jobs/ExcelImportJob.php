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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Helper\ProgressBar;


class ExcelImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }
    public function handle()
    {
        $filePath = storage_path('app/ExcelFolder/' . $this->filePath);
        ini_set('memory_limit', '50G');
        ini_set('post_max_size', '50G');
        ini_set('upload_max_filesize', '50G');

        try {
            $reader = new Xlsx();

            $spreadsheet = $reader->load($this->filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            // Validate the column headers
            if (!$this->validateColumnHeaders($worksheet)) {
                throw new \Exception('There is an error in the column header.');
            }
        } catch (\Exception $e) {
            $this->importAndDeleteFile($filePath);
        }
    }
    protected function validateColumnHeaders(Worksheet $worksheet)
    {
        $rows = $worksheet->toArray();
        $highestRow = $rows[0];
        $sliceHighestRow = array_slice($rows, 1);
        $collection = collect($highestRow);
        $dataIndexNames = $collection->values()->toArray();

        $databaseColumnNames = Schema::getColumnListing('catalogs');
        array_shift($databaseColumnNames);
        $indexNamesString = implode(', ', $databaseColumnNames);

        $dataIndexNamesString = implode(', ', $dataIndexNames);

        return ($dataIndexNamesString === $indexNamesString);
    }

    private function importAndDeleteFile($filePath)
    {
        ini_set('memory_limit', '50G');
        ini_set('post_max_size', '50G');
        ini_set('upload_max_filesize', '50G');

        $filePath = storage_path('app/ExcelFolder/' . $filePath); // Update the file path

        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $requiredColumns = ['brand'];

        $rows = $worksheet->toArray();
        $headerRow = array_shift($rows); // Remove the header row from the rows array

        $progressOutput = new ConsoleOutput();
        $progressBar = new ProgressBar($progressOutput, count($rows));
        $progressBar->setFormat('debug');
        $progressBar->start();

        foreach ($rows as $row) {
            $data = array_combine($headerRow, $row);
            if ($this->validateRequiredColumns($data, $requiredColumns)) {
                $primaryKey = ['brand' => $data['brand'], 'mspn' => $data['mspn']];
                Catalog::updateOrCreate($primaryKey, $data);
            }

            $progressBar->advance();
            Log::info('Progress: ' . $progressBar->getProgress());
        }

        $progressBar->finish();
        $progressOutput->writeln('');

        unlink($filePath); // Delete the file after importing

        // throw new \Exception('Excel Imported Successfully');
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

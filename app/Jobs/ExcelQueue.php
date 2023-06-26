<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Schema;
use App\Models\Catalog;
use Illuminate\Support\Facades\Log;
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
        set_time_limit(120);
        $spreadsheet = IOFactory::load($this->filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $headerRow = $worksheet->getRowIterator()->current();

        $requiredColumns = ['brand', 'category'];
        $columnIndices = [];
        $emptyRows = [];
        $maxAttempts = 2;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            try {
                $spreadsheet = IOFactory::load($this->filePath);
                $worksheet = $spreadsheet->getActiveSheet();
                $headerRow = $worksheet->getRowIterator()->current();

                foreach ($requiredColumns as $requiredColumn) {
                    $columnIndex = null;

                    foreach ($headerRow->getCellIterator() as $cell) {
                        $cellValue = $cell->getValue();

                        if ($cellValue === $requiredColumn) {
                            $columnIndex = $cell->getColumn();
                            break;
                        }
                    }

                    if ($columnIndex === null) {
                        // Handle the case where a required column is not found in the header row
                        throw new \ErrorException('Required column "' . $requiredColumn . '" not found in the Excel file.');
                    }

                    $columnIndices[$requiredColumn] = $columnIndex;
                }

                foreach ($worksheet->getRowIterator() as $row) {
                    if ($row->getRowIndex() === $headerRow->getRowIndex()) {
                        // Skip the header row
                        continue;
                    }

                    foreach ($requiredColumns as $requiredColumn) {
                        $columnIndex = $columnIndices[$requiredColumn];
                        $cell = $worksheet->getCell($columnIndex . $row->getRowIndex());
                        $cellValue = $cell->getValue();

                        if (empty($cellValue)) {
                            // Store the index of the empty row for further processing or validation
                            $emptyRows[$row->getRowIndex()][] = $requiredColumn;
                        }
                    }
                }

                if (!empty($emptyRows)) {
                    // Handle the case where there are empty values in the required columns
                    $errorMessage = 'Empty values found in the following required columns: ';
                    foreach ($emptyRows as $rowIndex => $columns) {
                        $errorMessage .= 'Row ' . $rowIndex . ': ' . implode(', ', $columns) . '; ';
                    }
                    throw new \ErrorException($errorMessage);
                }

                // Import Function
                $rows = $worksheet->toArray();
                $worksheet = $rows[0];
                $dataRows = array_slice($rows, 1);
                $collection = collect($worksheet);
                $dataIndexNames = $collection->values()->toArray();
                $dataIndexNamesString = implode(', ', $dataIndexNames);

                $databaseColumnNames = Schema::getColumnListing('catalogs');
                array_shift($databaseColumnNames);
                $indexNamesString = implode(', ', $databaseColumnNames);

                $areColumnsEqual = ($dataIndexNamesString === $indexNamesString);

                if ($areColumnsEqual) {
                    $collection = collect($dataRows);
                    $results = $collection->map(function ($row) use ($dataIndexNames) {
                        return array_combine($dataIndexNames, $row);
                    });

                    $results->each(function ($row) {
                        $primaryKey = ['brand' => $row['brand'], 'mspn' => $row['mspn']];
                        Catalog::updateOrCreate($primaryKey, $row);
                    });

                    throw new \Exception('Excel Imported Successfully');
                } else {
                    throw new \ErrorException('Columns from the Excel file do not match the database columns');
                }
            } catch (\Exception $e) {
                $attempt++;
                if ($attempt < $maxAttempts) {
                    sleep(10); // Delay for 1 minute before the next attempt
                } else {
                    // After 5 attempts, throw the exception to return the errors
                    throw $e;
                }
            }
        }
    }
}

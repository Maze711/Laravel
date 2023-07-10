<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ColumnCheck implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $temporaryPath;

    /**
     * Create a new job instance.
     *
     * @param  string  $temporaryPath
     * @return void
     */
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

        
        $fileContents = Storage::disk('local')->get($this->temporaryPath);
        $filePath = storage_path('app/' . $this->temporaryPath);

        $data = Excel::toArray([], $filePath)[0];
        $headerRow = array_shift($data);
        $collection = collect($headerRow);
        $dataIndexNames = $collection->values()->toArray();
        $dataIndexNamesString = implode(', ', $dataIndexNames);

        $dbHeaders = DB::getSchemaBuilder()->getColumnListing('catalogs');
        array_shift($dbHeaders);
        $indexNamesString = implode(', ', $dbHeaders);

        $areColumnsEqual = ($dataIndexNamesString === $indexNamesString);

        if (!$areColumnsEqual) {
            
        }

        // Perform your import operations here with the $data array

        Storage::delete($this->temporaryPath);
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(Exception $exception)
    {
    }
}

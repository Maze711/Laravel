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

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Schema;
use App\Imports\CatalogImport;

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

        Schema::table('catalogs', function ($table) {
            $table->id();
            $table->string('unq_id', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->nullable()->default('')->change();
            $table->integer('category')->default(0)->nullable()->change();
            $table->string('sub_category')->default('')->nullable()->change();
            $table->string('discontinued', 10)->default('No')->nullable()->change();
            $table->string('legacy_brand', 50)->default('')->nullable()->change();
            $table->string('brand', 50)->default('')->nullable()->change();
            $table->string('mspn', 50)->default('')->nullable()->change();
            $table->string('external_id', 255)->default('')->nullable()->change();
            $table->string('external_id_type', 25)->default('')->nullable()->change();
            $table->string('brand_id', 50)->default('')->nullable()->change();
            $table->string('model', 255)->default('')->nullable()->change();
            $table->string('manufacturer', 255)->default('')->nullable()->change();
            $table->string('lt_p', 5)->default('')->nullable()->change();
            $table->string('size_dimensions', 50)->default('')->nullable()->change();
            $table->string('full_size', 50)->default('')->nullable()->change();
            $table->string('full_bolt_patterns', 50)->default('')->nullable()->change();
            $table->string('full_bolt_pattern_1', 50)->default('')->nullable()->change();
            $table->string('full_bolt_pattern_2', 50)->default('')->nullable()->change();
            $table->string('c_z_rated', 25)->nullable()->change();
            $table->string('rft', 10)->default('No')->nullable()->change();
            $table->mediumText('vast_description')->nullable()->change();
            $table->mediumText('description')->nullable()->change();
            $table->mediumText('long_description')->nullable()->change();
            $table->mediumText('notes')->nullable()->change();
            $table->mediumText('features')->nullable()->change();
            $table->mediumText('install_time')->nullable()->change();
            $table->integer('length_val')->default(0)->nullable()->change();
            $table->string('section_width', 100)->nullable()->change();
            $table->string('section_width_unit_id', 10)->nullable()->change();
            $table->string('aspect_ratio', 100)->nullable()->change();
            $table->string('rim_diameter', 100)->nullable()->change();
            $table->string('rim_diameter_unit_id', 10)->nullable()->change();
            $table->string('overall_diameter', 100)->default('0')->nullable()->change();
            $table->string('overall_diameter_unit_id', 10)->nullable()->change();
            $table->integer('weight_tire')->default(0)->nullable()->change();
            $table->string('weight_tire_unit_id', 10)->default('')->nullable()->change();
            $table->string('length_package', 100)->default('0')->nullable()->change();
            $table->string('length_unit_id', 10)->default('')->nullable()->change();
            $table->string('width_package', 100)->default('0')->nullable()->change();
            $table->string('width_unit_id', 10)->default('')->nullable()->change();
            $table->string('height_package', 100)->default('0')->nullable()->change();
            $table->string('height_unit_id', 10)->default('')->nullable()->change();
            $table->string('weight_package', 100)->default('0')->nullable()->change();
            $table->string('weight_unit_id', 10)->default('')->nullable()->change();
            $table->mediumText('wheel_finish')->nullable()->change();
            $table->mediumText('simple_finish')->nullable()->change();
            $table->mediumText('side_wall_style')->nullable()->change();
            $table->integer('load_index_1')->default(0)->nullable()->change();
            $table->integer('load_index_2')->default(0)->nullable()->change();
            $table->string('speed_rating', 10)->default('')->nullable()->change();
            $table->integer('load_range')->default(0)->nullable()->change();
            $table->integer('load_rating')->default(0)->nullable()->change();
            $table->string('back_spacing', 100)->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->nullable()->default('')->change();
            $table->string('offset', 25)->default('')->nullable()->change();
            $table->string('center_bore', 100)->default('0')->nullable()->change();
            $table->string('ply', 10)->default('')->nullable()->change();
            $table->string('tread_depth', 100)->default('0')->nullable()->change();
            $table->mediumText('tread_depth_unit_id')->nullable()->change();
            $table->string('rim_width', 100)->default('0')->nullable()->change();
            $table->mediumText('rim_width_unit_id')->nullable()->change();
            $table->string('max_rim_width', 100)->default('0')->nullable()->change();
            $table->mediumText('max_rim_width_unit_id')->nullable()->change();
            $table->string('min_rim_width', 100)->default('0')->nullable()->change();
            $table->mediumText('min_rim_width_unit_id')->nullable()->change();
            $table->string('utqg', 50)->default('')->nullable()->change();
            $table->integer('tread_wear')->default(0)->nullable()->change();
            $table->string('traction', 10)->default('')->nullable()->change();
            $table->string('temperature', 10)->default('')->nullable()->change();
            $table->string('warranty_type', 25)->default('')->nullable()->change();
            $table->string('warranty_in_miles', 25)->default('')->nullable()->change();
            $table->integer('max_psi')->default(0)->nullable()->change();
            $table->string('max_load_lb', 100)->default('0')->nullable()->change();
            $table->mediumText('image_url_full')->nullable()->change();
            $table->mediumText('image_url_quarter')->nullable()->change();
            $table->mediumText('image_side')->nullable()->change();
            $table->mediumText('image_url_tread')->nullable()->change();
            $table->mediumText('image_kit_1')->nullable()->change();
            $table->mediumText('image_kit_2')->nullable()->change();
            $table->string('season', 50)->default('')->nullable()->change();
            $table->string('tire_type_performance', 50)->default('')->nullable()->change();
            $table->string('car_type', 50)->default('')->nullable()->change();
            $table->string('country', 50)->default('')->nullable()->change();
            $table->string('quality_tier', 50)->default('')->nullable()->change();
            $table->string('construction', 50)->default('')->nullable()->change();
            $table->mediumText('source')->nullable()->change();
            $table->string('oem_fitments', 255)->default('')->nullable()->change();
            $table->string('status', 100)->default('')->nullable()->change();
            $table->string('msct', 10)->default('No')->nullable()->change();
            $table->string('wheel_diameter', 100)->nullable()->change();
            $table->string('wheel_width', 100)->nullable()->change();
            $table->integer('bolt_pattern_1')->default(0)->nullable()->change();
            $table->string('bolt_circle_diameter_1', 100)->default('0')->nullable()->change();
            $table->integer('bolt_pattern_2')->default(0)->nullable()->change();
            $table->string('bolt_circle_diameter_2', 100)->default('0')->nullable()->change();
        });

        Excel::import(new CatalogImport, $temporaryPath);
        Storage::delete($temporaryPath);
    }

    /**
     * Validate the presence of required columns in the data.
     *
     * @param  array  $data
     * @param  array  $requiredColumns
     * @return bool
     */
}

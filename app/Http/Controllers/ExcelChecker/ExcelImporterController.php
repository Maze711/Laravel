<?php

namespace App\Http\Controllers\ExcelChecker;

use App\Http\Controllers\Controller;
use App\Models\Catalog;
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
        $worksheet->removeRow(1);
        $rows = $worksheet->toArray();

        $columnNames = [
            'id',
            'unq_id',
            'category',
            'sub_category',
            'discontinued',
            'legacy_brand',
            'brand',
            'mspn',
            'external_id',
            'external_id_type',
            'brand_id',
            'model',
            'manufacturer',
            'lt_p',
            'size_dimensions',
            'full_size',
            'full_bolt_patterns',
            'full_bolt_pattern_1',
            'full_bolt_pattern_2',
            'c_z_rated',
            'rft',
            'vast_description',
            'description',
            'long_description',
            'notes',
            'features',
            'install_time',
            'length_val',
            'section_width',
            'section_width_unit_id',
            'aspect_ratio',
            'rim_diameter',
            'rim_diameter_unit_id',
            'overall_diameter',
            'overall_diameter_unit_id',
            'weight_tire',
            'weight_tire_unit_id',
            'length_package',
            'length_unit_id',
            'width_package',
            'width_unit_id',
            'height_package',
            'height_unit_id',
            'weight_package',
            'weight_unit_id',
            'wheel_finish',
            'simple_finish',
            'side_wall_style',
            'load_index_1',
            'load_index_2',
            'speed_rating',
            'load_range',
            'load_rating',
            'back_spacing',
            'offset',
            'center_bore',
            'ply',
            'tread_depth',
            'tread_depth_unit_id',
            'rim_width',
            'rim_width_unit_id',
            'max_rim_width',
            'max_rim_width_unit_id',
            'min_rim_width',
            'min_rim_width_unit_id',
            'utqg',
            'tread_wear',
            'traction',
            'temperature',
            'warranty_type',
            'warranty_in_miles',
            'max_psi',
            'max_load_lb',
            'image_url_full',
            'image_url_quarter',
            'image_side',
            'image_url_tread',
            'image_kit_1',
            'image_kit_2',
            'season',
            'tire_type_performance',
            'car_type',
            'country',
            'quality_tier',
            'construction',
            'source',
            'oem_fitments',
            'status',
            'msct',
            'wheel_diameter',
            'wheel_width',
            'bolt_pattern_1',
            'bolt_circle_diameter_1',
            'bolt_pattern_2',
            'bolt_circle_diameter_2',
            'created_at',
            'updated_at'
        ];
        // $columnNames = Schema::getColumnListing('Catalog');
        // dd($columnNames);

        foreach ($rows as $row) {
            $primaryKey = ['brand', 'mspn'];

            foreach ($columnNames as $index => $columnName) {
                $columnValue = $row[$index] ?? ' ';
                $modelData[$columnName] = $columnValue;
            }
            // Catalog::upsert($columnNames, $primaryKey, [$primaryKey => DB::raw($modelData)]);
            $existingRecord = Catalog::where(array_combine($primaryKey, array_intersect_key($modelData, array_flip($primaryKey))))->first();
            // Update the existing record
            if ($existingRecord) {
                $existingRecord->update($modelData);
            } else {
                // Create a new record
                Catalog::create($modelData);
            }
        }
        // dd($rows);
        return redirect()->back()->with(['rows' => $rows]);
    }
}

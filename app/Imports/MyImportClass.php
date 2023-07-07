<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithValidation;
use App\Models\Catalog;

class MyImportClass implements ToModel, WithStartRow
{
    public function model(array $row)
    {
        // Process each row of data and create a new Catalog model instance
        return new Catalog([
            'id' => $row[0],
            'unq_id' => $row[1],
            'category' => $row[2],
            'sub_category' => $row[3],
            'discontinued' => $row[4],
            'legacy_brand' => $row[5],
            'brand' => $row[6],
            'mspn' => $row[7],
            'external_id' => $row[8],
            'external_id_type' => $row[9],
            'brand_id' => $row[10],
            'model' => $row[11],
            'manufacturer' => $row[12],
            'lt_p' => $row[13],
            'size_dimensions' => $row[14],
            'full_size' => $row[15],
            'full_bolt_patterns' => $row[16],
            'full_bolt_pattern_1' => $row[17],
            'full_bolt_pattern_2' => $row[18],
            'c_z_rated' => $row[19],
            'rft' => $row[20],
            'vast_description' => $row[21],
            'description' => $row[22],
            'long_description' => $row[23],
            'notes' => $row[24],
            'features' => $row[25],
            'install_time' => $row[26],
            'length_val' => $row[27],
            'section_width' => $row[28],
            'section_width_unit_id' => $row[29],
            'aspect_ratio' => $row[30],
            'rim_diameter' => $row[31],
            'rim_diameter_unit_id' => $row[32],
            'overall_diameter' => $row[33],
            'overall_diameter_unit_id' => $row[34],
            'weight_tire' => $row[35],
            'weight_tire_unit_id' => $row[36],
            'length_package' => $row[37],
            'length_unit_id' => $row[38],
            'width_package' => $row[39],
            'width_unit_id' => $row[40],
            'height_package' => $row[41],
            'height_unit_id' => $row[42],
            'weight_package' => $row[43],
            'weight_unit_id' => $row[44],
            'wheel_finish' => $row[45],
            'simple_finish' => $row[46],
            'side_wall_style' => $row[47],
            'load_index_1' => $row[48],
            'load_index_2' => $row[49],
            'speed_rating' => $row[50],
            'load_range' => $row[51],
            'load_rating' => $row[52],
            'back_spacing' => $row[53],
            'offset' => $row[54],
            'center_bore' => $row[55],
            'ply' => $row[56],
            'tread_depth' => $row[57],
            'tread_depth_unit_id' => $row[58],
            'rim_width' => $row[59],
            'rim_width_unit_id' => $row[60],
            'max_rim_width' => $row[61],
            'max_rim_width_unit_id' => $row[62],
            'min_rim_width' => $row[63],
            'min_rim_width_unit_id' => $row[64],
            'utqg' => $row[65],
            'tread_wear' => $row[66],
            'traction' => $row[67],
            'temperature' => $row[68],
            'warranty_type' => $row[69],
            'warranty_in_miles' => $row[70],
            'max_psi' => $row[71],
            'max_load_lb' => $row[72],
            'image_url_full' => $row[73],
            'image_url_quarter' => $row[74],
            'image_side' => $row[75],
            'image_url_tread' => $row[76],
            'image_kit_1' => $row[77],
            'image_kit_2' => $row[78],
            'season' => $row[79],
            'tire_type_performance' => $row[80],
            'car_type' => $row[81],
            'country' => $row[82],
            'quality_tier' => $row[83],
            'construction' => $row[84],
            'source' => $row[85],
            'oem_fitments' => $row[86],
            'status' => $row[87],
            'msct' => $row[88],
            'wheel_diameter' => $row[89],
            'wheel_width' => $row[90],
            'bolt_pattern_1' => $row[91],
            'bolt_circle_diameter_1' => $row[92],
            'bolt_pattern_2' => $row[93],
            'bolt_circle_diameter_2' => $row[94],
            'created_at' => $row[95],
            'updated_at' => $row[96],            
        ]);
    }

    public function startRow(): int
    {
        return 2; // Start processing data from row 2 (assuming row 1 is the header)
    }
}

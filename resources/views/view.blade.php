<!DOCTYPE html>
<html>

<head>
    <title>Excel File Uploader</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            overflow-x: hidden;
        }
    </style>
</head>

<body>
    <div class="mt-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header h2">Excel Uploader</div>
                    <div class="card-body">
                        <div class="container mt-4">
                            <div class="row justify-content-between">
                                <div class="col-md-6">
                                    <form action="{{ route('import') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="input-group">
                                            <input class="form-control" type="file" name="excel_file" accept=".csv,.xls,.xlsx">
                                            <button type="submit" class="btn btn-warning">Upload</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ route('logout') }}" class="btn btn-dark">Logout</a>
                                </div>
                            </div>
                        </div>
                        @if(isset($empty))
                        <p>{{ $empty }}</p>
                        @else
                        <div class="table-responsive mt-4">
                            <table class="table text-center">
                                <thead>
                                    <tr>
                                        <!-- <th>ID</th>
                                        <th>Uniq_ID</th>
                                        <th>Category</th>
                                        <th>Sub_Category</th>
                                        <th>Discontinued</th>
                                        <th>Legacy_Brand</th>
                                        <th>Legacy_Brand</th>
                                        <th>MSPN</th>
                                        <th>External_ID</th>
                                        <th>Model</th>
                                        <th>Manufacturer</th>
                                        <th>lt_p</th>
                                        <th>size_dimensions</th>
                                        <th>full_size</th>
                                        <th>full_bolt_patterns</th>
                                        <th>full_bolt_pattern_1</th>
                                        <th>full_bolt_pattern_2</th>
                                        <th>c_z_rated</th>
                                        <th>rft</th>
                                        <th>vast_description</th>
                                        <th>description</th>
                                        <th>long_description</th>
                                        <th>notes</th>
                                        <th>features</th>
                                        <th>install_time</th>
                                        <th>length_val</th>
                                        <th>section_width</th>
                                        <th>section_width_unit_id</th>
                                        <th>aspect_ratio</th>
                                        <th>rim_diameter</th>
                                        <th>rim_diameter_unit_id</th>
                                        <th>overall_diameter</th>
                                        <th>overall_diameter_unit_id</th>
                                        <th>weight_tire</th>
                                        <th>weight_tire_unit_id</th>
                                        <th>length_package</th>
                                        <th>length_unit_id</th>
                                        <th>width_package</th>
                                        <th>width_unit_id</th>
                                        <th>height_unit_id</th>
                                        <th>weight_package</th>
                                        <th>weight_unit_id</th>
                                        <th>wheel_finish</th>
                                        <th>simple_finish</th>
                                        <th>side_wall_style</th>
                                        <th>load_index_1</th>
                                        <th>load_index_2</th>
                                        <th>speed_rating</th>
                                        <th>load_range</th>
                                        <th>load_rating</th>
                                        <th>back_spacing</th>
                                        <th>offset</th>
                                        <th>center_bore</th>
                                        <th>ply</th>
                                        <th>tread_depth</th>
                                        <th>tread_depth_unit_id</th>
                                        <th>rim_width</th>
                                        <th>rim_width_unit_id</th>
                                        <th>max_rim_width</th>
                                        <th>max_rim_width_unit_id</th>
                                        <th>min_rim_width</th>
                                        <th>min_rim_width_unit_id</th>
                                        <th>utqg</th>
                                        <th>tread_wear</th>
                                        <th>traction</th>
                                        <th>warranty_type</th>
                                        <th>warranty_in_miles</th>
                                        <th>max_psi</th>
                                        <th>max_load_lb</th>
                                        <th>image_url_full</th>
                                        <th>image_url_quarter</th>
                                        <th>image_side</th>
                                        <th>image_url_tread</th>
                                        <th>image_kit_1</th>
                                        <th>image_kit_2</th>
                                        <th>season</th>
                                        <th>tire_type_performance</th>
                                        <th>car_type</th>
                                        <th>country</th>
                                        <th>quality_tier</th>
                                        <th>construction</th>
                                        <th>source</th>
                                        <th>oem_fitments</th>
                                        <th>status</th>
                                        <th>msct</th>
                                        <th>wheel_diameter</th>
                                        <th>wheel_width</th>
                                        <th>bolt_pattern_1</th>
                                        <th>bolt_circle_diameter_1</th>
                                        <th>bolt_pattern_2</th>
                                        <th>bolt_circle_diameter_1</th>
                                        <th>bolt_circle_diameter_2</th>
                                        <th>created_at</th>
                                        <th>updated_at</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rows as $catalog)
                                    <tr>
                                        <td>{{ $catalog['id'] }}</td>
                                        <td>{{ $catalog['unq_id'] }}</td>
                                        <td>{{ $catalog['category'] }}</td>
                                        <td>{{ $catalog['sub_category'] }}</td>
                                        <td>{{ $catalog['discontinued'] }}</td>
                                        <td>{{ $catalog['legacy_brand'] }}</td>
                                        <td>{{ $catalog['brand'] }}</td>
                                        <td>{{ $catalog['mspn'] }}</td>
                                        <td>{{ $catalog['external_id'] }}</td>
                                        <td>{{ $catalog['external_id_type'] }}</td>
                                        <td>{{ $catalog['brand_id'] }}</td>
                                        <td>{{ $catalog['model'] }}</td>
                                        <td>{{ $catalog['manufacturer'] }}</td>
                                        <td>{{ $catalog['lt_p'] }}</td>
                                        <td>{{ $catalog['size_dimensions'] }}</td>
                                        <td>{{ $catalog['full_size'] }}</td>
                                        <td>{{ $catalog['full_bolt_patterns'] }}</td>
                                        <td>{{ $catalog['full_bolt_pattern_1'] }}</td>
                                        <td>{{ $catalog['full_bolt_pattern_2'] }}</td>
                                        <td>{{ $catalog['c_z_rated'] }}</td>
                                        <td>{{ $catalog['rft'] }}</td>
                                        <td>{{ $catalog['vast_description'] }}</td>
                                        <td>{{ $catalog['description'] }}</td>
                                        <td>{{ $catalog['long_description'] }}</td>
                                        <td>{{ $catalog['notes'] }}</td>
                                        <td>{{ $catalog['features'] }}</td>
                                        <td>{{ $catalog['install_time'] }}</td>
                                        <td>{{ $catalog['length_val'] }}</td>
                                        <td>{{ $catalog['section_width'] }}</td>
                                        <td>{{ $catalog['section_width_unit_id'] }}</td>
                                        <td>{{ $catalog['aspect_ratio'] }}</td>
                                        <td>{{ $catalog['rim_diameter'] }}</td>
                                        <td>{{ $catalog['rim_diameter_unit_id'] }}</td>
                                        <td>{{ $catalog['overall_diameter'] }}</td>
                                        <td>{{ $catalog['overall_diameter_unit_id'] }}</td>
                                        <td>{{ $catalog['weight_tire'] }}</td>
                                        <td>{{ $catalog['weight_tire_unit_id'] }}</td>
                                        <td>{{ $catalog['length_package'] }}</td>
                                        <td>{{ $catalog['length_unit_id'] }}</td>
                                    </tr>
                                    @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
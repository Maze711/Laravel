<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('unq_id', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->nullable(false)->default('');
            $table->integer('category')->default(0);
            $table->string('sub_category')->default('');
            $table->string('discontinued', 10)->default('No');
            $table->string('legacy_brand', 50)->default('');
            $table->string('brand', 50)->default('');
            $table->string('mspn', 50)->default('');
            $table->string('external_id', 255)->default('');
            $table->string('external_id_type', 25)->default('');
            $table->string('brand_id', 50)->default('');
            $table->string('model', 255)->default('');
            $table->string('manufacturer', 255)->default('');
            $table->string('lt_p', 5)->default('');
            $table->string('size_dimensions', 50)->default('');
            $table->string('full_size', 50)->default('');
            $table->string('full_bolt_patterns', 50)->default('');
            $table->string('full_bolt_pattern_1', 50)->default('');
            $table->string('full_bolt_pattern_2', 50)->default('');
            $table->string('c_z_rated', 25);
            $table->string('rft', 10)->default('No');
            $table->mediumText('vast_description');
            $table->mediumText('description');
            $table->mediumText('long_description');
            $table->mediumText('notes');
            $table->mediumText('features');
            $table->mediumText('install_time');
            $table->integer('length_val')->default(0);
            $table->string('section_width', 100)->nullable();
            $table->string('section_width_unit_id', 10)->nullable();
            $table->string('aspect_ratio', 100)->nullable();
            $table->string('rim_diameter', 100)->nullable();
            $table->string('rim_diameter_unit_id', 10);
            $table->string('overall_diameter', 100)->default('0');
            $table->string('overall_diameter_unit_id', 10);
            $table->integer('weight_tire')->default(0);
            $table->string('weight_tire_unit_id', 10)->default('');
            $table->string('length_package', 100)->default('0');
            $table->string('length_unit_id', 10)->default('');
            $table->string('width_package', 100)->default('0');
            $table->string('width_unit_id', 10)->default('');
            $table->string('height_package', 100)->default('0');
            $table->string('height_unit_id', 10)->default('');
            $table->string('weight_package', 100)->default('0');
            $table->string('weight_unit_id', 10)->default('');
            $table->mediumText('wheel_finish');
            $table->mediumText('simple_finish');
            $table->mediumText('side_wall_style');
            $table->integer('load_index_1')->default(0);
            $table->integer('load_index_2')->default(0);
            $table->string('speed_rating', 10)->default('');
            $table->integer('load_range')->default(0);
            $table->integer('load_rating')->default(0);
            $table->string('back_spacing', 100)->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->nullable(false)->default('');
            $table->string('offset', 25)->default('');
            $table->string('center_bore', 100)->default('0');
            $table->string('ply', 10)->default('');
            $table->string('tread_depth', 100)->default('0');
            $table->mediumText('tread_depth_unit_id');
            $table->string('rim_width', 100)->default('0');
            $table->mediumText('rim_width_unit_id');
            $table->string('max_rim_width', 100)->default('0');
            $table->mediumText('max_rim_width_unit_id');
            $table->string('min_rim_width', 100)->default('0');
            $table->mediumText('min_rim_width_unit_id');
            $table->string('utqg', 50)->default('');
            $table->integer('tread_wear')->default(0);
            $table->string('traction', 10)->default('');
            $table->string('temperature', 10)->default('');
            $table->string('warranty_type', 25)->default('');
            $table->string('warranty_in_miles', 25)->default('');
            $table->integer('max_psi')->default(0);
            $table->string('max_load_lb', 100)->default('0');
            $table->mediumText('image_url_full');
            $table->mediumText('image_url_quarter');
            $table->mediumText('image_side');
            $table->mediumText('image_url_tread');
            $table->mediumText('image_kit_1');
            $table->mediumText('image_kit_2');
            $table->string('season', 50)->default('');
            $table->string('tire_type_performance', 50)->default('');
            $table->string('car_type', 50)->default('');
            $table->string('country', 50)->default('');
            $table->string('quality_tier', 50)->default('');
            $table->string('construction', 50)->default('');
            $table->mediumText('source');
            $table->string('oem_fitments', 255)->default('');
            $table->string('status', 100)->default('');
            $table->string('msct', 10)->default('No');
            $table->string('wheel_diameter', 100)->nullable();
            $table->string('wheel_width', 100)->nullable();
            $table->integer('bolt_pattern_1')->default(0);
            $table->string('bolt_circle_diameter_1', 100)->default('0');
            $table->integer('bolt_pattern_2')->default(0);
            $table->string('bolt_circle_diameter_2', 100)->default('0');
            $table->timestamps();
            $table->unique(['brand', 'mspn']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('catalog');
    }
};

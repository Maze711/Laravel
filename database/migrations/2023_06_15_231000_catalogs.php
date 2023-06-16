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
            $table->integer('category')->default(0)->nullable();
            $table->string('sub_category')->default('')->nullable();
            $table->string('discontinued', 10)->default('No')->nullable();
            $table->string('legacy_brand', 50)->default('')->nullable();
            $table->string('brand', 50)->default('')->nullable();
            $table->string('mspn', 50)->default('')->nullable();
            $table->string('external_id', 255)->default('')->nullable();
            $table->string('external_id_type', 25)->default('')->nullable();
            $table->string('brand_id', 50)->default('')->nullable();
            $table->string('model', 255)->default('')->nullable();
            $table->string('manufacturer', 255)->default('')->nullable();
            $table->string('lt_p', 5)->default('')->nullable();
            $table->string('size_dimensions', 50)->default('')->nullable();
            $table->string('full_size', 50)->default('')->nullable();
            $table->string('full_bolt_patterns', 50)->default('')->nullable();
            $table->string('full_bolt_pattern_1', 50)->default('')->nullable();
            $table->string('full_bolt_pattern_2', 50)->default('')->nullable();
            $table->string('c_z_rated', 25)->nullable();
            $table->string('rft', 10)->default('No')->nullable();
            $table->mediumText('vast_description')->nullable();
            $table->mediumText('description')->nullable();
            $table->mediumText('long_description')->nullable();
            $table->mediumText('notes')->nullable();
            $table->mediumText('features')->nullable();
            $table->mediumText('install_time')->nullable();
            $table->integer('length_val')->default(0)->nullable();
            $table->string('section_width', 100)->nullable();
            $table->string('section_width_unit_id', 10)->nullable();
            $table->string('aspect_ratio', 100)->nullable();
            $table->string('rim_diameter', 100)->nullable();
            $table->string('rim_diameter_unit_id', 10)->nullable();
            $table->string('overall_diameter', 100)->default('0')->nullable();
            $table->string('overall_diameter_unit_id', 10)->nullable();
            $table->integer('weight_tire')->default(0)->nullable();
            $table->string('weight_tire_unit_id', 10)->default('')->nullable();
            $table->string('length_package', 100)->default('0')->nullable();
            $table->string('length_unit_id', 10)->default('')->nullable();
            $table->string('width_package', 100)->default('0')->nullable();
            $table->string('width_unit_id', 10)->default('')->nullable();
            $table->string('height_package', 100)->default('0')->nullable();
            $table->string('height_unit_id', 10)->default('')->nullable();
            $table->string('weight_package', 100)->default('0')->nullable();
            $table->string('weight_unit_id', 10)->default('')->nullable();
            $table->mediumText('wheel_finish')->nullable();
            $table->mediumText('simple_finish')->nullable();
            $table->mediumText('side_wall_style')->nullable();
            $table->integer('load_index_1')->default(0)->nullable();
            $table->integer('load_index_2')->default(0)->nullable();
            $table->string('speed_rating', 10)->default('')->nullable();
            $table->integer('load_range')->default(0)->nullable();
            $table->integer('load_rating')->default(0)->nullable();
            $table->string('back_spacing', 100)->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->nullable()->default('');
            $table->string('offset', 25)->default('')->nullable();
            $table->string('center_bore', 100)->default('0')->nullable();
            $table->string('ply', 10)->default('')->nullable();
            $table->string('tread_depth', 100)->default('0')->nullable();
            $table->mediumText('tread_depth_unit_id')->nullable();
            $table->string('rim_width', 100)->default('0')->nullable();
            $table->mediumText('rim_width_unit_id')->nullable();
            $table->string('max_rim_width', 100)->default('0')->nullable();
            $table->mediumText('max_rim_width_unit_id')->nullable();
            $table->string('min_rim_width', 100)->default('0')->nullable();
            $table->mediumText('min_rim_width_unit_id')->nullable();
            $table->string('utqg', 50)->default('')->nullable();
            $table->integer('tread_wear')->default(0)->nullable();
            $table->string('traction', 10)->default('')->nullable();
            $table->string('temperature', 10)->default('')->nullable();
            $table->string('warranty_type', 25)->default('')->nullable();
            $table->string('warranty_in_miles', 25)->default('')->nullable();
            $table->integer('max_psi')->default(0)->nullable();
            $table->string('max_load_lb', 100)->default('0')->nullable();
            $table->mediumText('image_url_full')->nullable();
            $table->mediumText('image_url_quarter')->nullable();
            $table->mediumText('image_side')->nullable();
            $table->mediumText('image_url_tread')->nullable();
            $table->mediumText('image_kit_1')->nullable();
            $table->mediumText('image_kit_2')->nullable();
            $table->string('season', 50)->default('')->nullable();
            $table->string('tire_type_performance', 50)->default('')->nullable();
            $table->string('car_type', 50)->default('')->nullable();
            $table->string('country', 50)->default('')->nullable();
            $table->string('quality_tier', 50)->default('')->nullable();
            $table->string('construction', 50)->default('')->nullable();
            $table->mediumText('source')->nullable();
            $table->string('oem_fitments', 255)->default('')->nullable();
            $table->string('status', 100)->default('')->nullable();
            $table->string('msct', 10)->default('No')->nullable();
            $table->string('wheel_diameter', 100)->nullable();
            $table->string('wheel_width', 100)->nullable();
            $table->integer('bolt_pattern_1')->default(0)->nullable();
            $table->string('bolt_circle_diameter_1', 100)->default('0')->nullable();
            $table->integer('bolt_pattern_2')->default(0)->nullable();
            $table->string('bolt_circle_diameter_2', 100)->default('0')->nullable();
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

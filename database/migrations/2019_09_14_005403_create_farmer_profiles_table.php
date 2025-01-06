<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFarmerProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('farmer_profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('rsbsa_stub_control');
            $table->string('farmer_first_name');
            $table->string('farmer_middle_name');
            $table->string('farmer_last_name');
            $table->string('farmer_suffix_name');
            $table->date('farmer_birth_date');
            $table->integer('farmer_gender');
            $table->string('farmer_contact_number')->nullable();
            $table->integer('farmer_details_id')->unsigned()->comment('farmer_details_table FK');
            $table->integer('farm_performance_id')->unsigned()->comment('farm_performance_table FK');
            $table->integer('farmer_affiliation_id')->unsigned()->comment('farm_affiliation_table FK');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('farmer_profiles');
    }
}

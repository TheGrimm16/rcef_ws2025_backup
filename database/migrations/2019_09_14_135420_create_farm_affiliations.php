<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFarmAffiliations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('farmer_affiliations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('affiliation_type')->unsigned();
            $table->string('affiliation_type_others');
            $table->text('affiliation_name');
            $table->integer('farm_accreditation')->unsigned();
            $table->string('farm_accreditation_others');
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
        Schema::dropIfExists('farmer_affiliations');
    }
}

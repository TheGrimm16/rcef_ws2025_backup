<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFarmDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('farm_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('farm_area');
            $table->integer('rice_area');
            $table->integer('tenurial_status');
            $table->integer('tenurial_type');
            $table->text('farm_brgy');
            $table->string('farm_municipality');
            $table->string('farm_province');
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
        Schema::dropIfExists('farm_details');
    }
}

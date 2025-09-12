<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLibSignatoriesPersonTable extends Migration
{
    public function up()
    {

        // 1. Create lib_signatories_position
        if (!Schema::hasTable('lib_signatories_position')) {
            Schema::create('lib_signatories_position', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 50);
                $table->timestamps();
            });
        }

        // 2. Create lib_signatories_person
        if (!Schema::hasTable('lib_signatories_person')) {
            Schema::create('lib_signatories_person', function (Blueprint $table) {
                $table->increments('id');
                $table->string('honorific_prefix', 10)->nullable();
                $table->string('complete_name', 255);
                $table->string('post_nominal', 50)->nullable();

                $table->string('cell_number', 11)->nullable();
                $table->string('email', 255)->nullable();

                $table->char('sex', 1)->nullable();
                $table->string('civil_status', 2)->nullable();

                $table->integer('position_id')->unsigned()->nullable();
                $table->foreign('position_id')->references('id')->on('lib_signatories_position');

                // $table->boolean('is_active')->default(1);
                $table->tinyInteger('is_right')->default(0);
                $table->timestamps();

                
            });
        }

        // 3. Create pivot table
        if (!Schema::hasTable('lib_signatories_person_provinces')) {
            Schema::create('lib_signatories_person_provinces', function (Blueprint $table) {
                $table->increments('id');

                $table->integer('person_id')->unsigned();
                $table->string('provCode', 10); // FK to lib_signatories_province

                $table->primary(['person_id', 'provCode']);

                $table->foreign('person_id')->references('id')->on('lib_signatories_person')->onDelete('cascade');
                $table->foreign('provCode')->references('provCode')->on('lib_provinces')->onDelete('cascade');

                $table->timestamps();
            });
        }

    }

    public function down()
    {   
        Schema::dropIfExists('lib_signatories_logs');
        Schema::dropIfExists('lib_signatories_person_provinces');
        Schema::dropIfExists('lib_signatories_person');
        Schema::dropIfExists('lib_signatories_position');
        Schema::dropIfExists('lib_signatories_province');
    }
}

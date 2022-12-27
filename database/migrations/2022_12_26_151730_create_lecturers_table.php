<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLecturersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lecturers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->dateTime('entry_year')->nullable();
            $table->dateTime('out_year')->nullable();
            $table->bigInteger('lecturer_id');
            $table->bigInteger('periode_id');
            $table->integer('departement_id');
            $table->integer('level')->nullable();
            $table->integer('is_active')->nullable();
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
        Schema::dropIfExists('lecturers');
    }
}

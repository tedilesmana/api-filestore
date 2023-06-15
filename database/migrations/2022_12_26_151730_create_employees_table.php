<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->char('employee_code', 35)->unique();
            $table->dateTime('entry_year')->nullable();
            $table->dateTime('out_year')->nullable();
            $table->bigInteger('source_employee_id');
            $table->bigInteger('departement_id')->nullable();
            $table->bigInteger('direktorat_id')->nullable();
            $table->bigInteger('rektorat_id')->nullable();
            $table->string('personal_uid')->nullable();
            $table->bigInteger('jabatan_id')->nullable();
            $table->integer('is_active')->nullable();
            $table->char('initial', 35)->nullable();
            $table->char('nidn', 35)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations1.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
}

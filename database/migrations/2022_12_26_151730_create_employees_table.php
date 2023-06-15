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
            $table->integer('departement_id');
            $table->integer('level')->nullable();
            $table->integer('is_active')->nullable();
            $table->char('initial', 35)->nullable();
            $table->char('nidn', 35)->nullable();
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
        Schema::dropIfExists('employees');
    }
}

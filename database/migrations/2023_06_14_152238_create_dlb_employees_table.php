<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDlbEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dlb_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->char('dlb_employee_code', 35)->unique();
            $table->bigInteger('source_dlb_employee_id');
            $table->string('staff_id')->nullable();
            $table->integer('is_active')->nullable();
            $table->integer('program_studi')->nullable();
            $table->char('initial', 35)->nullable();
            $table->char('nidn', 35)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dlb_employees');
    }
}

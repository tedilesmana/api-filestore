<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->char('student_code', 35)->unique();
            $table->string('nim')->nullable();
            $table->string('nif')->nullable();
            $table->string('angkatan')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('tanggal_terdaftar')->nullable();
            $table->dateTime('tanggal_lulus')->nullable();
            $table->char('semester_masuk', 35)->nullable();
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
        Schema::dropIfExists('students');
    }
}

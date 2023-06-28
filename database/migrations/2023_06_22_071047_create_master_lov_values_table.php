<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterLovValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_lov_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_lov_group_id')->constrained()
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->string('group_name')->nullable();
            $table->string('values')->nullable();
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
        Schema::dropIfExists('master_lov_values');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->char('route_code', 35)->unique();
            $table->foreignId('menu_id')->constrained()
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->foreignId('sub_menu_id')->constrained()
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->foreignId('additional_menu_id')->constrained()
                ->onUpdate('restrict')
                ->onDelete('restrict');
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
        Schema::dropIfExists('routes');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenusTable extends Migration
{
    /**
     * Run the migrations.
     * 
     * @return void
     */
    public function up()
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained()
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->char('menu_code', 35)->unique();
            $table->bigInteger('sort');
            $table->string('name')->nullable();
            $table->string('title')->nullable();
            $table->string('sub_title')->nullable();
            $table->string('path')->nullable();
            $table->longText('icon_url')->nullable();
            $table->longText('access_permissions')->nullable();
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
        Schema::dropIfExists('menus');
    }
}

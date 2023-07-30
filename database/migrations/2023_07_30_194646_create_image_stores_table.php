<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImageStoresTable extends Migration
{
    /**
     * Run the migrations. 
     *
     * @return void
     */
    public function up()
    {
        Schema::create('image_stores', function (Blueprint $table) {
            $table->id();
            $table->char('code', 25)->unique();
            $table->char('name', 50)->unique();
            $table->string('description', 191);
            $table->string('filename', 191);
            $table->char('extention', 10);
            $table->bigInteger('size');
            $table->string('directory', 191);
            $table->string('image_url', 191);
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
        Schema::dropIfExists('image_stores');
    }
}

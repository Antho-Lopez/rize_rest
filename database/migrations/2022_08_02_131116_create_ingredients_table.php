<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('meal_id')->constrained();
            $table->float('calories', 8, 2)->nullable();
            $table->float('proteins', 8, 2)->nullable();
            $table->float('glucides', 8, 2)->nullable();
            $table->float('lipids', 8, 2)->nullable();
            $table->float('portion', 8, 2)->nullable();
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
        Schema::dropIfExists('ingredients');
    }
};

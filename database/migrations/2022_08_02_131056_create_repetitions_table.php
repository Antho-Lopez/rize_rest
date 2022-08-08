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
        Schema::create('repetitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercice_id')->constrained();
            $table->foreignId('session_id')->constrained();
            $table->float('nb_repetitions', 8, 2);
            $table->float('kilos', 8, 2);
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
        Schema::dropIfExists('repetitions');
    }
};

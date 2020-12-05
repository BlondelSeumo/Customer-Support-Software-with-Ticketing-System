<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTriggerConditionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trigger_condition', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('trigger_id')->index();
            $table->integer('condition_id')->index();
            $table->integer('operator_id')->index();
            $table->text('condition_value');
            $table->string('match_type', 3)->index();

            $table->collation = config('database.connections.mysql.collation');
            $table->charset = config('database.connections.mysql.charset');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('trigger_condition');
    }
}

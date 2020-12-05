<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCannedRepliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('canned_replies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->index();
            $table->text('body');
            $table->integer('user_id');
            $table->timestamps();

            $table->unique(['name', 'user_id']);

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
        Schema::drop('canned_replies');
    }
}

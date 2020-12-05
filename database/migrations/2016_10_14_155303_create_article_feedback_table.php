<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_feedback', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('was_helpful');
            $table->text('comment')->nullable();
            $table->integer('article_id')->index();
            $table->integer('user_id')->nullable()->index();
            $table->string('ip')->nullable()->index();
            $table->timestamps();

            $table->unique(['article_id', 'user_id']);
            $table->unique(['article_id', 'ip']);

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
        Schema::drop('article_feedback');
    }
}

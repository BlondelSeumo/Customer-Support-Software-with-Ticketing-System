<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateSearchTermsTableToV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('search_terms', function (Blueprint $table) {
            $table->dropColumn('count');
            $table->dropColumn('user_id');
            $table->dropColumn('type');
            $table->dropColumn('updated_at');

            $table->string('normalized_term');
            $table->integer('result_count');
            $table->boolean('clicked_article');
            $table->boolean('created_ticket');
            $table->string('source');
            $table->string('page')->nullable();
            $table->string('category_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('search_terms', function (Blueprint $table) {
            //
        });
    }
}

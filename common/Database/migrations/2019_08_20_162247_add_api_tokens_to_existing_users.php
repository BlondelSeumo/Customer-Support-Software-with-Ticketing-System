<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AddApiTokensToExistingUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('users')
            ->whereNull('api_token')
            ->orderBy('id')
            ->chunk(50, function(Collection $users) {
                $users->each(function($user) {
                    DB::table('users')->where('id', $user->id)->update(['api_token' => Str::random(40)]);
                });
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

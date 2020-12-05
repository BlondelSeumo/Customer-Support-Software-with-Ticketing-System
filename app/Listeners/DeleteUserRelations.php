<?php

namespace App\Listeners;

use Common\Auth\Events\UsersDeleted;
use DB;

class DeleteUserRelations
{
    /**
     * @param  UsersDeleted $event
     * @return void
     */
    public function handle(UsersDeleted $event)
    {
        DB::table('purchase_codes')->whereIn('user_id', $event->users->pluck('id'))->delete();
    }
}

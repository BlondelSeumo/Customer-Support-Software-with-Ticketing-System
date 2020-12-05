<?php

use App\Ticket;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Migrations\Migration;

class SetClosedAtOnClosedTickets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Ticket::whereHas('tags', function(HasMany $query) {
            return $query->where('type', 'status')->where('name', 'closed');
        })->whereNull('closed_at')->update(['closed_at' => Carbon::now()]);
    }
}

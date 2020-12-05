<?php

use Illuminate\Database\Seeder;

class TicketsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = factory(\App\User::class)->create();

        $tickets = factory(\App\Ticket::class, 15)->create(['user_id' => $user->id]);

        $openTag = App\Tag::where('name', 'open')->first();

        foreach ($tickets as $ticket) {
            $ticket->tags()->attach($openTag->id);
            factory(App\Reply::class, 30)->create(['user_id' => $user->id, 'ticket_id' => $ticket->id]);
        }
    }
}

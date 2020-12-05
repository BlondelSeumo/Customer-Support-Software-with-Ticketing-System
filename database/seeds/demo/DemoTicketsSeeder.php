<?php

use App\Tag;
use App\User;
use App\Reply;
use App\Ticket;
use App\Category;
use Carbon\Carbon;
use Common\Auth\Roles\Role;
use Illuminate\Database\Seeder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Collection;

class DemoTicketsSeeder extends Seeder
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var Ticket
     */
    private $ticket;

    /**
     * @var int
     */
    private $ticketCount = 60;

    /**
     * @var Reply
     */
    private $reply;

    /**
     * @var Tag
     */
    private $tag;

    /**
     * @var Role
     */
    private $role;

    /**
     * DemoSeeder constructor.
     * @param User $user
     * @param Role $role
     * @param Tag $tag
     * @param Ticket $ticket
     * @param Reply $reply
     * @param Filesystem $fs
     */
    public function __construct(User $user, Role $role, Tag $tag, Ticket $ticket, Reply $reply, Filesystem $fs)
    {
        $this->fs = $fs;
        $this->tag = $tag;
        $this->user = $user;
        $this->reply = $reply;
        $this->role = $role;
        $this->ticket = $ticket;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tickets = $this->seedDemoTickets();
        $tagNames = $this->seedDemoTicketCategories();
        $this->attachTagsToTickets($tagNames, $tickets);
        $this->seedDemoReplies($tickets);
    }

    /**
     * @return Collection
     */
    private function seedDemoTickets()
    {
        $subjects = json_decode($this->fs->get(database_path('seeds/demo/demo-ticket-subjects.json')), true);
        $customers = $this->role->with('users')->where('name', 'customers')->first()->users;

        $tickets = [];

        for ($i = 0; $i <= $this->ticketCount; $i++) {
            $date = Carbon::now()->addDays(-rand(1, 30));
            $tickets[] =  ['subject' => Arr::random($subjects), 'user_id' => $customers->random()->id, 'created_at' => $date, 'updated_at' => $date];
        }

        $this->ticket->insert($tickets);

        return $this->ticket->whereIn('subject', $subjects)->get();
    }

    /**
     * Seed demo ticket categories;
     *
     * @return array
     */
    private function seedDemoTicketCategories()
    {
        $demoCategories = json_decode($this->fs->get(database_path('seeds/demo/demo-categories.json')), true);

        $tags = collect($demoCategories['parents'])->each(function($name) {
            $tag = $this->tag->create(['name' => $name, 'type' => 'category']);
            $category = app(Category::class)->where('name', $tag->name)->first();
            $tag->categories()->attach($category->id);
        })->toArray();


        return $demoCategories['parents'];
    }

    /**
     * Attach random category and status tags to all example tickets.
     *
     * @param array $tagNames
     * @param Collection $tickets
     */
    private function attachTagsToTickets($tagNames, $tickets)
    {
        $tags = $this->tag->whereIn('name', $tagNames)->get();
        $statuses = $this->tag->whereIn('name', ['open', 'closed', 'pending'])->get();

        $pivot = $tags->map(function (Tag $tag, $index) use ($tickets, $statuses) {
            $chunk = $tickets->slice($index * 10, 10);
            return $chunk->map(function (Ticket $ticket) use ($tag, $statuses) {
                return [
                    ['tag_id' => $tag->id, 'taggable_id' => $ticket->id, 'taggable_type' => Ticket::class],
                    ['tag_id' => $statuses->random()->id, 'taggable_id' => $ticket->id, 'taggable_type' => Ticket::class],
                ];
            });
        })->flatten(2)->toArray();

        DB::table('taggables')->insert($pivot);
    }

    /**
     * Seed example ticket replies for demo site.
     *
     * @param Collection $tickets
     */
    private function seedDemoReplies($tickets)
    {
        $bodies = json_decode($this->fs->get(database_path('seeds/demo/demo-reply-bodies.json')), true);
        $agent  = $this->user->where('email', 'agent@demo.com')->first();

        $replies = $tickets->map(function (Ticket $ticket) use ($bodies, $agent) {
            return collect($bodies)->random(10)->map(function ($body, $index) use ($ticket, $agent) {
                $isEven = $index % 2 == 0;
                $date = Carbon::now()->addHours(rand(-1, -24));

                return [
                    'body' => $body,
                    'user_id' => ($isEven ? $agent->id : $ticket->user_id),
                    'ticket_id' => $ticket->id,
                    'created_at' => $date,
                    'updated_at' => $date,
                ];
            });
        })->flatten(1)->toArray();

        $this->reply->insert($replies);
    }
}

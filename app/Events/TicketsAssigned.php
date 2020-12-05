<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Collection;

class TicketsAssigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Collection
     */
    public $originalTickets;

    /**
     * @var Collection
     */
    public $updatedTickets;

    /**
     * @param Collection $originalTickets
     * @param Collection $updatedTickets
     */
    public function __construct(Collection $originalTickets, Collection $updatedTickets)
    {
        $this->originalTickets = $originalTickets;
        $this->updatedTickets = $updatedTickets;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}

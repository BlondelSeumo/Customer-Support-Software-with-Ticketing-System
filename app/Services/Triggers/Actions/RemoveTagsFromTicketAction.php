<?php namespace App\Services\Triggers\Actions;

use App\Action;
use App\Ticket;
use App\Services\TagRepository;

class RemoveTagsFromTicketAction implements TriggerActionInterface {

    /**
     * TagRepository service instance.
     *
     * @var TagRepository
     */
    private $tagRepository;

    /**
     * AddTagsToTicketAction constructor.
     *
     * @param TagRepository $tagRepository
     */
    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * Perform specified action on ticket.
     *
     * @param Ticket $ticket
     * @param Action $action
     * @param \App\Trigger $trigger
     * @return Ticket
     */
    public function perform(Ticket $ticket, Action $action, \App\Trigger $trigger)
    {
        $tags = json_decode($action->pivot['action_value'])->tags_to_remove;
        $tags = explode(',', $tags);

        $tags = $this->tagRepository->findByName($tags);

        $this->tagRepository->detachById($tags->pluck('id')->toArray(), $ticket);

        //'unload' tags relationship in case it was already loaded
        //on passed in ticket so removed tags are properly removed
        //the next time tags relationship is accessed on this ticket
        unset($ticket->tags);

        return $ticket;
    }
}

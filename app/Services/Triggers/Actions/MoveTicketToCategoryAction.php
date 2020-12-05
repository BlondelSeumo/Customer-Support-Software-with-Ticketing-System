<?php namespace App\Services\Triggers\Actions;

use App\Action;
use App\Services\TagRepository;
use App\Ticket;
use App\Services\Ticketing\TicketRepository;

class MoveTicketToCategoryAction implements TriggerActionInterface {

    /**
     * TicketRepository service instance.
     *
     * @var TicketRepository
     */
    private $ticketRepository;

    /**
     * TagRepository service instance.
     *
     * @var TagRepository
     */
    private $tagRepository;

    /**
     * CopyTicketToFolderAction constructor.
     *
     * @param TicketRepository $ticketRepository
     * @param TagRepository    $tagRepository
     */
    public function __construct(TicketRepository $ticketRepository, TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
        $this->ticketRepository = $ticketRepository;
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
        $categoryName = json_decode($action['pivot']['action_value'])->category_name;

        $category = $this->tagRepository->updateOrCreate(['name' => $categoryName, 'type' => 'category']);

        $this->ticketRepository->addTagToTickets([$ticket->id], $category['id']);

        //'unload' tags relationship in case it was already loaded
        //on passed in ticket so removed tags are properly removed
        //the next time tags relationship is accessed on this ticket
        unset($ticket->tags);

        return $ticket;
    }
}

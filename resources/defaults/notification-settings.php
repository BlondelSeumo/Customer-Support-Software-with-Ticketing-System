<?php

use App\Notifications\Ticketing\Assigned\TicketAssignedNotif;
use App\Notifications\Ticketing\Assigned\TicketAssignedNotMeNotif;
use App\Notifications\Ticketing\Replies\Agent\AgentRepliedToMyTicketNotif;
use App\Notifications\Ticketing\Replies\Agent\AgentRepliedToSomeoneElseTicketNotif;
use App\Notifications\Ticketing\Replies\Agent\AgentRepliedToUnassignedTicketNotif;
use App\Notifications\Ticketing\Replies\Customer\CustomerRepliedToMyTicketNotif;
use App\Notifications\Ticketing\Replies\Customer\CustomerRepliedToSomeoneElseTicketNotif;
use App\Notifications\Ticketing\Replies\Customer\CustomerRepliedToUnassignedTicketNotif;
use App\Notifications\Ticketing\TicketCreatedNotif;

return [
    'available_channels' => ['email', 'browser', 'slack'],
    'subscriptions' => [
        [
            'group_name' => 'Notify me when…',
            'subscriptions' => [
                ['name' => 'There is a new conversation', 'notif_id' => TicketCreatedNotif::NOTIF_ID],
                ['name' => 'A conversation is assigned to me', 'notif_id' => TicketAssignedNotif::NOTIF_ID],
                ['name' => 'A conversation is assigned to someone else', 'notif_id' => TicketAssignedNotMeNotif::NOTIF_ID],
            ]
        ],
        [
            'group_name' => 'Notify me when a customer replies…',
            'subscriptions' => [
                ['name' => 'To an unassigned conversation', 'notif_id' => CustomerRepliedToUnassignedTicketNotif::NOTIF_ID],
                ['name' => 'To one of my conversations', 'notif_id' => CustomerRepliedToMyTicketNotif::NOTIF_ID],
                ['name' => 'To a conversation owned by someone else', 'notif_id' => CustomerRepliedToSomeoneElseTicketNotif::NOTIF_ID],
            ]
        ],
        [
            'group_name' => 'Notify me when agent replies or adds a note…',
            'subscriptions' => [
                ['name' => 'To an unassigned conversation', 'notif_id' => AgentRepliedToUnassignedTicketNotif::NOTIF_ID],
                ['name' => 'To one of my conversations', 'notif_id' => AgentRepliedToMyTicketNotif::NOTIF_ID],
                ['name' => 'To a conversation owned by someone else', 'notif_id' => AgentRepliedToSomeoneElseTicketNotif::NOTIF_ID],
            ]
        ],
    ]
];

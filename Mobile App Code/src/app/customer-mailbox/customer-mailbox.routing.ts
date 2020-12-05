import {RouterModule} from '@angular/router';
import {CustomerTicketsListComponent} from './customer-tickets-list/customer-tickets-list.component';
import {NewTicketComponent} from '../ticketing/new-ticket/new-ticket.component';
import {CustomerMailboxComponent} from './customer-mailbox.component';
import {CustomerConversationComponent} from './customer-conversation/customer-conversation.component';
import {TicketResolve} from '../conversation/conversation-resolve.service';
import {NewTicketCategoriesResolve} from './new-ticket-categories-resolve';
import {AuthGuard} from '@common/guards/auth-guard.service';

export const routing = RouterModule.forChild([
    {path: '', component: CustomerMailboxComponent, canActivate: [AuthGuard], children: [
        {
            path: '',
            component: CustomerTicketsListComponent
        },
        {
            path: 'new',
            component: NewTicketComponent,
            resolve: {categories: NewTicketCategoriesResolve},
            data: {permissions: ['tickets.create', 'tags.view']}
        },
        {
            path: ':id',
            component: CustomerConversationComponent,
            resolve: {ticket: TicketResolve},
        },
    ]},
]);


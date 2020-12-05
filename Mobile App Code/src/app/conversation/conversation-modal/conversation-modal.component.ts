import {Component, Inject, ViewEncapsulation} from '@angular/core';
import {Conversation} from '../conversation.service';
import {TicketsService} from '../../ticketing/tickets.service';
import {Draft} from '../draft.service';
import {ConversationReplies} from '../conversation-replies.service';
import {AfterReplyAction} from '../after-reply-action.service';
import {MailboxTagsService} from '../../ticketing/mailbox-tags.service';
import {Modal} from '@common/core/ui/dialogs/modal.service';
import {ConfirmModalComponent} from '@common/core/ui/confirm-modal/confirm-modal.component';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import {Ticket} from '../../shared/models/Ticket';
import {Router} from '@angular/router';

interface ConversationModalData {
    ticketId: number;
    activeTicketId?: number;
}

@Component({
    selector: 'conversation-modal',
    templateUrl: './conversation-modal.component.html',
    styleUrls: ['./conversation-modal.component.scss'],
    providers: [TicketsService, Conversation, Draft, ConversationReplies, AfterReplyAction],
    encapsulation: ViewEncapsulation.None,
})
export class ConversationModalComponent {

    /**
     * ID of ticket that is currently active behind this modal, if any.
     */
    public activeTicketId: number;

    constructor(
        private dialogRef: MatDialogRef<ConversationModalComponent>,
        @Inject(MAT_DIALOG_DATA) public data: ConversationModalData,
        private tickets: TicketsService,
        private modal: Modal,
        public conversation: Conversation,
        private tags: MailboxTagsService,
        private router: Router,
    ) {
        this.hydrate();
    }

    public close(ticket?: Ticket) {
        this.dialogRef.close(ticket);
    }

    public openTicket() {
        this.modal.closeAll();
        this.router.navigate(
            ['/mailbox/tickets', this.conversation.get().id],
            {queryParams: {tagId: this.conversation.getStatus().id}}
        );
    }

    /**
     * Merge currently active ticket with ticket displayed in this modal.
     */
    public mergeTickets() {
        this.modal.show(ConfirmModalComponent, {
            title: 'Merge Tickets',
            body: 'Are you sure you want merge this ticket with the original one behind the pop-ups?',
            bodyBold: 'Merged tickets can not be unmerged.',
            ok: 'Merge'
        }).afterClosed().subscribe(confirmed => {
            if ( ! confirmed) return;
            this.tickets.merge(this.activeTicketId, this.conversation.get().id).subscribe(ticket => {
                this.close(ticket);
                this.tags.refresh();
            });
        });
    }

    private hydrate() {
        this.activeTicketId = this.data.activeTicketId;

        this.tickets.get(this.data.ticketId).subscribe(response => {
            this.conversation.init(response.ticket);
        });
    }
}

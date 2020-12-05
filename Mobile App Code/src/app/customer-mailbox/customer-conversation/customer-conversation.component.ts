import {AfterViewInit, Component, ElementRef, OnInit, ViewChild} from '@angular/core';
import {TicketAttachmentsService} from '../../ticketing/ticket-attachments.service';
import {ActivatedRoute} from '@angular/router';
import {Conversation} from '../../conversation/conversation.service';
import {UploadsApiService} from '@common/uploads/uploads-api.service';
import {CurrentUser} from '@common/auth/current-user';
import {TicketsService} from '../../ticketing/tickets.service';
import {MailboxTagsService} from '../../ticketing/mailbox-tags.service';

@Component({
    selector: 'customer-conversation',
    templateUrl: './customer-conversation.component.html',
    styleUrls: ['./customer-conversation.component.scss'],
    providers: [TicketAttachmentsService],
})
export class CustomerConversationComponent implements OnInit, AfterViewInit {
    @ViewChild('scrollContainer') scrollContainer: ElementRef<HTMLElement>;
    constructor(
        private route: ActivatedRoute,
        public uploads: UploadsApiService,
        public currentUser: CurrentUser,
        public conversation: Conversation,
        private tickets: TicketsService,
        private tags: MailboxTagsService,
    ) {}

    ngAfterViewInit() {
        this.conversation.scrollContainer = this.scrollContainer.nativeElement;
    }

    ngOnInit() {
        this.route.data.subscribe(data => {
            this.conversation.init(data.ticket);
        });
    }

    public markAsSolved() {
        const status = this.tags.getTagByIdOrName('closed');
        this.tickets.changeTicketStatus(this.conversation.get().id, status.name).subscribe(() => {
            this.conversation.afterReplyAction.navigateToCustomerTicketsList('Ticket marked as solved.');
            this.conversation.setStatus(status);
        });
    }
}

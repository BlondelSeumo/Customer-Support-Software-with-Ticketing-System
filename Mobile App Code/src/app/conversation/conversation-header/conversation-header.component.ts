import {Component, ViewEncapsulation} from '@angular/core';
import {Conversation} from '../conversation.service';
import {TicketsService} from '../../ticketing/tickets.service';
import {MailboxTagsService} from '../../ticketing/mailbox-tags.service';
import {CurrentUser} from '@common/auth/current-user';
import {Tag} from '@common/core/types/models/Tag';

@Component({
    selector: 'conversation-header',
    templateUrl: './conversation-header.component.html',
    styleUrls: ['./conversation-header.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class ConversationHeaderComponent {
    constructor(
        private tickets: TicketsService,
        public currentUser: CurrentUser,
        private mailboxTags: MailboxTagsService,
        public conversation: Conversation,
    ) {}

    public removeTag(tagToRemove: Tag) {
        this.tickets.removeTag(tagToRemove, [this.conversation.get().id]).subscribe(() => {
            this.conversation.get().tags = this.conversation.get().tags.filter(tag => tag.id !== tagToRemove.id);
            this.mailboxTags.refresh();
        });
    }
}

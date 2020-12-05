import {Component, OnInit, ViewEncapsulation, Input, AfterViewInit, OnDestroy} from '@angular/core';
import {Conversation} from '../conversation.service';
import {ConversationTextEditorComponent} from '../conversation-text-editor/conversation-text-editor.component';
import {ShowOriginalReplyModalComponent} from './show-original-reply-modal/show-original-reply-modal.component';
import {Reply} from '../../shared/models/Reply';
import {TicketsService} from '../../ticketing/tickets.service';
import {UpdateReplyModalComponent} from '../../ticketing/update-reply-modal/update-reply-modal.component';
import {EchoService} from '../../shared/broadcasting/echo.service';
import {ConfirmReplyDeleteModalComponent} from '../confirm-reply-delete-modal/confirm-reply-delete-modal.component';
import {CurrentUser} from '@common/auth/current-user';
import {Modal} from '@common/core/ui/dialogs/modal.service';

import * as Prism from 'prismjs';
import 'prismjs/components/prism-markup';
import 'prismjs/components/prism-markup-templating';
import 'prismjs/components/prism-php';
import 'prismjs/components/prism-typescript';
import 'prismjs/components/prism-javascript';
import {FileEntry} from '@common/uploads/types/file-entry';
import {Subscription} from 'rxjs';

@Component({
    selector: 'conversation-replies',
    templateUrl: './conversation-replies.component.html',
    styleUrls: ['./conversation-replies.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class ConversationRepliesComponent implements OnInit, AfterViewInit, OnDestroy {
    @Input() textEditor: ConversationTextEditorComponent;
    public subscription: Subscription;

    constructor(
        private modal: Modal,
        public currentUser: CurrentUser,
        private tickets: TicketsService,
        public conversation: Conversation,
        private backendEvents: EchoService,
    ) {}

    ngOnInit() {
        // make sure only replies to this ticket are added via backend events
        this.subscription = this.backendEvents.ticketReplyCreated.subscribe((reply: Reply) => {
            if (this.conversation.get().id !== reply.ticket_id) return;
            reply['animated'] = true;
            this.conversation.replies.add(reply);
        });
    }

    ngOnDestroy() {
        this.subscription && this.subscription.unsubscribe();
    }

    ngAfterViewInit() {
        if (Prism) Prism.highlightAll();
    }

    /**
     * Update existing reply.
     */
    public update(reply: Reply) {
        this.modal.open(UpdateReplyModalComponent, {reply}, {panelClass: 'update-reply-modal-container'})
            .afterClosed()
            .subscribe((newReply: Reply) => {
                if ( ! newReply) return;
                this.conversation.replies.replace(newReply);
            });
    }

    /**
     * Delete specified reply if user confirms this action.
     */
    public maybeDeleteReply(reply: Reply) {
        this.modal.show(ConfirmReplyDeleteModalComponent, {reply})
            .afterClosed()
            .subscribe(confirmed => {
                if ( ! confirmed) return;
                this.conversation.replies.delete(reply);
            });
    }

    /**
     * Open specified draft in text editor.
     */
    public editDraft(draft: Reply) {
        this.conversation.setDraft(draft);
        this.conversation.openEditor();
        this.conversation.replies.remove(draft.id);
    }

    /**
     * Show original email that this reply was created from.
     * (if it was created from email)
     */
    public showOriginalEmail(reply: Reply) {
        const className = 'show-original-reply-modal-container';
        this.tickets.getOriginalEmailForReply(reply.id).subscribe(response => {
            this.modal.open(ShowOriginalReplyModalComponent, {original: response.data}, {panelClass: className});
        }, () => {
            this.modal.open(ShowOriginalReplyModalComponent, null, {panelClass: className});
        });
    }

    public removeAttachment(reply: Reply, entry: FileEntry) {
        reply.uploads = reply.uploads.filter(u => u.id !== entry.id);
        this.tickets.updateReply(reply.id, {uploads: reply.uploads.map(u => u.id)})
            .subscribe(() => {}, () => {});
    }
}

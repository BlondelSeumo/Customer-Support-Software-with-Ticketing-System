import {Component, OnDestroy, OnInit, ViewChild, ViewEncapsulation} from '@angular/core';
import {Conversation} from '../conversation.service';
import {MailboxTagsService} from '../../ticketing/mailbox-tags.service';
import {ConfirmReplyDeleteModalComponent} from '../confirm-reply-delete-modal/confirm-reply-delete-modal.component';
import {TextEditorComponent} from '@common/text-editor/text-editor.component';
import {Subscription} from 'rxjs';
import {CurrentUser} from '@common/auth/current-user';
import {BrowserEvents} from '@common/core/services/browser-events.service';
import {Modal} from '@common/core/ui/dialogs/modal.service';
import {CannedReply} from '../../shared/models/CannedReply';
import {DraftPayload} from '../../ticketing/tickets.service';
import {Article} from '../../shared/models/Article';
import {HcUrls} from '../../help-center/shared/hc-urls.service';
import {Settings} from '@common/core/config/settings.service';
import {SuggestedArticlesDropdownComponent} from '../../help-center/suggested-articles-dropdown/suggested-articles-dropdown.component';
import {R, SLASH} from '@angular/cdk/keycodes';
import {Tag} from '../../shared/models/Tag';
import {TinymceTextEditor} from '@common/text-editor/editors/tinymce-text-editor.service';
import {LocalStorage} from '@common/core/services/local-storage.service';
import {filter, take} from 'rxjs/operators';

@Component({
    selector: 'conversation-text-editor',
    templateUrl: './conversation-text-editor.component.html',
    styleUrls: ['./conversation-text-editor.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class ConversationTextEditorComponent implements OnInit, OnDestroy {
    @ViewChild('textEditor', { static: true }) textEditor: TextEditorComponent;
    @ViewChild(SuggestedArticlesDropdownComponent, { static: false }) articleDropdown: SuggestedArticlesDropdownComponent;

    public selectedStatus = new Tag();
    private subscriptions: Subscription[] = [];

    constructor(
        private modal: Modal,
        public currentUser: CurrentUser,
        public conversation: Conversation,
        private browserEvents: BrowserEvents,
        public mailboxTags: MailboxTagsService,
        private hcUrls: HcUrls,
        private settings: Settings,
        private localStorage: LocalStorage,
    ) {}

    ngOnInit() {
        this.mailboxTags.allTags$.pipe(filter(t => !!t.length), take(1)).subscribe(() => {
            this.setSelectedStatus();
        });
        this.initKeybinds();
        this.conversation.setEditor(this.textEditor);
    }

    public submitReply() {
        const payload = {body: this.textEditor.getContents(), status: this.selectedStatus};
        this.conversation.submitReply(payload).subscribe(() => {
            this.closeEditor();
        }, () => {});
    }

    public saveDraft(params: DraftPayload = {}) {
        return this.conversation.draft.save(params);
    }

    public closeEditor() {
        this.conversation.closeEditor();
        this.conversation.draft.reset();
        this.textEditor.reset();
    }

    public saveDraftAndAddToReplies() {
        if ( ! this.conversation.draft.isEmpty()) {
            this.saveDraft().subscribe(response => {
                this.conversation.replies.add(response.data);
                this.closeEditor();
            });
        } else {
            this.closeEditor();
        }
    }

    public maybeDeleteDraft() {
        // TODO: refactor to use async/wait and remove duplicated stuff
        const draft = this.conversation.draft.get();

        if (draft.id) {
            this.modal.show(ConfirmReplyDeleteModalComponent, {reply: draft}).afterClosed()
                .subscribe(confirmed => {
                    if ( ! confirmed) return;
                    this.conversation.draft.delete();
                    this.closeEditor();
                });
        } else {
            this.closeEditor();
        }
    }

    public applyCannedReply(reply: CannedReply) {
        this.textEditor.insertContents(reply.body);
        setTimeout(() => {
            this.saveDraft({uploads: reply.uploads.map(upload => upload.id)});
        });
    }

    /**
     * Set the status that should be applied to ticket after submitting reply.
     */
    public setSelectedStatus(tag?: Tag) {
        if (tag) {
            this.localStorage.set('default_status_tag', tag.name);
        } else {
            const storedTagName = this.localStorage.get('default_status_tag');
            if (storedTagName) {
                tag = this.mailboxTags.getTagByIdOrName(storedTagName);
            // if current user is customer, open ticket when they reply,
            // otherwise set default tag to "closed"
            } else if ( ! this.currentUser.hasPermission('tickets.update')) {
                tag = this.mailboxTags.getTagByIdOrName('open');
            } else {
                tag = this.mailboxTags.getTagByIdOrName('closed');
            }
        }
        this.selectedStatus = tag;
    }

    private initKeybinds() {
        const sub = this.browserEvents.globalKeyDown$.subscribe(e => {

            // if any modals are open or user is currently typing, bail
            if (this.modal.anyDialogOpen() || BrowserEvents.userIsTyping()) return;

            // open text editor
            if (e.keyCode === R) {
                this.conversation.openEditor();
                this.conversation.scrollContainer.scrollTo({top: 0, behavior: 'smooth'});
            }

            if (e.keyCode === SLASH && e.ctrlKey) {
                this.articleDropdown.focus();
                this.conversation.scrollContainer.scrollTo({top: 0, behavior: 'smooth'});
            }
        });
        this.subscriptions.push(sub);

        (this.textEditor.editor as TinymceTextEditor).waitForEditor().then((editor: any) => {
            editor.shortcuts.add('ctrl+191', 'Search docs', () => {
                this.articleDropdown.focus();
                this.conversation.scrollContainer.scrollTo({top: 0, behavior: 'smooth'});
            });
        });
    }

    ngOnDestroy() {
        this.saveDraft();
        this.closeEditor();

        this.subscriptions.forEach(subscription => {
            subscription && subscription.unsubscribe();
        });
    }

    public insertArticleLink(article: Article) {
        const uri = this.hcUrls.article(article).join('/');
        this.textEditor.insertLink({
            target: 'blank',
            href: this.settings.getBaseUrl() + uri,
            text: article.title
        });
    }
}

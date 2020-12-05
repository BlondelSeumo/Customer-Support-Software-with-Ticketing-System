import {Component, Input, OnInit, Output, EventEmitter, ViewEncapsulation, ViewChild, ElementRef} from '@angular/core';
import {FormControl} from '@angular/forms';
import {Conversation} from '../../../conversation/conversation.service';
import {CannedRepliesService} from '../canned-replies.service';
import {CannedReply} from '../../../shared/models/CannedReply';
import {CrupdateCannedReplyModalComponent} from '../crupdate-canned-reply-modal/crupdate-canned-reply-modal.component';
import {CurrentUser} from '@common/auth/current-user';
import {catchError, debounceTime, distinctUntilChanged, switchMap, tap} from 'rxjs/operators';
import { MatMenuTrigger } from '@angular/material/menu';
import {Modal} from '@common/core/ui/dialogs/modal.service';
import * as Dot from 'dot-object';
import {Toast} from '@common/core/ui/toast.service';
import {of} from 'rxjs';
import {EMPTY_PAGINATION_RESPONSE} from '@common/core/types/pagination/pagination-response';

@Component({
    selector: 'canned-replies-dropdown',
    templateUrl: './canned-replies-dropdown.component.html',
    styleUrls: ['./canned-replies-dropdown.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class CannedRepliesDropdownComponent implements OnInit {
    @ViewChild(MatMenuTrigger, { static: true }) trigger: MatMenuTrigger;
    @ViewChild('input', { static: true }) input: ElementRef;

    @Input() searchQuery = new FormControl();
    @Output() onReplySelect = new EventEmitter();

    public loadedResultsAtLeastOnce = false;
    public cannedReplies: CannedReply[] = [];

    constructor(
        private cannedRepliesService: CannedRepliesService,
        private currentUser: CurrentUser,
        private modal: Modal,
        private conversation: Conversation,
        private toast: Toast,
    ) {}

    ngOnInit() {
        this.searchQuery.valueChanges
            .pipe(
                debounceTime(250),
                distinctUntilChanged(),
                switchMap(query => this.getCannedReplies(query)),
                catchError(() => of(EMPTY_PAGINATION_RESPONSE)),
            ).subscribe();

        this.trigger.menuOpened
            .subscribe(() => {
                if ( ! this.loadedResultsAtLeastOnce) {
                    this.getCannedReplies(null, true).subscribe();
                }
            });
    }

    public selectCannedReply(cannedReply: CannedReply) {
        // replace placeholders
        const body = cannedReply.body.replace(/{{([\w.\-]+?)}}/, (fullMatch, groupMatch) => {
            const replacement = Dot.pick(groupMatch.toLowerCase(), {ticket: this.conversation.get()});
            return replacement || fullMatch;
        });
        this.onReplySelect.emit({...cannedReply, body});
    }

    public showNewCannedReplyModal() {
        const cannedReply =  new CannedReply({
            body: this.conversation.draft.get().body,
            uploads: this.conversation.draft.get().uploads,
        });
        this.modal.open(
            CrupdateCannedReplyModalComponent,
            {cannedReply},
            {panelClass: 'crupdate-canned-reply-modal-container'}
        ).afterClosed().subscribe(newReply => {
            if ( ! newReply) return;
            this.toast.open('Canned reply created.');
            this.cannedReplies.unshift(newReply);
        });
    }

    public getCannedReplies(query: string, force?: boolean) {
        if ( ! query && ! force) return of(EMPTY_PAGINATION_RESPONSE);
        const payload = {query, user_id: this.currentUser.get('id'), perPage: 20, shared: true};
        return this.cannedRepliesService.getReplies(payload)
            .pipe(tap(response => {
                this.loadedResultsAtLeastOnce = true;
                this.cannedReplies = response.pagination.data;
            }));
    }
}

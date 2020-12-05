import {Component, OnInit, ViewEncapsulation, ChangeDetectionStrategy} from '@angular/core';
import {FormBuilder} from '@angular/forms';
import {Settings} from '@common/core/config/settings.service';
import {BehaviorSubject, Observable, of} from 'rxjs';
import {UploadedFile} from '@common/uploads/uploaded-file';
import {UploadQueueService} from '@common/uploads/upload-queue/upload-queue.service';
import {TicketsService} from '../tickets.service';
import {catchError, debounceTime, distinctUntilChanged, finalize, switchMap} from 'rxjs/operators';
import {User} from '@common/core/types/models/User';
import {Users} from '@common/auth/users.service';
import {Toast} from '@common/core/ui/toast.service';
import {ActivatedRoute, Router} from '@angular/router';
import {Tag} from '../../shared/models/Tag';
import {MailboxTagsService} from '../mailbox-tags.service';
import {FileEntry} from '@common/uploads/types/file-entry';
import {BackendErrorResponse} from '@common/core/types/backend-error-response';

interface TicketErrors {
    category?: string;
    subject?: string;
    body?: string;
    user_id?: string;
    status?: string;
}

@Component({
    selector: 'create-ticket',
    templateUrl: './create-ticket.component.html',
    styleUrls: ['./create-ticket.component.scss'],
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [TicketsService, UploadQueueService],
})
export class CreateTicketComponent implements OnInit {
    public errors$ = new BehaviorSubject<TicketErrors>({});
    public attachments$ = new BehaviorSubject<FileEntry[]>([]);
    public users$ = new BehaviorSubject<User[]>([]);
    public loading$ = new BehaviorSubject<boolean>(false);
    public ticketCategories: Tag[] = [];

    public ticketForm = this.fb.group({
        customer: [''],
        subject: [''],
        category: [''],
        body: [''],
        status: [''],
    });

    constructor(
        private fb: FormBuilder,
        public settings: Settings,
        private uploadQueue: UploadQueueService,
        private users: Users,
        private tickets: TicketsService,
        private toast: Toast,
        private router: Router,
        private route: ActivatedRoute,
        public mailboxTags: MailboxTagsService,
    ) {}

    ngOnInit() {
        this.bindToCustomerControl();
        this.ticketForm.patchValue({status: this.mailboxTags.statusTags[0].name});
        this.route.data.subscribe(data => {
            this.ticketCategories = data.categories;
            if (data.categories.length) {
                this.ticketForm.patchValue({category: this.ticketCategories[0].id});
            }
        });
    }

    public createTicket() {
        this.loading$.next(true);
        this.tickets.create(this.getPayload())
            .pipe(finalize(() => this.loading$.next(false)))
            .subscribe(() => {
                this.toast.open('Ticket created');
                this.mailboxTags.refresh();
                this.router.navigateByUrl('/mailbox/tickets');
            }, (errResponse: BackendErrorResponse) => this.errors$.next(errResponse.errors));
    }

    private getPayload() {
        const payload = this.ticketForm.value;
        payload.user_id = payload.customer.id;
        payload.uploads = this.uploadQueue.getAllCompleted().map(entry => entry.id);
        return payload;
    }

    public setBody(value: string) {
        this.ticketForm.patchValue({body: value});
    }

    public uploadFiles(files: UploadedFile[]) {
        this.uploadQueue.start(files).subscribe(response => {
            this.attachments$.next([...this.attachments$.value, response.fileEntry]);
        });
    }

    public removeAttachment(entry: FileEntry) {
        const newAttachments = this.attachments$.value.slice();
        for (let i = 0; i < newAttachments.length; i++) {
            if (newAttachments[i].id === entry.id) {
                newAttachments.splice(i, 1);
            }
        }
        this.attachments$.next(newAttachments);
    }

    private bindToCustomerControl() {
        this.ticketForm.get('customer').valueChanges
            .pipe(
                debounceTime(250),
                distinctUntilChanged(),
                switchMap(query => this.searchUsers(query)),
                catchError(() => of([])),
            ).subscribe(users => {
                this.users$.next(users);
            });
    }

    private searchUsers(query: string|User): Observable<User[]> {
        if ( ! query || typeof query !== 'string') {
            return of([]);
        }
        return this.users.getAll({query});
    }

    public displayUserFn(user?: User): string | undefined {
        return user ? user.email : undefined;
    }
}

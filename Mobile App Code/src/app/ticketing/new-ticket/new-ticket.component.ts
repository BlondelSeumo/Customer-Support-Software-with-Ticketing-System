import {ChangeDetectionStrategy, Component, OnDestroy, OnInit, ViewChild, ViewEncapsulation} from '@angular/core';
import {ActivatedRoute, Router} from '@angular/router';
import {TicketsService} from '../tickets.service';
import {HcUrls} from '../../help-center/shared/hc-urls.service';
import {TextEditorComponent} from '@common/text-editor/text-editor.component';
import {Settings} from '@common/core/config/settings.service';
import {UploadsApiService} from '@common/uploads/uploads-api.service';
import {Toast} from '@common/core/ui/toast.service';
import {UploadQueueService} from '@common/uploads/upload-queue/upload-queue.service';
import {UploadedFile} from '@common/uploads/uploaded-file';
import {BehaviorSubject, ReplaySubject} from 'rxjs';
import {finalize} from 'rxjs/operators';
import {Tag} from '../../shared/models/Tag';
import {SearchTermLoggerService} from '../../help-center/front/search-term-logger.service';
import {FileEntry} from '@common/uploads/types/file-entry';
import {NewTicketPageContent} from '../../admin/appearance/new-ticket-appearance-panel/new-ticket-page-content';
import {BackendErrorResponse} from '@common/core/types/backend-error-response';

interface TicketErrors {
    category?: string;
    purchase_code?: string;
    subject?: string;
    body?: string;
}

@Component({
    selector: 'new-ticket',
    templateUrl: './new-ticket.component.html',
    styleUrls: ['./new-ticket.component.scss'],
    providers: [TicketsService, UploadQueueService],
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush,
    host: {id: 'new-ticket'}
})
export class NewTicketComponent implements OnInit, OnDestroy {
    @ViewChild(TextEditorComponent, { static: false }) textEditor: TextEditorComponent;

    public errors$ = new BehaviorSubject<TicketErrors>({});
    public loading$ = new BehaviorSubject<boolean>(false);
    public attachments$ = new BehaviorSubject<FileEntry[]>([]);

    public ticketCategories: Tag[] = [];
    public ticketModel: {category?: Tag, body?: string, subject?: string} = {};
    public content$ = new ReplaySubject<NewTicketPageContent>(1);

    constructor(
        public settings: Settings,
        private tickets: TicketsService,
        private uploads: UploadsApiService,
        private router: Router,
        private urls: HcUrls,
        private route: ActivatedRoute,
        private toast: Toast,
        private uploadQueue: UploadQueueService,
        private searchLogger: SearchTermLoggerService,
    ) {
        this.settings.all$().subscribe(() => {
            this.content$.next(this.settings.getJson('hc.newTicket.appearance', {}));
        });
    }

    ngOnInit() {
        this.route.data.subscribe(data => {
            this.ticketCategories = data.categories;
            this.ticketModel.category = this.ticketCategories[0];
        });
    }

    ngOnDestroy() {
        this.textEditor.destroyEditor();
    }

    public createTicket() {
        this.loading$.next(true);

        const payload = {
            subject: this.ticketModel.subject,
            body: this.ticketModel.body,
            category: this.ticketModel.category && this.ticketModel.category.id,
            uploads: this.uploadQueue.getAllCompleted().map(entry => entry.id)
        };

        this.tickets.create(payload)
            .pipe(finalize(() => {
                this.loading$.next(false);
            }))
            .subscribe(() => {
                this.searchLogger.updateSessionAndStore({createdTicket: true});
                this.router.navigate(this.urls.customerTicketList());
                this.toast.open('Your request was successfully submitted.');
            }, (errResponse: BackendErrorResponse) => {
                this.errors$.next(errResponse.errors);
            });
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

    public getArticleCategories(): number[] {
        return this.ticketModel.category ? this.ticketModel.category.categories.map(cat => cat.id) : [];
    }
}

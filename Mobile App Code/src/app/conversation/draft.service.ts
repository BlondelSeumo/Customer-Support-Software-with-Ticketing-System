import {Injectable} from '@angular/core';
import {TicketAttachmentsService} from '../ticketing/ticket-attachments.service';
import {Reply} from '../shared/models/Reply';
import {DraftPayload, TicketsService} from '../ticketing/tickets.service';
import {Observable, of} from 'rxjs';
import {catchError, last, scan, share} from 'rxjs/operators';
import {UploadQueueService} from '@common/uploads/upload-queue/upload-queue.service';
import {UploadedFile} from '@common/uploads/uploaded-file';
import {Toast} from '@common/core/ui/toast.service';
import {FileEntry} from '@common/uploads/types/file-entry';
import {UploadFileResponse} from '@common/uploads/uploads-api.service';

@Injectable({
    providedIn: 'root'
})
export class Draft {
    private model: Reply;
    private ticketId: number;

    constructor(
        private ticketUploads: TicketAttachmentsService,
        private tickets: TicketsService,
        private uploadQueue: UploadQueueService,
        private toast: Toast,
    ) {
        this.reset();
    }

    public get() {
        return this.model;
    }

    public set(draft: Reply) {
        this.model = draft;
    }

    public setBody(contents: string) {
        this.model.body = contents;
    }

    public setTicketId(id: number) {
        this.ticketId = id;
    }

    public isEmpty(): boolean {
        return this.model.body.length === 0 && this.model.uploads.length === 0;
    }

    public uploadFiles(files: UploadedFile[]) {
        // TODO: refactor
        this.uploadQueue.start(files)
            .pipe(
                scan((acc, value: UploadFileResponse) => [...acc, value.fileEntry], []),
                last(),
            )
            .subscribe(entries => {
                this.model.uploads = [...this.model.uploads, ...entries];
                this.save();
            });
    }

    public detachUpload(fileEntry: FileEntry) {
        this.ticketUploads.detach(this.model.id, fileEntry.id).subscribe(() => {
            const i = this.get().uploads.findIndex(upl => upl.id === fileEntry.id);
            this.get().uploads.splice(i, 1);
        });
    }

    public save(params: {body?: string} = {}): Observable<{data: Reply}> {
        if (this.isEmpty()) return;

        const request = this.tickets.saveDraft(
            this.ticketId,
            this.getPayload(params),
            this.model.id,
        ).pipe(
            catchError(() => of({data: this.get()})),
            share()
        );

        request.subscribe(response => {
            this.set(response['data']);
        });

        return request;
    }

    public delete() {
        if (this.model.id) {
            this.tickets.deleteDraft(this.model.id).subscribe();
        }

        this.reset();
    }

    public getPayload(params = {}): DraftPayload {
        return Object.assign({}, {
            body: this.model.body || null,
            uploads: this.model.uploads.map(upload => upload.id),
        }, params);
    }

    public reset() {
        this.set(new Reply({uploads: [], body: ''}));
    }
}

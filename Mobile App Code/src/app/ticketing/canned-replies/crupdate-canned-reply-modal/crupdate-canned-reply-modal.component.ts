import {Component, Inject, ViewChild, ViewEncapsulation} from '@angular/core';
import {CannedRepliesService} from '../canned-replies.service';
import {CannedReply} from '../../../shared/models/CannedReply';
import {TextEditorComponent} from '@common/text-editor/text-editor.component';
import {MAT_DIALOG_DATA, MatDialogRef} from '@angular/material/dialog';
import {UploadQueueService} from '@common/uploads/upload-queue/upload-queue.service';
import {UploadedFile} from '@common/uploads/uploaded-file';
import {of} from 'rxjs';
import {delay, take} from 'rxjs/operators';
import {FileEntry} from '@common/uploads/types/file-entry';
import {BackendErrorResponse} from '@common/core/types/backend-error-response';

interface CrupdateCannedReplyModalData {
    cannedReply?: CannedReply;
    contents?: {body?: string, uploads?: FileEntry[]};
}

interface CrupdateCannedReplyModalErrors {
    name?: string;
    body?: string;
    uploads?: string;
    shared?: string;
}

@Component({
    selector: 'crupdate-canned-reply-modal',
    templateUrl: './crupdate-canned-reply-modal.component.html',
    styleUrls: ['./crupdate-canned-reply-modal.component.scss'],
    providers: [UploadQueueService],
    encapsulation: ViewEncapsulation.None,
})
export class CrupdateCannedReplyModalComponent {
    @ViewChild('textEditor', { static: true }) textEditor: TextEditorComponent;

    public attachments: FileEntry[] = [];
    public model: CannedReply = new CannedReply();
    public errors: CrupdateCannedReplyModalErrors = {};

    constructor(
        private dialogRef: MatDialogRef<CrupdateCannedReplyModalComponent>,
        @Inject(MAT_DIALOG_DATA) public data: CrupdateCannedReplyModalData,
        private cannedReplies: CannedRepliesService,
        public uploadQueue: UploadQueueService,
    ) {
        of(null).pipe(delay(0), take(1))
            .subscribe(() => this.hydrate());
    }

    public close(cannedReply?: CannedReply) {
        this.dialogRef.close(cannedReply);
        this.textEditor.destroyEditor();
    }

    public confirm() {
        let request;
        const payload = this.getPayload();

        if (this.model.id) {
            request = this.cannedReplies.update(this.model.id, payload);
        } else {
            request = this.cannedReplies.create(payload);
        }

        request.subscribe(response => {
            this.close(response.data);
        }, (errResponse: BackendErrorResponse) => this.errors = errResponse.errors);
    }

    private getPayload(): Object {
        return {
            name: this.model.name,
            body: this.textEditor.getContents(),
            shared: this.model.shared,
            uploads: this.attachments.map(attachment => attachment.id),
        };
    }

    private setContents(contents: {body?: string, uploads?: FileEntry[]} = {}) {
        if (contents.body) {
            this.textEditor.setContents(contents.body);
        }

        if (contents.uploads) {
            this.attachments = contents.uploads.slice();
        }
    }

    private hydrate() {
        // init modal with specified body and uploads
        if (this.data.contents) {
            this.setContents(this.data.contents);
        }

        // init modal using existing canned reply
        if (this.data.cannedReply) {
            this.model = new CannedReply(this.data.cannedReply);
            this.setContents({body: this.model.body, uploads: this.model.uploads});
        }
    }

    public uploadFiles(files: UploadedFile[]) {
        this.uploadQueue.start(files).subscribe(response => {
            this.attachments = [...this.attachments, response.fileEntry];
        });
    }

    public removeAttachment(entry: FileEntry) {
        for (let i = 0; i < this.attachments.length; i++) {
            if (this.attachments[i].id === entry.id) {
                this.attachments.splice(i, 1);
            }
        }
    }
}

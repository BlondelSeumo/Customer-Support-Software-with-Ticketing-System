import {AfterViewInit, Component, Inject, ViewChild, ViewEncapsulation} from '@angular/core';
import {TicketsService} from '../tickets.service';
import {Reply} from '../../shared/models/Reply';
import {TextEditorComponent} from '@common/text-editor/text-editor.component';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import {UploadQueueService} from '@common/uploads/upload-queue/upload-queue.service';
import {UploadedFile} from '@common/uploads/uploaded-file';
import {FileEntry} from '@common/uploads/types/file-entry';
import {BackendErrorResponse} from '@common/core/types/backend-error-response';

interface UpdateReplyModalData {
    reply: Reply;
}

interface UpdateReplyModalErrors {
    body?: string;
    attachments?: string;
}

@Component({
    selector: 'update-reply-modal',
    templateUrl: './update-reply-modal.component.html',
    styleUrls: ['./update-reply-modal.component.scss'],
    providers: [UploadQueueService],
    encapsulation: ViewEncapsulation.None
})
export class UpdateReplyModalComponent implements AfterViewInit {
    @ViewChild(TextEditorComponent, { static: true }) textEditor: TextEditorComponent;

    private reply: Reply;
    public attachments: FileEntry[] = [];
    public errors: UpdateReplyModalErrors = {};

    constructor(
        private dialogRef: MatDialogRef<UpdateReplyModalComponent>,
        @Inject(MAT_DIALOG_DATA) public data: UpdateReplyModalData,
        private tickets: TicketsService,
        private uploadQueue: UploadQueueService,
    ) {
        this.hydrate();
    }

    ngAfterViewInit() {
        this.textEditor.focus();
        this.textEditor.setContents(this.reply.body);
    }

    private hydrate() {
        this.reply = Object.assign({}, this.data.reply);
        this.attachments = this.reply.uploads.slice();
    }

    public close(reply?: Reply) {
        this.dialogRef.close(reply);
        this.textEditor.destroyEditor();
    }

    public confirm() {
        const payload = {
            body: this.textEditor.getContents(),
            uploads: this.attachments.map(attachment => attachment.id)
        };

        this.tickets.updateReply(this.reply.id, payload).subscribe(response => {
            this.close(response.data);
        }, (errResponse: BackendErrorResponse) => this.errors = errResponse.errors);
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


import {ChangeDetectionStrategy, Component, EventEmitter, Input, Output, ViewEncapsulation} from '@angular/core';
import {FileMime} from '../file-mime.service';
import {OverlayPanel} from '@common/core/ui/overlay-panel/overlay-panel.service';
import {UploadQueueService} from '@common/uploads/upload-queue/upload-queue.service';
import {FilePreviewOverlayComponent} from '../file-preview-overlay/file-preview-overlay.component';
import {FileEntry} from '@common/uploads/types/file-entry';

@Component({
    selector: 'reply-attachment-list',
    templateUrl: './reply-attachment-list.component.html',
    styleUrls: ['./reply-attachment-list.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    encapsulation: ViewEncapsulation.None,
})
export class ReplyAttachmentListComponent {
    @Input() attachments: FileEntry[];
    @Input() cancelButtonVisible = true;

    @Output() detached: EventEmitter<FileEntry> = new EventEmitter();

    constructor(
        public mime: FileMime,
        private overlay: OverlayPanel,
        public uploadQueue: UploadQueueService,
    ) {
    }

    public detachEntry(entry: FileEntry) {
        const i = this.attachments.findIndex(ent => ent.id === entry.id);
        if (i > -1) {
            this.attachments.splice(i, 1);
            this.detached.emit(entry);
        }
    }

    public showPreviewOverlay(attachment: FileEntry) {
        this.overlay.open(FilePreviewOverlayComponent, {
                position: 'center',
                origin: 'global',
                data: {entries: [attachment], ticketEntry: true}
            },
        );
    }
}

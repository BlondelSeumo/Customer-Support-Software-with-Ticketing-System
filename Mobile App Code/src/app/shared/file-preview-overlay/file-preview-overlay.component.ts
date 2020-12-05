import {
    AfterViewInit,
    ChangeDetectionStrategy,
    Component,
    ElementRef,
    Inject,
    OnDestroy,
    OnInit,
    ViewChild,
} from '@angular/core';
import {Subscription} from 'rxjs';
import {OverlayPanelRef} from '@common/core/ui/overlay-panel/overlay-panel-ref';
import {PreviewFilesService} from '@common/file-preview/preview-files.service';
import {OVERLAY_PANEL_DATA} from '@common/core/ui/overlay-panel/overlay-panel-data';
import {Settings} from '@common/core/config/settings.service';
import {downloadFileFromUrl} from '@common/uploads/utils/download-file-from-url';
import {FileEntry} from '@common/uploads/types/file-entry';

export interface FilePreviewOverlayData {
    entries: FileEntry[];
    ticketEntry?: boolean;
}

@Component({
    selector: 'file-preview-overlay',
    templateUrl: './file-preview-overlay.component.html',
    styleUrls: ['./file-preview-overlay.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class FilePreviewOverlayComponent implements OnInit, AfterViewInit, OnDestroy {
    public entries: FileEntry[] = [];
    @ViewChild('previewContainer', { read: ElementRef, static: true }) previewContainer: ElementRef;
    @ViewChild('moreOptionsButton', { read: ElementRef, static: false }) optionsButton: ElementRef;
    private downloadSub: Subscription;

    constructor(
        private el: ElementRef,
        private overlayRef: OverlayPanelRef,
        private previewFiles: PreviewFilesService,
        private settings: Settings,
        @Inject(OVERLAY_PANEL_DATA) public data: FilePreviewOverlayData
    ) {
        this.entries = data.entries;
    }

    ngOnInit() {
        if (this.data.ticketEntry) {
            this.previewFiles.setPreviewUriTransformer(this.urlTransformer.bind(this));
        }
    }

    ngAfterViewInit() {
        this.bindToDownload();

        this.previewContainer.nativeElement.addEventListener('click', e => {
            if ( ! e.target.closest('.preview-object')) {
                this.overlayRef.close();
            }
        });
    }

    ngOnDestroy() {
        this.downloadSub.unsubscribe();
    }

    public closeOverlay() {
        this.overlayRef.close();
    }

    private bindToDownload() {
        this.downloadSub = this.previewFiles.download.subscribe(() => {
            const entries = this.previewFiles.getAllEntries();
            this.downloadFile(entries);
        });
    }

    private downloadFile(entries: FileEntry[]) {
        const hashes = entries.map(entry => entry.hash).join(',');
        let url = `${this.settings.getBaseUrl()}secure/uploads/download?hashes=${hashes}`;
        if (this.data.ticketEntry) url += '&ticketEntry=true';
        downloadFileFromUrl(url);
    }

    public urlTransformer(entry: FileEntry) {
        return `${entry.url}?ticketEntry=true`;
    }
}

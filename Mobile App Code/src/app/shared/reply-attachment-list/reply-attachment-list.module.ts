import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ReplyAttachmentListComponent} from './reply-attachment-list.component';
import {MatButtonModule} from '@angular/material/button';
import {MatIconModule} from '@angular/material/icon';
import {FormatPipesModule} from '@common/core/ui/format-pipes/format-pipes.module';
import {FilePreviewOverlayComponent} from '../file-preview-overlay/file-preview-overlay.component';
import {FilePreviewToolbarComponent} from '../file-preview-overlay/file-preview-toolbar/file-preview-toolbar.component';
import {FilePreviewModule} from '@common/file-preview/file-preview.module';

@NgModule({
    declarations: [
        ReplyAttachmentListComponent,
        FilePreviewOverlayComponent,
        FilePreviewToolbarComponent,
    ],
    imports: [
        CommonModule,
        FilePreviewModule,

        // material
        MatButtonModule,
        MatIconModule,
        FormatPipesModule,
    ],
    exports: [
        ReplyAttachmentListComponent,
    ],
})
export class ReplyAttachmentListModule {
}

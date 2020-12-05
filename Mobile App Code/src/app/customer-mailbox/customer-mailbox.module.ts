import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import {RouterModule} from '@angular/router';
import {CustomerTicketsListComponent} from './customer-tickets-list/customer-tickets-list.component';
import {routing} from './customer-mailbox.routing';
import {CustomerMailboxComponent} from './customer-mailbox.component';
import {NewTicketComponent} from '../ticketing/new-ticket/new-ticket.component';
import {SuggestedArticlesDrawerComponent} from '../ticketing/suggested-articles-drawer/suggested-articles-drawer.component';
import {CustomerConversationComponent} from './customer-conversation/customer-conversation.component';
import {HelpCenterSharedModule} from '../help-center/shared/help-center-shared.module';
import {ConversationModule} from '../conversation/conversation.module';
import {MatChipsModule} from '@angular/material/chips';
import {SuggestedArticleDropdownModule} from '../help-center/suggested-articles-dropdown/suggested-article-dropdown.module';
import {MaterialNavbarModule} from '@common/core/ui/material-navbar/material-navbar.module';
import {TextEditorModule} from '@common/text-editor/text-editor.module';
import {TranslationsModule} from '@common/core/translations/translations.module';
import {MatButtonModule} from '@angular/material/button';
import {FormatPipesModule} from '@common/core/ui/format-pipes/format-pipes.module';
import {LoadingIndicatorModule} from '@common/core/ui/loading-indicator/loading-indicator.module';
import {NoResultsMessageModule} from '@common/core/ui/no-results-message/no-results-message.module';
import {MatIconModule} from '@angular/material/icon';
import {CustomScrollbarModule} from '@common/core/ui/custom-scrollbar/custom-scrollbar.module';
import {ReplyAttachmentListModule} from '../shared/reply-attachment-list/reply-attachment-list.module';
import {CustomerFooterModule} from '../shared/customer-footer/customer-footer.module';
import {UploadsModule} from '@common/uploads/uploads.module';
import {DatatableModule} from '../../common/datatable/datatable.module';

@NgModule({
    imports:      [
        CommonModule,
        FormsModule,
        ReactiveFormsModule,
        RouterModule,
        ConversationModule,
        HelpCenterSharedModule,
        SuggestedArticleDropdownModule,
        TextEditorModule,
        MaterialNavbarModule,
        TranslationsModule,
        LoadingIndicatorModule,
        NoResultsMessageModule,
        CustomScrollbarModule,
        ReplyAttachmentListModule,
        CustomerFooterModule,
        UploadsModule,
        DatatableModule,
        routing,

        // material
        MatIconModule,
        MatChipsModule,
        MatButtonModule,
        FormatPipesModule,
    ],
    declarations: [
        CustomerMailboxComponent,
        CustomerTicketsListComponent,
        NewTicketComponent,
        SuggestedArticlesDrawerComponent,
        CustomerConversationComponent,
    ],
    exports: [],
})
export class CustomerMailboxModule { }

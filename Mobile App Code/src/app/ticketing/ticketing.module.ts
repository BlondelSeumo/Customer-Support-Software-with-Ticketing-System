import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import {RouterModule} from '@angular/router';
import {routing} from './ticketing.routing';
import {MailboxComponent} from './mailbox/mailbox.component';
import {TicketFloatingToolbarComponent} from './ticket-floating-toolbar/ticket-floating-toolbar.component';
import {AgentMailboxTicketListComponent} from './agent-mailbox-ticket-list/agent-mailbox-ticket-list.component';
import {AgentSearchModalComponent} from './agent-search-modal/agent-search-modal.component';
import {TicketsService} from './tickets.service';
import {ConversationModule} from '../conversation/conversation.module';
import {UserProfileComponent} from './user-profile/user-profile.component';
import {UserProfileResolve} from './user-profile/user-profile-resolve.service';
import {MatAutocompleteModule} from '@angular/material/autocomplete';
import {MatDialogModule} from '@angular/material/dialog';
import {MatMenuModule} from '@angular/material/menu';
import {MatSidenavModule} from '@angular/material/sidenav';
import {TicketSearchDropdownComponent} from './ticket-search-dropdown/ticket-search-dropdown.component';
import {CreateTicketComponent} from './create-ticket/create-ticket.component';
import {TextEditorModule} from '@common/text-editor/text-editor.module';
import {MaterialNavbarModule} from '@common/core/ui/material-navbar/material-navbar.module';
import {MatIconModule} from '@angular/material/icon';
import {MatTooltipModule} from '@angular/material/tooltip';
import {TranslationsModule} from '@common/core/translations/translations.module';
import {NoResultsMessageModule} from '@common/core/ui/no-results-message/no-results-message.module';
import {MatButtonModule} from '@angular/material/button';
import {ReplyAttachmentListModule} from '../shared/reply-attachment-list/reply-attachment-list.module';
import {EmailAddressModalComponent} from '../shared/email-address-modal/email-address-modal.component';
import {TicketsListModule} from '../shared/tickets-list/tickets-list.module';
import {UploadsModule} from '@common/uploads/uploads.module';
import {CustomScrollbarModule} from '@common/core/ui/custom-scrollbar/custom-scrollbar.module';
import {MatTabsModule} from '@angular/material/tabs';
import {SearchReportTableModule} from '../admin/analytics/help-center-report/search-report-table/search-report-table.module';
import {InfoPopoverModule} from '../../common/core/ui/info-popover/info-popover.module';
import {TagsManagerModule} from '../../common/tags/tags-manager/tags-manager.module';
import {SkeletonModule} from '../../common/core/ui/skeleton/skeleton.module';

@NgModule({
    imports: [
        CommonModule,
        FormsModule,
        ReactiveFormsModule,
        RouterModule,
        ConversationModule,
        TextEditorModule,
        MaterialNavbarModule,
        TranslationsModule,
        NoResultsMessageModule,
        ReplyAttachmentListModule,
        TagsManagerModule,
        TicketsListModule,
        UploadsModule,
        CustomScrollbarModule,
        SearchReportTableModule,
        InfoPopoverModule,
        SkeletonModule,
        routing,

        // material
        MatIconModule,
        MatButtonModule,
        MatTooltipModule,
        MatAutocompleteModule,
        MatMenuModule,
        MatDialogModule,
        MatSidenavModule,
        MatTabsModule,
    ],
    declarations: [
        MailboxComponent,
        TicketFloatingToolbarComponent,
        AgentMailboxTicketListComponent,
        TicketSearchDropdownComponent,
        AgentSearchModalComponent,
        UserProfileComponent,
        CreateTicketComponent,
        EmailAddressModalComponent,
    ],
    providers: [
        TicketsService,
        UserProfileResolve,
    ]
})
export class TicketingModule {
}

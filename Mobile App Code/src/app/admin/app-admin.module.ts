import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import {EnvatoSettingsComponent} from './settings/envato/envato-settings.component';
import {HelpCenterSettingsComponent} from './settings/help-center/help-center-settings.component';
import {RealtimeSettingsComponent} from './settings/realtime/realtime-settings.component';
import {TicketingSettingsComponent} from './settings/ticketing/ticketing-settings.component';
import {TriggerComponent} from './triggers/triggers.component';
import {ConditionsComponent} from './triggers/conditions/conditions.component';
import {CannedRepliesComponent} from './canned-replies/canned-replies.component';
import {SearchSettingsComponent} from './settings/search/search-settings.component';
import {CrupdateTriggerComponent} from './triggers/crupdate-trigger/crupdate-trigger.component';
import {BaseAdminModule} from '@common/admin/base-admin.module';
import {TicketCategoriesComponent} from './ticket-categories/ticket-categories.component';
import {CrupdateTicketCategoryModalComponent} from './ticket-categories/crupdate-ticket-category-modal/crupdate-ticket-category-modal.component';
import {MatAutocompleteModule} from '@angular/material/autocomplete';
import {MatListModule} from '@angular/material/list';
import {MatProgressBarModule} from '@angular/material/progress-bar';
import {MatButtonModule} from '@angular/material/button';
import {MatIconModule} from '@angular/material/icon';
import {MatTooltipModule} from '@angular/material/tooltip';
import {TranslationsModule} from '@common/core/translations/translations.module';
import {ConfirmModalModule} from '@common/core/ui/confirm-modal/confirm-modal.module';

@NgModule({
    imports:      [
        CommonModule,
        FormsModule,
        ReactiveFormsModule,
        BaseAdminModule,
        TranslationsModule,
        ConfirmModalModule,

        // material
        MatAutocompleteModule,
        MatListModule,
        MatProgressBarModule,
        MatButtonModule,
        MatIconModule,
        MatTooltipModule,
    ],
    declarations: [
        CannedRepliesComponent,

        // triggers
        TriggerComponent,
        ConditionsComponent,
        CrupdateTriggerComponent,

        // settings
        EnvatoSettingsComponent,
        HelpCenterSettingsComponent,
        RealtimeSettingsComponent,
        TicketingSettingsComponent,
        SearchSettingsComponent,
        TicketCategoriesComponent,
        CrupdateTicketCategoryModalComponent,
    ],
})
export class AppAdminModule { }

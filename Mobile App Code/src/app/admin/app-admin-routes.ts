import {Routes} from '@angular/router';
import {EnvatoSettingsComponent} from './settings/envato/envato-settings.component';
import {HelpCenterSettingsComponent} from './settings/help-center/help-center-settings.component';
import {TicketingSettingsComponent} from './settings/ticketing/ticketing-settings.component';
import {RealtimeSettingsComponent} from './settings/realtime/realtime-settings.component';
import {TriggerComponent} from './triggers/triggers.component';
import {CrupdateTriggerComponent} from './triggers/crupdate-trigger/crupdate-trigger.component';
import {TriggerResolve} from './triggers/trigger-resolve.service';
import {CannedRepliesComponent} from './canned-replies/canned-replies.component';
import {SearchSettingsComponent} from './settings/search/search-settings.component';
import {TicketCategoriesComponent} from './ticket-categories/ticket-categories.component';

export const APP_ADMIN_ROUTES: Routes = [
    {
        path: 'ticket-categories',
        component: TicketCategoriesComponent, data: {permissions: ['tags.view']}
    },
    {
        path: 'canned-replies',
        component: CannedRepliesComponent,
        data: {permissions: ['canned_replies.view']}
    },
    {
        path: 'triggers',
        component: TriggerComponent,
        data: {permissions: ['triggers.view']}
    },
    {
        path: 'triggers/new',
        component: CrupdateTriggerComponent,
        data: {permissions: ['triggers.create']}
    },
    {
        path: 'triggers/:id/edit',
        component: CrupdateTriggerComponent,
        resolve: {trigger: TriggerResolve},
        data: {permissions: ['triggers.update']}
    },
];

export const APP_SETTING_ROUTES: Routes = [
    {path: 'envato', component: EnvatoSettingsComponent},
    {path: 'help center', component: HelpCenterSettingsComponent},
    {path: 'realtime', component: RealtimeSettingsComponent},
    {path: 'ticketing', component: TicketingSettingsComponent},
    {path: 'search', component: SearchSettingsComponent},
];

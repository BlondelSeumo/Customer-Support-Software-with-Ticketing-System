import {ChangeDetectionStrategy, Component} from '@angular/core';
import {SettingsPanelComponent} from '@common/admin/settings/settings-panel.component';

@Component({
    selector: 'ticketing-settings',
    templateUrl: './ticketing-settings.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    host: {'class': 'settings-panel'},
})
export class TicketingSettingsComponent extends SettingsPanelComponent {}

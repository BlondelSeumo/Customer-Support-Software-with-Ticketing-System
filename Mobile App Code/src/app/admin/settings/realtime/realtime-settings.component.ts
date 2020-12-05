import {ChangeDetectionStrategy, Component} from '@angular/core';
import {SettingsPanelComponent} from '@common/admin/settings/settings-panel.component';

@Component({
    selector: 'realtime-settings',
    templateUrl: './realtime-settings.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    host: {'class': 'settings-panel'},
})
export class RealtimeSettingsComponent extends SettingsPanelComponent {
}

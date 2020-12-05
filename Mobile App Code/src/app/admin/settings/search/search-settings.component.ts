import {Component} from '@angular/core';
import {SettingsPanelComponent} from '@common/admin/settings/settings-panel.component';

@Component({
    selector: 'search-settings',
    templateUrl: './search-settings.component.html',
    host: {'class': 'settings-panel'},
})
export class SearchSettingsComponent extends SettingsPanelComponent {}

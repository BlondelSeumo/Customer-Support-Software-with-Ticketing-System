import {Component, ViewEncapsulation} from '@angular/core';
import {SettingsPanelComponent} from '@common/admin/settings/settings-panel.component';

@Component({
    selector: 'envato-settings',
    templateUrl: './envato-settings.component.html',
    encapsulation: ViewEncapsulation.None,
    host: {'class': 'settings-panel'},
})
export class EnvatoSettingsComponent extends SettingsPanelComponent {
    public importEnvatoItems() {
        this.loading$.next(true);

        this.http.post('envato/items/import').subscribe(() => {
            this.toast.open('Imported envato items');
            this.loading$.next(false);
        });
    }

}

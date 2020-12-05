import {Component, ViewEncapsulation} from '@angular/core';
import {SettingsPanelComponent} from '@common/admin/settings/settings-panel.component';
import {ConfirmModalComponent} from '@common/core/ui/confirm-modal/confirm-modal.component';
import {downloadFileFromUrl} from '@common/uploads/utils/download-file-from-url';
import {openUploadWindow} from '@common/uploads/utils/open-upload-window';

@Component({
    selector: 'help-center-settings',
    templateUrl: './help-center-settings.component.html',
    encapsulation: ViewEncapsulation.None,
    host: {'class': 'settings-panel'},
})
export class HelpCenterSettingsComponent extends SettingsPanelComponent {
    public confirmAndImportHelpCenterData(payload: FormData) {
        this.modal.show(ConfirmModalComponent, {
            title: 'Import Help Center Data',
            body:  'Are you sure you want to import help center data?',
            bodyBold: 'This will erase all existing articles and categories.',
            ok:    'Import'
        }).afterClosed().subscribe(confirmed => {
            if ( ! confirmed) return;

            this.loading$.next(true);

            this.http.post('help-center/actions/import', payload).subscribe(() => {
                this.toast.open('Imported Help Center Data');
                 this.loading$.next(false);
            }, () =>  this.loading$.next(false));
        });
    }

    public openHelpCenterImportWindow() {
        openUploadWindow({extensions: ['.zip']}).then(files => {
            const payload = new FormData();
            payload.append('data', files[0].native);
            this.confirmAndImportHelpCenterData(payload);
        });
    }

    public exportHelpCenterData(format: 'html'|'json') {
        const url = `${this.settings.getBaseUrl()}secure/help-center/actions/export?format=${format}`;
        downloadFileFromUrl(url, 'hc-export.json');
    }
}

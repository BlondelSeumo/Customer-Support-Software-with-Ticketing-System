import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {BaseAppearanceModule} from '@common/admin/appearance/base-appearance.module';
import {NewTicketAppearancePanelComponent} from './new-ticket-appearance-panel/new-ticket-appearance-panel.component';
import {MatIconModule} from '@angular/material/icon';
import {MatButtonModule} from '@angular/material/button';
import {TranslationsModule} from '@common/core/translations/translations.module';
import {ReactiveFormsModule} from '@angular/forms';
import {HelpCenterHomeAppearancePanelComponent} from './help-center-home-appearance-panel/help-center-home-appearance-panel.component';
import {APPEARANCE_EDITOR_CONFIG} from '../../../common/admin/appearance/appearance-editor-config.token';
import {APP_APPEARANCE_CONFIG} from './app-appearance-config';


@NgModule({
    declarations: [
        NewTicketAppearancePanelComponent,
        HelpCenterHomeAppearancePanelComponent,
    ],
    imports: [
        CommonModule,
        BaseAppearanceModule,

        MatIconModule,
        MatButtonModule,
        TranslationsModule,
        ReactiveFormsModule,
    ],
    providers: [
        {
            provide: APPEARANCE_EDITOR_CONFIG,
            useValue: APP_APPEARANCE_CONFIG,
            multi: true,
        }
    ]
})
export class AppAppearanceModule {
}

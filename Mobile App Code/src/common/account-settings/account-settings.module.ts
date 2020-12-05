import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import {ConnectSocialAccountsPanelComponent} from './connect-social-accounts-panel/connect-social-accounts-panel.component';
import {AccountSettingsResolve} from './account-settings-resolve.service';
import {AccountSettingsRoutingModule} from './account-settings-routing.module';
import {AccountSettingsComponent} from './account-settings.component';
import {MaterialNavbarModule} from '@common/core/ui/material-navbar/material-navbar.module';
import {MatButtonModule} from '@angular/material/button';
import {MatIconModule} from '@angular/material/icon';
import {MatMenuModule} from '@angular/material/menu';
import {TranslationsModule} from '@common/core/translations/translations.module';

@NgModule({
    imports:      [
        CommonModule,
        FormsModule,
        ReactiveFormsModule,
        MaterialNavbarModule,
        AccountSettingsRoutingModule,
        TranslationsModule,

        // material
        MatButtonModule,
        MatIconModule,
        MatMenuModule,
    ],
    declarations: [
        AccountSettingsComponent,
        ConnectSocialAccountsPanelComponent,
    ],
    exports:      [
        AccountSettingsRoutingModule,
    ],
    providers:    [
        AccountSettingsResolve
    ]
})
export class AccountSettingsModule { }

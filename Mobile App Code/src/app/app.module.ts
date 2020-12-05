import {NgModule} from '@angular/core';
import {BrowserModule} from '@angular/platform-browser';
import {AppComponent} from './app.component';
import {RouterModule} from '@angular/router';
import {BrowserAnimationsModule} from '@angular/platform-browser/animations';
import {AuthModule} from '@common/auth/auth.module';
import {AccountSettingsModule} from '@common/account-settings/account-settings.module';
import {AppRoutingModule} from './app-routing.module';
import {HelpCenterModule} from './help-center/front/help-center.module';
import {BEDESK_CONFIG} from './bedesk-config';
import {ACCOUNT_SETTINGS_PANELS} from '@common/account-settings/account-settings-panels';
import {EnvatoPurchasesPanelComponent} from './account-settings/envato-purchases-panel/envato-purchases-panel.component';
import {APP_CONFIG} from '@common/core/config/app-config';
import {HTTP_INTERCEPTORS, HttpClientModule} from '@angular/common/http';
import {EchoInterceptor} from './shared/broadcasting/echo-interceptor.service';
import {TranslationsModule} from '@common/core/translations/translations.module';
import {MatIconModule} from '@angular/material/icon';
import {MatButtonModule} from '@angular/material/button';
import {CORE_PROVIDERS} from '@common/core/core-providers';
import {CommonModule} from '@angular/common';
import {CookieNoticeModule} from '@common/gdpr/cookie-notice/cookie-notice.module';
import {PagesModule} from '@common/pages/shared/pages.module';
import { AddPurchaseUsingCodeModalComponent } from './account-settings/add-purchase-using-code-modal/add-purchase-using-code-modal.component';
import {MatDialogModule} from '@angular/material/dialog';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';

@NgModule({
    declarations: [
        AppComponent,
        EnvatoPurchasesPanelComponent,
        AddPurchaseUsingCodeModalComponent,
    ],
    imports: [
        CommonModule,
        BrowserModule,
        HttpClientModule,
        BrowserAnimationsModule,
        RouterModule.forRoot([], {
            scrollPositionRestoration: 'top'
        }),
        PagesModule,
        AuthModule,
        AccountSettingsModule,
        HelpCenterModule,
        AppRoutingModule,
        TranslationsModule,
        CookieNoticeModule,

        // material
        MatIconModule,
        MatButtonModule,
        MatDialogModule,
        FormsModule,
        ReactiveFormsModule,
        // ServiceWorkerModule.register('client/ngsw-worker.js', {enabled: environment.production, scope: '/'}),
    ],
    providers: [
        ...CORE_PROVIDERS,
        {
            provide: APP_CONFIG,
            useValue: BEDESK_CONFIG,
            multi: true,
        },
        {
            provide: ACCOUNT_SETTINGS_PANELS,
            useValue: {component: EnvatoPurchasesPanelComponent},
            multi: true
        },
        {
            provide: HTTP_INTERCEPTORS,
            useClass: EchoInterceptor,
            multi: true
        }
    ],
    bootstrap: [AppComponent],
})
export class AppModule {}

import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {CustomerFooterComponent} from './customer-footer.component';
import {TranslationsModule} from '@common/core/translations/translations.module';
import {RouterModule} from '@angular/router';
import {CustomMenuModule} from '@common/core/ui/custom-menu/custom-menu.module';


@NgModule({
    declarations: [
        CustomerFooterComponent,
    ],
    imports: [
        CommonModule,
        TranslationsModule,
        RouterModule,
        CustomMenuModule,
    ],
    exports: [
        CustomerFooterComponent,
    ]
})
export class CustomerFooterModule {
}

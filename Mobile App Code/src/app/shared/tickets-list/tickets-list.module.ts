import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TicketsListComponent} from './tickets-list.component';
import {TranslationsModule} from '@common/core/translations/translations.module';
import {MatRippleModule} from '@angular/material/core';
import {DatatableModule} from '../../../common/datatable/datatable.module';
import {MatProgressBarModule} from '@angular/material/progress-bar';
import {RouterModule} from '@angular/router';
import {MatChipsModule} from '@angular/material/chips';


@NgModule({
    declarations: [
        TicketsListComponent,
    ],
    imports: [
        CommonModule,
        DatatableModule,
        TranslationsModule,
        RouterModule,

        MatRippleModule,
        MatProgressBarModule,
        MatChipsModule,
    ],
    exports: [
        TicketsListComponent,
    ]
})
export class TicketsListModule {
}

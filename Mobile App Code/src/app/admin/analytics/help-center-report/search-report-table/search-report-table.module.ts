import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {SearchReportTableComponent} from './search-report-table.component';
import {RouterModule} from '@angular/router';
import {TranslationsModule} from '../../../../../common/core/translations/translations.module';
import {FormatPipesModule} from '../../../../../common/core/ui/format-pipes/format-pipes.module';
import {DatatableModule} from '../../../../../common/datatable/datatable.module';


@NgModule({
    declarations: [
        SearchReportTableComponent,
    ],
    imports: [
        CommonModule,
        RouterModule,
        TranslationsModule,
        FormatPipesModule,
        DatatableModule,
    ],
    exports: [
        SearchReportTableComponent
    ]
})
export class SearchReportTableModule {
}

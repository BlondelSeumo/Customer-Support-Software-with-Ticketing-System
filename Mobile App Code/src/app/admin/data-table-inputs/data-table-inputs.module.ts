import {NgModule} from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatMenuModule } from '@angular/material/menu';
import {CommonModule} from '@angular/common';
import {TranslationsModule} from '@common/core/translations/translations.module';
import {ReactiveFormsModule} from '@angular/forms';
import {LoadingIndicatorModule} from '@common/core/ui/loading-indicator/loading-indicator.module';
import {AppDataTableInputsComponent} from './app-data-table-inputs/app-data-table-inputs.component';

@NgModule({
    declarations: [
        AppDataTableInputsComponent,
    ],
    imports: [
        CommonModule,
        MatMenuModule,
        TranslationsModule,
        ReactiveFormsModule,
        MatIconModule,
        MatButtonModule,
        LoadingIndicatorModule,
    ],
    exports: [
        AppDataTableInputsComponent,
    ],
    entryComponents: [
    ],
})
export class DataTableInputsModule {
}

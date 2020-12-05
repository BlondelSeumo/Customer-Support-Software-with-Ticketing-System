import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {SuggestedArticlesDropdownComponent} from './suggested-articles-dropdown.component';
import {MatAutocompleteModule} from '@angular/material/autocomplete';
import {MatIconModule} from '@angular/material/icon';
import {RouterModule} from '@angular/router';
import {TranslationsModule} from '@common/core/translations/translations.module';
import {NoResultsMessageModule} from '@common/core/ui/no-results-message/no-results-message.module';
import {MatButtonModule} from '@angular/material/button';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';

@NgModule({
    imports: [
        CommonModule,
        RouterModule,
        TranslationsModule,
        NoResultsMessageModule,
        ReactiveFormsModule,
        FormsModule,

        // material
        MatButtonModule,
        MatAutocompleteModule,
        MatIconModule,
    ],
    declarations: [
        SuggestedArticlesDropdownComponent,
    ],
    exports: [
        SuggestedArticlesDropdownComponent
    ]
})
export class SuggestedArticleDropdownModule {
}

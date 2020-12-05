import {ChangeDetectionStrategy, Component, Inject} from '@angular/core';
import {Tag} from '../../../shared/models/Tag';
import {MatAutocompleteSelectedEvent} from '@angular/material/autocomplete';
import {MAT_DIALOG_DATA, MatDialogRef} from '@angular/material/dialog';
import {Toast} from '@common/core/ui/toast.service';
import {TagService} from '../../../shared/tag.service';
import {FormControl} from '@angular/forms';
import {BehaviorSubject, Observable, of} from 'rxjs';
import {Category} from '../../../shared/models/Category';
import {
    debounceTime,
    distinctUntilChanged,
    map,
    startWith,
    switchMap
} from 'rxjs/operators';
import {CategoriesService} from '../../../help-center/shared/categories.service';
import {BackendErrorResponse} from '@common/core/types/backend-error-response';

interface CrupdateTicketCategoryModalData {
    tag?: Tag;
}

interface CrupdateTicketCategoryModalErrors {
    name?: string;
    display_name?: string;
    category?: string;
}

@Component({
    selector: 'crupdate-ticket-category-modal',
    templateUrl: './crupdate-ticket-category-modal.component.html',
    styleUrls: ['./crupdate-ticket-category-modal.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class CrupdateTicketCategoryModalComponent {
    public model = new Tag();
    public updating = false;
    public errors$ = new BehaviorSubject<CrupdateTicketCategoryModalErrors>({});
    public categorySearchControl = new FormControl();
    public categoryResults$: Observable<Category[]>|null;
    public attachedCategories$ = new BehaviorSubject<Category[]>([]);

    constructor(
        private dialogRef: MatDialogRef<CrupdateTicketCategoryModalComponent>,
        @Inject(MAT_DIALOG_DATA) public data: CrupdateTicketCategoryModalData,
        private toast: Toast,
        private tags: TagService,
        private categories: CategoriesService,
    ) {
        this.hydrate();
        this.bindCategorySearchControl();
    }

    public close(tag?: Tag) {
        this.errors$.next({});
        this.dialogRef.close(tag);
    }

    private hydrate() {
        if ( ! this.data.tag) return;
        this.updating = true;
        this.model = {...this.data.tag};
        this.attachedCategories$.next(this.data.tag.categories);
    }

    public confirm() {
        let request;

        if (this.updating) {
            request = this.tags.update(this.model.id, this.getPayload());
        } else {
            request = this.tags.createNew(this.getPayload());
        }

        request.subscribe(response => {
            this.toast.open('Category ' + (this.updating ? 'Updated.' : 'Created.'));
            this.close(response.tag);
        }, (errResponse: BackendErrorResponse) => this.errors$.next(errResponse.errors));
    }

    public getPayload() {
        const taggables = this.attachedCategories$.value.map(cat => {
            return {id: cat.id, relation: 'categories'};
        });
        return {
            name: this.model.name,
            display_name: this.model.display_name,
            type: 'category',
            taggables: taggables,
        };
    }

    public attachCategory(e: MatAutocompleteSelectedEvent) {
        const newCategory = e.option.value as Category,
            categories = this.attachedCategories$.value;
        if ( ! categories.find(cat => cat.id === newCategory.id)) {
            this.attachedCategories$.next([...categories, newCategory]);
        }
    }

    public detachCategory(category: Category) {
        const newCategories = this.attachedCategories$.value
            .filter(cat => cat.id !== category.id);
        this.attachedCategories$.next(newCategories);
    }

    public categoryDisplayFn(category?: Category|string): string {
        if ( ! category || typeof category !== 'string') return '';
        return category;
    }

    private bindCategorySearchControl() {
        this.categoryResults$ = this.categorySearchControl.valueChanges
            .pipe(
                distinctUntilChanged(),
                debounceTime(150),
                startWith(''),
                switchMap(query => {
                    if ( ! query || typeof query !== 'string') return of([]);
                    return this.categories.getCategories({query, limit: 5})
                        .pipe(map(response => response.categories));
                })
            );
    }
}

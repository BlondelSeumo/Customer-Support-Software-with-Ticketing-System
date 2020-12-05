import {Component, OnInit, ViewChild, ViewEncapsulation} from '@angular/core';
import {Category} from '../../../shared/models/Category';
import {CategoriesService} from '../../shared/categories.service';
import {ControlValueAccessor, FormControl, NG_VALUE_ACCESSOR} from '@angular/forms';
import {CategoriesFilterer} from '../categories-filterer';
import {CustomScrollbarDirective} from '@common/core/ui/custom-scrollbar/custom-scrollbar.directive';
import {LocalStorage} from '@common/core/services/local-storage.service';
import {debounceTime, distinctUntilChanged, share, tap} from 'rxjs/operators';
import {Observable} from 'rxjs';

@Component({
    selector: 'categories-manager',
    templateUrl: './categories-manager.component.html',
    styleUrls: ['./categories-manager.component.scss'],
    encapsulation: ViewEncapsulation.None,
    providers: [{
        provide: NG_VALUE_ACCESSOR,
        useExisting: CategoriesManagerComponent,
        multi: true,
    }]
})
export class CategoriesManagerComponent implements OnInit, ControlValueAccessor {
    @ViewChild(CustomScrollbarDirective, { static: true }) scrollbar: CustomScrollbarDirective;

    public searchControl = new FormControl();
    public selectedCategories: number[] = [];
    public filteredCategories: Category[] = [];
    public allCategories: Category[] = [];
    private propagateChange: (categories: number[]) => void;

    constructor(private api: CategoriesService, private storage: LocalStorage) {}

    ngOnInit() {
        this.bindSearchQuery();

        if ( ! this.allCategories.length) {
            this.refresh();
        }
    }

    public writeValue(value: string[] = []) {
        this.selectedCategories = value.length ? value : this.storage.get('selectedCategories', []);
    }

    public registerOnChange(fn: (categories: number[]) => void) {
        this.propagateChange = fn;
    }

    public registerOnTouched() {}

    public refresh(): Observable<any> {
        const request = this.api.getCategories().pipe(tap(response => {
            this.filteredCategories = response.categories;
            this.allCategories = response.categories;
        }), share());
        request.subscribe();
        return request;
    }

    public categoryIsSelected(id: number) {
        return this.selectedCategories.indexOf(id) > -1;
    }

    public childIsSelected(category: Category): boolean {
        if ( ! category.children.length) return false;
        for (let i = 0; i < category.children.length; i++) {
            if (this.categoryIsSelected(category.children[i].id)) {
                return true;
            }
        }
    }

    public toggle(category: Category, parentId?: number) {
        const index = this.selectedCategories.indexOf(category.id);

        // toggle category
        if (index > -1) {
            this.selectedCategories.splice(index, 1);
        } else {
            this.selectedCategories.push(category.id);
        }

        // also select parent if we are toggling child category
        if (parentId && ! this.categoryIsSelected(parentId)) {
            this.selectedCategories.push(parentId);
        }

        // deselect all child categories as well
        this.deselectChildren(category);

        this.storage.set('selectedCategories', this.selectedCategories);
        this.propagateChange(this.selectedCategories);
    }

    public deselectAll() {
        this.selectedCategories = [];
        this.storage.set('selectedCategories', []);
        this.propagateChange(this.selectedCategories);
    }

    private deselectChildren(parent: Category) {
        if ( ! parent.children) return;
        parent.children.forEach(child => {
            const index = this.selectedCategories.indexOf(child.id);
            if (index > -1) {
                this.selectedCategories.splice(index, 1);
            }
        });
    }

    private bindSearchQuery() {
        this.searchControl.valueChanges
            .pipe(debounceTime(400), distinctUntilChanged())
            .subscribe(query => {
                this.filteredCategories = (new CategoriesFilterer()).filter(query, this.allCategories);
                this.scrollbar.update();
            });
    }
}

import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {FormControl} from '@angular/forms';
import {CategoryModalComponent} from '../category-modal/category-modal.component';
import {Category} from '../../../shared/models/Category';
import {CategoriesService} from '../../shared/categories.service';
import {CategoriesFilterer} from '../categories-filterer';
import {debounceTime, distinctUntilChanged} from 'rxjs/operators';
import {Modal} from '@common/core/ui/dialogs/modal.service';
import {CdkDragDrop, moveItemInArray} from '@angular/cdk/drag-drop';

@Component({
    selector: 'categories-list',
    templateUrl: './categories-list.component.html',
    styleUrls: ['./categories-list.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class CategoriesListComponent implements OnInit {
    public searchQuery = new FormControl();
    public allCategories: Category[] = [];
    public filteredCategories: Category[] = [];
    public categoryChildrenVisible = true;

    constructor(
        public api: CategoriesService,
        private modal: Modal
    ) { }

    ngOnInit() {
        this.updateCategories();
        this.bindSearchQuery();
    }

    public showNewCategoryModal() {
        this.modal.show(CategoryModalComponent).
            afterClosed()
            .subscribe(category => {
                if ( ! category) return;
                this.updateCategories();
            });
    }

    public updateCategories() {
        this.api.getCategories().subscribe(response => {
            this.filteredCategories = response.categories;
            this.allCategories      = response.categories;
            this.filterCategories(this.searchQuery.value);
        });
    }

    private bindSearchQuery() {
        this.searchQuery.valueChanges
            .pipe(debounceTime(400), distinctUntilChanged())
            .subscribe(query => this.filterCategories(query));
    }

    private filterCategories(searchQuery: string) {
        this.filteredCategories = (new CategoriesFilterer).filter(searchQuery, this.allCategories);
    }

    public reorderCategories(e: CdkDragDrop<Category>, parent?: Category) {
        const array = parent ? parent.children : this.filteredCategories;
        moveItemInArray(array, e.previousIndex, e.currentIndex);
        const ids = array.map(category => category.id);
        this.api.reorderCategories(ids, parent ? parent.id : null).subscribe();
    }

    public toggleCategoryChildren() {
        this.categoryChildrenVisible = !this.categoryChildrenVisible;
    }

    public shouldDisableReorder(): boolean {
        return this.searchQuery.value;
    }
}

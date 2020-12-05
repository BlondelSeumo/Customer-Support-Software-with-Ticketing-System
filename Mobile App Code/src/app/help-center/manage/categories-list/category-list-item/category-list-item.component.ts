import {Component, ViewEncapsulation, ChangeDetectionStrategy, Input, Output, EventEmitter} from '@angular/core';
import {CategoryModalComponent} from '../../category-modal/category-modal.component';
import {Category} from '../../../../shared/models/Category';
import {CategoriesService} from '../../../shared/categories.service';
import {Router} from '@angular/router';
import {LocalStorage} from '@common/core/services/local-storage.service';
import {Modal} from '@common/core/ui/dialogs/modal.service';
import {ConfirmModalComponent} from '@common/core/ui/confirm-modal/confirm-modal.component';

@Component({
    selector: 'category-list-item',
    templateUrl: './category-list-item.component.html',
    styleUrls: ['./category-list-item.component.scss'],
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class CategoryListItemComponent {
    constructor(
        private modal: Modal,
        private api: CategoriesService,
        private router: Router,
        private storage: LocalStorage,
    ) {}

    /**
     * Help center category model instance.
     */
    @Input() public category: Category;

    /**
     * Fired when this category model changes or is deleted.
     */
    @Output() public onChange = new EventEmitter();

    /**
     * Show modal for creating child category.
     */
    public openCreateChildCategoryModal() {
        this.modal.show(CategoryModalComponent, {parentId: this.category.id})
            .afterClosed().subscribe(() => this.onChange.emit());
    }

    /**
     * Show modal for updating specified category.
     */
    public openUpdateCategoryModal(category: Category) {
        this.modal.show(CategoryModalComponent, {category})
            .afterClosed().subscribe(() => this.onChange.emit());
    }

    /**
     * Delete specified category if user confirms.
     */
    public maybeDeleteCategory(id: number) {
        this.modal.show(ConfirmModalComponent, {
            title: 'Delete Category',
            body: 'Are you sure you want to delete this category?',
            bodyBold: 'Children of this category will not be deleted.',
            ok: 'Delete'
        }).afterClosed().subscribe(confirmed => {
            if ( ! confirmed) return;
            this.api.deleteCategory(id).subscribe(() => this.onChange.emit());
        });
    }

    /**
     * Detach specified category from parent if user confirms.
     */
    public maybeDetachCategory(id: number) {
        this.modal.show(ConfirmModalComponent, {
            title: 'Detach Category',
            body:  'Are you sure you want to detach this category from its parent?',
            ok:    'Detach'
        }).afterClosed().subscribe(confirmed => {
            if ( ! confirmed) return;
            this.api.detachCategory(id).subscribe(() => this.onChange.emit());
        });
    }

    /**
     * Select specified category and navigate to articles list route.
     */
    public navigateToArticlesList(category: Category) {
        const ids = [category.id];
        if (category.parent_id) ids.push(category.parent_id);
        this.storage.set('selectedCategories', ids);

        this.router.navigate(['/help-center/manage/articles']);
    }
}

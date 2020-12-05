import {Component, OnInit} from '@angular/core';
import {CurrentUser} from '@common/auth/current-user';
import {CrupdateTicketCategoryModalComponent} from './crupdate-ticket-category-modal/crupdate-ticket-category-modal.component';
import {TagsService} from '@common/core/services/tags.service';
import {DatatableService} from '../../../common/datatable/datatable.service';
import {Observable} from 'rxjs';
import {Tag} from '../../../common/core/types/models/Tag';

@Component({
    selector: 'ticket-categories',
    templateUrl: './ticket-categories.component.html',
    styleUrls: ['./ticket-categories.component.scss'],
    providers: [DatatableService],
})
export class TicketCategoriesComponent implements OnInit {
    public categories$ = this.datatable.data$ as Observable<Tag[]>;
    constructor(
        private tags: TagsService,
        public currentUser: CurrentUser,
        public datatable: DatatableService<Tag>
    ) { }

    ngOnInit() {
        this.datatable.init({
            uri: 'tags',
            staticParams: {
                type: 'category',
                with: ['categories'],
                withCount: ['categories']
            }
        });
    }

    public maybeDeleteSelectedCategories() {
        this.datatable.confirmResourceDeletion('categories').subscribe(() => {
            this.tags.delete(this.datatable.selectedRows$.value).subscribe(() => {
                this.datatable.reset();
            });
        });
    }

    public showCrupdateCategoryModal(tag?: Tag) {
        this.datatable.openCrupdateResourceModal(CrupdateTicketCategoryModalComponent, {tag})
            .subscribe();
    }
}

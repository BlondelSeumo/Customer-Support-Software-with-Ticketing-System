import {Component, Inject, ViewEncapsulation} from '@angular/core';
import {Category} from '../../../shared/models/Category';
import {CategoriesService} from '../../shared/categories.service';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import {AvatarValidator} from '@common/account-settings/avatar-validator';
import {openUploadWindow} from '@common/uploads/utils/open-upload-window';
import {UploadInputTypes} from '@common/uploads/upload-input-config';
import {UploadQueueService} from '@common/uploads/upload-queue/upload-queue.service';
import {Toast} from '@common/core/ui/toast.service';
import {BackendErrorResponse} from '@common/core/types/backend-error-response';

interface CategoryModalData {
    category?: Category;
    parentId?: number;
}

interface CategoryModalErrors {
    name?: string;
    parent_id?: string;
    description?: string;
    image?: string;
}

@Component({
    selector: 'category-modal',
    templateUrl: './category-modal.component.html',
    encapsulation: ViewEncapsulation.None,
    providers: [UploadQueueService],
})
export class CategoryModalComponent {
    public categories: Category[] = [];
    public model: Partial<Category> = {};
    public updating = false;
    public errors: CategoryModalErrors = {};

    constructor(
        private dialogRef: MatDialogRef<CategoryModalComponent>,
        @Inject(MAT_DIALOG_DATA) public data: CategoryModalData,
        private api: CategoriesService,
        private imageValidator: AvatarValidator,
        private uploadQueue: UploadQueueService,
        private toast: Toast,
    ) {
       this.hydrate();
    }

    public close(category?: Category) {
        this.dialogRef.close(category);
    }

    public confirm() {
        this.api.createOrUpdateCategory(this.getPayload()).subscribe(category => {
            if (this.model.id) {
                this.toast.open('Updated category.');
            } else {
                this.toast.open('Created category.');
            }
            this.close(category);
        }, (errResponse: BackendErrorResponse) => this.errors = errResponse.errors);
    }

    private hydrate() {
        this.fetchCategories(this.data.category);

        if (this.data.category) {
            this.updating = true;
            this.model = this.data.category;
        }

        if (this.data.parentId) this.model.parent_id = this.data.parentId;
    }

    private getPayload() {
        return {
            id: this.model.id,
            name: this.model.name,
            image: this.model.image,
            description: this.model.description,
            parent_id: this.model.parent_id || null,
            hidden: this.model.hidden,
        };
    }

    private fetchCategories(category?: Category) {
        this.api.getCategories().subscribe(response => {
            // remove category we're currently editing from parent_id
            // select so category can't be select as parent to itself
            this.categories = response.categories.filter(current => {
                return ! category || category.id !== current.id;
            });
        });
    }

    public openInsertImageDialog() {
        const params = {
            uri: 'uploads/images',
            httpParams: {diskPrefix: 'category'},
            validator: this.imageValidator
        };
        openUploadWindow({types: [UploadInputTypes.image], multiple: false}).then(uploadedFiles => {
            if ( ! uploadedFiles) return;
            this.uploadQueue.start(uploadedFiles, params).subscribe(response => {
                this.model.image = response.fileEntry.url;
            });
        });
    }
}

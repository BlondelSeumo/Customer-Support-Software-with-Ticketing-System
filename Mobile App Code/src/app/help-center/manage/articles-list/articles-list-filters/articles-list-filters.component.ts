import {Component, OnInit} from '@angular/core';
import {DatatableService} from '../../../../../common/datatable/datatable.service';
import {Article} from '../../../../shared/models/Article';
import {FormControl, FormGroup} from '@angular/forms';

@Component({
    selector: 'articles-list-filters',
    templateUrl: './articles-list-filters.component.html',
    styleUrls: ['./articles-list-filters.component.scss'],
})
export class ArticlesListFiltersComponent implements OnInit {
    public form = new FormGroup({
        categories: new FormControl(''),
        tags: new FormControl(''),
        draft: new FormControl(null),
    });

    constructor(private datatable: DatatableService<Article>) {}

    ngOnInit() {
        this.form.valueChanges.subscribe(value => {
            this.datatable.filters$.next(value);
        });
    }
}

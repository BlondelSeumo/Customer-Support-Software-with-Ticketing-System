import {
    ChangeDetectionStrategy,
    Component,
    Input,
    OnChanges,
    OnDestroy,
    SimpleChanges
} from '@angular/core';
import {Article} from '../../../../shared/models/Article';
import {HcUrls} from '../../../../help-center/shared/hc-urls.service';
import {DatatableService} from '@common/datatable/datatable.service';
import {Observable} from 'rxjs';

@Component({
    selector: 'article-report-table',
    templateUrl: './article-report-table.component.html',
    styleUrls: ['./article-report-table.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [DatatableService],
})
export class ArticleReportTableComponent implements OnChanges, OnDestroy {
    public items$ = this.datatable.data$ as Observable<Article[]>;
    @Input() data: Article[];

    constructor(
        public urls: HcUrls,
        public datatable: DatatableService<Article>,
    ) {
        this.datatable.init();
    }

    ngOnChanges(changes: SimpleChanges) {
        this.datatable.data = this.data || [];
    }

    ngOnDestroy() {
        this.datatable.destroy();
    }
}

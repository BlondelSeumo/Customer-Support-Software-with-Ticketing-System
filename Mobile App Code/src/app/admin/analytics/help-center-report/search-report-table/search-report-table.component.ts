import {
    ChangeDetectionStrategy,
    Component,
    Input,
    OnChanges,
    OnDestroy,
    SimpleChanges
} from '@angular/core';
import {SearchTermReport} from '../help-center-report';
import {HcUrls} from '../../../../help-center/shared/hc-urls.service';
import {DatatableService} from '../../../../../common/datatable/datatable.service';
import {Observable} from 'rxjs';

@Component({
    selector: 'search-report-table',
    templateUrl: './search-report-table.component.html',
    styleUrls: ['./search-report-table.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [DatatableService],
})
export class SearchReportTableComponent implements OnDestroy, OnChanges {
    public items$ = this.datatable.data$ as Observable<SearchTermReport[]>;
    @Input() data: SearchTermReport[];

    constructor(
        public urls: HcUrls,
        public datatable: DatatableService<SearchTermReport>,
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

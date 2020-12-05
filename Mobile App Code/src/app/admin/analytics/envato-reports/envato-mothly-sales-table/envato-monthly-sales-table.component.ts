import {
    ChangeDetectionStrategy,
    Component,
    Input,
    OnChanges,
    OnDestroy,
    SimpleChanges
} from '@angular/core';
import {EnvatoItemData, EnvatoYearlySales} from '../earnings-chart/earning-chart-data';
import {DatatableService} from '../../../../../common/datatable/datatable.service';
import {EnvatoReportsStateService} from '../envato-reports-state.service';
import {objectToArray} from '@common/core/utils/object-to-array';

@Component({
    selector: 'envato-monthly-sales-table',
    templateUrl: './envato-monthly-sales-table.component.html',
    styleUrls: ['./envato-monthly-sales-table.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [DatatableService],
})
export class EnvatoMonthlySalesTableComponent implements OnChanges, OnDestroy {
    @Input() primaryData: EnvatoYearlySales;
    @Input() compareData: EnvatoYearlySales;

    constructor(
        public datatable: DatatableService<EnvatoItemData>,
        public state: EnvatoReportsStateService,
    ) {
        this.datatable.init();
    }

    public objectToArray(obj: object) {
        return objectToArray(obj);
    }

    ngOnChanges(changes: SimpleChanges) {
        this.datatable.data = this.primaryData ? objectToArray(this.primaryData) : [];
    }

    ngOnDestroy() {
        this.datatable.destroy();
    }
}

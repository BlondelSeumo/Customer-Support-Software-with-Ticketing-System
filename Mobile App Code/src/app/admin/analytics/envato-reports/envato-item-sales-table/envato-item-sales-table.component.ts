import {
    ChangeDetectionStrategy,
    Component,
    Input,
    OnChanges,
    OnDestroy,
    SimpleChanges
} from '@angular/core';
import {EnvatoItemData} from '../earnings-chart/earning-chart-data';
import {DatatableService} from '../../../../../common/datatable/datatable.service';
import {EnvatoReportsStateService} from '../envato-reports-state.service';

@Component({
    selector: 'envato-item-sales-table',
    templateUrl: './envato-item-sales-table.component.html',
    styleUrls: ['./envato-item-sales-table.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [DatatableService],
})
export class EnvatoItemSalesTableComponent implements OnDestroy, OnChanges {
    @Input() data: EnvatoItemData[];

    constructor(
        public datatable: DatatableService<EnvatoItemData>,
        public state: EnvatoReportsStateService,
    ) {
        this.datatable.init();
    }

    public showEarningsForItem(itemId) {
        this.state.filters.envato_item_id = itemId;
        this.state.refreshActiveChart();
    }

    public stopFilteringByItem() {
        delete this.state.filters.envato_item_id;
        this.state.refreshActiveChart();
    }

    ngOnChanges(changes: SimpleChanges) {
        this.datatable.data = this.data || [];
    }

    ngOnDestroy() {
        this.datatable.destroy();
    }
}

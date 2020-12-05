import {
    Component,
    Inject,
    OnDestroy,
    OnInit,
    ViewChild,
    ViewEncapsulation
} from '@angular/core';
import {TicketsService} from '../tickets.service';
import {Ticket} from '../../shared/models/Ticket';
import {User} from '../../shared/models/User';
import {FormControl} from '@angular/forms';
import {Article} from '../../shared/models/Article';
import {HcUrls} from '../../help-center/shared/hc-urls.service';
import {debounceTime, distinctUntilChanged, finalize} from 'rxjs/operators';
import {MAT_DIALOG_DATA, MatDialogRef} from '@angular/material/dialog';
import {TicketsListComponent} from '../../shared/tickets-list/tickets-list.component';
import {PaginationResponse} from '@common/core/types/pagination/pagination-response';
import {DatatableService} from '../../../common/datatable/datatable.service';

interface AgentSearchModalData {
    query: string;
}

@Component({
    selector: 'agent-search-modal',
    templateUrl: './agent-search-modal.component.html',
    styleUrls: ['./agent-search-modal.component.scss'],
    encapsulation: ViewEncapsulation.None,
    providers: [DatatableService],
})
export class AgentSearchModalComponent implements OnInit, OnDestroy {
    @ViewChild(TicketsListComponent, { static: true }) ticketsList: TicketsListComponent;

    public results: {
        tickets?: PaginationResponse<Ticket>,
        users?: PaginationResponse<User>,
        articles?: PaginationResponse<Article>
    } = {};

    public isSearching = false;
    public searchedOnce = false;
    private activeTab = 'tickets';
    public searchQueryControl = new FormControl();

    constructor(
        private dialogRef: MatDialogRef<AgentSearchModalComponent>,
        @Inject(MAT_DIALOG_DATA) public data: AgentSearchModalData,
        private tickets: TicketsService,
        public urls: HcUrls,
        public datatable: DatatableService<Ticket>,
    ) {
        this.hydrate();
    }

    ngOnInit() {
        this.datatable.paginator.dontUpdateQueryParams = true;
    }

    ngOnDestroy() {
        this.datatable.destroy();
    }

    public close() {
        this.dialogRef.close();
    }

    private hydrate() {
        this.bindToSearchInput();
        this.searchQueryControl.setValue(this.data.query);
    }

    public setActiveTab(name: string) {
        this.activeTab = name;
    }

    public activeTabIs(name: string) {
        return this.activeTab === name;
    }

    private performSearch(query: string) {
        this.isSearching = true;
        this.tickets.search(query, this.getSearchQueryParams())
            .pipe(finalize(() => {
                this.searchedOnce = true;
                this.isSearching = false;
            }))
            .subscribe(response => {
                if ( ! this.datatable.config) {
                    this.datatable.init({
                        uri: 'search/tickets',
                        staticParams: this.getSearchQueryParams(),
                        initialData: response.results.tickets
                    });
                } else {
                    this.datatable.paginator.response$.next({pagination: response.results.tickets});
                    this.datatable.config.staticParams = this.getSearchQueryParams();
                }
                this.results = response.results;
            });
    }

    private bindToSearchInput() {
        this.searchQueryControl.valueChanges
            .pipe(debounceTime(400), distinctUntilChanged())
            .subscribe(query => {
                return this.performSearch(query);
            });
    }

    protected getSearchQueryParams() {
        return {
            query: this.searchQueryControl.value,
            perPage: 20,
            detailed: true
        };
    }
}

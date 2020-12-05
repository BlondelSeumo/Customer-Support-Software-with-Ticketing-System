import {Component, EventEmitter, Input, OnInit, Output, ViewChild} from '@angular/core';
import {FormControl} from '@angular/forms';
import {TicketsService} from '../tickets.service';
import {TagService} from '../../shared/tag.service';
import {debounceTime, distinctUntilChanged} from 'rxjs/operators';
import {Toast} from '@common/core/ui/toast.service';
import {MatMenuTrigger} from '@angular/material/menu';
import {PaginationParams} from '@common/core/types/pagination/pagination-params';
import {Tag} from '@common/core/types/models/Tag';

@Component({
    selector: 'add-tag-dropdown',
    templateUrl: './add-tag-dropdown.component.html',
    styleUrls: ['./add-tag-dropdown.component.scss'],
})
export class AddTagDropdownComponent implements OnInit {
    @ViewChild(MatMenuTrigger, { static: true }) trigger: MatMenuTrigger;

    @Input() ticketIds: number[] = [];
    @Output() tagAdded: EventEmitter<Tag> = new EventEmitter();

    public tags: Tag[];
    public tagQuery = new FormControl();
    public searchedOnce = false;

    constructor(
        private tickets: TicketsService,
        private tagService: TagService,
        private toast: Toast,
    ) {}

    ngOnInit() {
        this.bindTagQueryInput();

        this.trigger.menuOpened.subscribe(() => {
            if ( ! this.searchedOnce) {
                this.searchForTags(null, {perPage: 5});
            }
        });

        this.trigger.menuClosed.subscribe(() => {
            this.clearSearchField();
        });
    }

    public open() {
        this.trigger.openMenu();
    }

    public close() {
        this.trigger.closeMenu();
    }

    public addTag(tagName: string) {
        this.tickets.addTag(tagName, this.ticketIds).subscribe(response => {
            this.clearSearchField();
            this.toast.open('Tag added');
            this.tagAdded.emit(response.data);
            this.close();
        });
    }

    public clearSearchField() {
        if ( ! this.tagQuery.value) return;
        this.tagQuery.setValue(null);
    }

    private bindTagQueryInput() {
        this.tagQuery.valueChanges
            .pipe(debounceTime(300), distinctUntilChanged())
            .subscribe(query => {
                this.searchForTags(query);
            });
    }

    private searchForTags(query?: string, params: PaginationParams = {}) {
        this.tagService.search(query, params)
            .subscribe(response => this.tags = response.pagination.data);
    }

    public trackByFn = (i: number, tag: Tag) => tag.id;
}

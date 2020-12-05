import {Component, ViewEncapsulation, OnInit} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {Category} from '../../shared/models/Category';
import {Settings} from '@common/core/config/settings.service';
import {Paginator} from '@common/shared/paginator.service';

@Component({
    selector: 'help-center',
    templateUrl: './help-center.component.html',
    styleUrls: ['./help-center.component.scss'],
    providers: [Paginator],
    encapsulation: ViewEncapsulation.None,
})
export class HelpCenterComponent implements OnInit {
    public categories: Category[] = [];

    constructor(
        private route: ActivatedRoute,
        public settings: Settings,
    ) {}

    ngOnInit() {
        this.categories = this.route.snapshot.data.categories;
    }
}

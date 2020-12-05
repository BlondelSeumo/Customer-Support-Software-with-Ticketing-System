import {Component, ViewEncapsulation} from '@angular/core';
import {Settings} from '@common/core/config/settings.service';

@Component({
    selector: 'customer-footer',
    templateUrl: './customer-footer.component.html',
    styleUrls: ['./customer-footer.component.scss'],
    encapsulation: ViewEncapsulation.None,
    host: {'id': 'customer-footer'},
})

export class CustomerFooterComponent {
    constructor(public settings: Settings) {}

    public year(): number {
        return (new Date()).getFullYear();
    }
}

import {Component, ViewEncapsulation} from '@angular/core';
import {Settings} from '@common/core/config/settings.service';

@Component({
    selector: 'customer-mailbox.customer-mailbox',
    templateUrl: './customer-mailbox.component.html',
    styleUrls: ['./customer-mailbox.component.scss'],
    encapsulation: ViewEncapsulation.None,
})

export class CustomerMailboxComponent {
    constructor(public settings: Settings) {}
}

import {Component, ViewEncapsulation} from '@angular/core';
import {Settings} from '@common/core/config/settings.service';
import {CurrentUser} from '@common/auth/current-user';

@Component({
    selector: 'help-center-manage',
    templateUrl: './help-center-manage.component.html',
    styleUrls: ['./help-center-manage.component.scss'],
    encapsulation: ViewEncapsulation.None,
})

export class HelpCenterManageComponent {
    constructor(public settings: Settings, public currentUser: CurrentUser) {}
}

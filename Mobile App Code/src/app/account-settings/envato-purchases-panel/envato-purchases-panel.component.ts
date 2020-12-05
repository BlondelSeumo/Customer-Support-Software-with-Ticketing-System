import {Component, HostBinding, Input, ViewEncapsulation} from '@angular/core';
import {User} from '../../shared/models/User';
import {SocialAuthService} from '@common/auth/social-auth.service';
import {Toast} from '@common/core/ui/toast.service';
import {Settings} from '@common/core/config/settings.service';
import {Modal} from '@common/core/ui/dialogs/modal.service';
import {AddPurchaseUsingCodeModalComponent} from '../add-purchase-using-code-modal/add-purchase-using-code-modal.component';
import {PurchaseCode} from '../../shared/models/PurchaseCode';

@Component({
    selector: 'envato-purchases-panel',
    templateUrl: './envato-purchases-panel.component.html',
    styleUrls: ['./envato-purchases-panel.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class EnvatoPurchasesPanelComponent {
    @HostBinding('class.account-settings-panel') accountSettingsPanel = true;
    @HostBinding('class.hidden') get envatoDisabled(): boolean {
        return !this.settings.get('envato.enable');
    }

    @Input() public user = new User();


    constructor(
        private social: SocialAuthService,
        private toast: Toast,
        public settings: Settings,
        private modal: Modal,
    ) {}

    public updatePurchases() {
        this.social.connect('envato').then(user => {
            this.user.social_profiles = user.social_profiles;
            this.user.purchase_codes = user['purchase_codes'];
            this.toast.open('Updated envato purchases.');
        });
    }

    public openAddPurchaseModal() {
        this.modal.open(AddPurchaseUsingCodeModalComponent)
            .afterClosed()
            .subscribe((purchase?: PurchaseCode) => {
                if (purchase) {
                    this.user.purchase_codes.push(purchase);
                }
            });
    }
}

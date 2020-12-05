import {ChangeDetectionStrategy, Component} from '@angular/core';
import {FormControl, FormGroup} from '@angular/forms';
import {MatDialogRef} from '@angular/material/dialog';
import {Toast} from '@common/core/ui/toast.service';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import {PurchaseCode} from '../../shared/models/PurchaseCode';
import {BehaviorSubject} from 'rxjs';
import {finalize} from 'rxjs/operators';

@Component({
    selector: 'add-purchase-using-code-modal',
    templateUrl: './add-purchase-using-code-modal.component.html',
    styleUrls: ['./add-purchase-using-code-modal.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class AddPurchaseUsingCodeModalComponent {
    public loading$ = new BehaviorSubject<boolean>(false);
    public form = new FormGroup({
        code: new FormControl(''),
    });

    constructor(
        private dialogRef: MatDialogRef<AddPurchaseUsingCodeModalComponent>,
        private toast: Toast,
        private http: AppHttpClient,
    ) {}

    public close(purchase?: PurchaseCode) {
        this.dialogRef.close(purchase);
    }

    public confirm() {
        this.loading$.next(true);
        this.http.post<{purchase: PurchaseCode}>('envato/add-purchase-using-code', {purchaseCode: this.form.value.code})
            .pipe(finalize(() => this.loading$.next(false)))
            .subscribe(response => {
                this.toast.open('Added purchase.');
                this.close(response.purchase);
            });
    }
}

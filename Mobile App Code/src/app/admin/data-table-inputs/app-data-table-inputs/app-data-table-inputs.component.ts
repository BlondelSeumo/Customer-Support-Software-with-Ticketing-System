import {ChangeDetectionStrategy, Component, Input, ViewEncapsulation} from '@angular/core';
import {FormGroup} from '@angular/forms';
import {DataTableFilter} from '@common/shared/data-table/filter-panel/data-table-filters';

@Component({
    selector: 'app-data-table-inputs',
    templateUrl: './app-data-table-inputs.component.html',
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush
})
// There's no way to bind dynamically loaded custom form control
// to form in angular so we need to create a wrapper component
export class AppDataTableInputsComponent {
    @Input() formGroup: FormGroup;
    @Input() filter: DataTableFilter;
}

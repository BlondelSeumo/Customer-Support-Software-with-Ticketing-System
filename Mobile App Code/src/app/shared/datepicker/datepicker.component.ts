import {Component, OnInit, ViewChild, ElementRef, Output, Input, EventEmitter, ViewEncapsulation} from '@angular/core';
import Pikaday from 'pikaday';

@Component({
    selector: 'datepicker',
    templateUrl: './datepicker.component.html',
    styleUrls: ['./datepicker.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class DatepickerComponent implements OnInit {
    @ViewChild('input', { static: true }) input: ElementRef;
    @ViewChild('trigger', { static: true }) trigger: ElementRef;

    /**
     * Controls datepicker trigger button disabled state.
     */
    @Input() disabled = false;

    /**
     * Default date to use when picker is opened.
     */
    @Input() defaultDate: Date;

    /**
     * Fired when user selects a date.
     */
    @Output() onSelect = new EventEmitter();

    ngOnInit() {
        const picker = new Pikaday({
            field: this.input.nativeElement,
            trigger: this.trigger.nativeElement,
            maxDate: this.getMaxDate(),
            position: 'bottom right',
            defaultDate: this.defaultDate,
            onSelect: (date) => this.onSelect.emit(date)
        });
    }

    /**
     * Get latest date that user can pick.
     */
    private getMaxDate() {
        const date = new Date();
        return new Date(date.getFullYear(), date.getMonth() + 1, 0);
    }
}

import {Component, Input, OnInit} from '@angular/core';

@Component({
    selector: 'percentage-change',
    templateUrl: './percentage-change.component.html',
    styleUrls: ['./percentage-change.component.scss'],
})
export class PercentageChangeComponent implements OnInit {
    @Input() matchKey: string;
    @Input() compareKey: string;
    @Input() item: any;
    @Input() compareItem: any;
    @Input() items: any;

    public difference = 0;

    ngOnInit() {
        if ( ! this.item) return;

        if (this.compareItem) {
            this.difference = this.getDifferenceBetweenTwoNumbers();
        } else {
            this.difference = this.getDifferenceFromCollection(this.item, this.items);
        }
    }

    private getDifferenceBetweenTwoNumbers(item = null, compareItem = null) {
        if ( ! item) item = this.item;
        if ( ! compareItem) compareItem = this.compareItem;

        const percentage = ((item - compareItem) / item) * 100;
        return Math.round(percentage * 10) / 10;
    }

    /**
     * Get percentage change for given item in given data.
     */
    private getDifferenceFromCollection(item, items): number {
        if ( ! items) return 0;

        for (let i = 0; i < items.length; i++) {
            if (items[i][this.matchKey] === item[this.matchKey]) {
                return this.getDifferenceBetweenTwoNumbers(item[this.compareKey], items[i][this.compareKey]);
            }
        }

        return 0;
    }
}

import {Component, ViewEncapsulation, OnInit, Input} from '@angular/core';
import {HcUrls} from '../../shared/hc-urls.service';
import {Category} from '../../../shared/models/Category';

@Component({
    selector: 'topics-panel',
    templateUrl: './topics-panel.component.html',
    styleUrls: ['./topics-panel.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class TopicsPanelComponent implements OnInit {

    /**
     * Category model.
     */
    @Input() public category: Category;

    /**
     * Child or sibling categories for panel.
     */
    public topics: Category[] = [];

    /**
     * TopicsPanelComponent Constructor.
     */
    constructor(public urls: HcUrls) {}

    ngOnInit() {
        this.prepareTopics();
    }

    /**
     * Get title text for topics panel.
     */
    public getTitle(): string {
        return this.category && (this.category.parent ? this.category.parent.name : this.category.name);
    }

    /**
     * Set child or sibling topics on component based
     * on current category type (parent or child).
     */
    private prepareTopics() {
        if ( ! this.category) return;

        if (this.category.parent) {
            this.topics = this.category.parent.children;
        } else {
            this.topics = this.category.children;
        }
    }
}

import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {ActivatedRoute, Router} from '@angular/router';
import {delay} from 'rxjs/operators';

export interface ArticleContentNavItem {
    indent: boolean;
    display_name: string;
    slug: string;
    type: string;
}

@Component({
    selector: 'article-content',
    templateUrl: './article-content.component.html',
    styleUrls: ['./article-content.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class ArticleContentComponent implements OnInit {
    @Input() contentNav: ArticleContentNavItem[] = [];

    constructor(
        private router: Router,
        private route: ActivatedRoute,
    ) {}

    ngOnInit(): void {
        this.route.fragment
            .pipe(delay(10))
            .subscribe(fragment => {
                this.scrollContentIntoView(fragment);
            });
    }

    public scrollContentIntoView(contentItem: string) {
        if (contentItem) {
            const el = document.getElementById(contentItem) as HTMLElement;
            if (el) {
                window.scroll({ top: el.offsetTop, behavior: 'smooth' });
            }
        } else {
            window.scrollTo(0, 0);
        }
    }

    public onContentItemClick(navItemSlug: string) {
        // if this fragment is already present in url angular fragment change
        // will not trigger, so we need to manually scroll content item into view
        if (this.route.snapshot.fragment === navItemSlug) {
            this.scrollContentIntoView(navItemSlug);
        }
    }
}

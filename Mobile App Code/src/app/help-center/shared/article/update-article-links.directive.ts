import {Directive, ElementRef, AfterViewInit} from '@angular/core';
import {Router} from '@angular/router';

@Directive({
    selector: '[updateArticleLinks]',
})
export class UpdateArticleLinksDirective implements AfterViewInit {

    constructor(private el: ElementRef, private router: Router) {}

    ngAfterViewInit() {
        // remove any existing hashtag links from current url
        const currentUrl = this.router.url.split('#')[0] + '#';

        Array.from(this.el.nativeElement.querySelectorAll('a')).forEach((a: HTMLLinkElement) => {
            if (a.href.indexOf('#') === -1) return;
            a.href = currentUrl + a.href.split('#')[1];
        });
    }
}

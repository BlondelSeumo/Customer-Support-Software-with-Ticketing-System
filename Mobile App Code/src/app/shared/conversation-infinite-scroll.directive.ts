import {Directive, ElementRef, Input, NgZone, OnInit} from '@angular/core';
import {InfiniteScroll} from '@common/core/ui/infinite-scroll/infinite.scroll';
import {Conversation} from '../conversation/conversation.service';

@Directive({
    selector: '[conversationInfiniteScroll]'
})
export class ConversationInfiniteScrollDirective extends InfiniteScroll implements OnInit {
    @Input() scrollTargetIsDocument = false;

    constructor(
        protected el: ElementRef,
        private conversation: Conversation,
        protected zone: NgZone,
    ) {
        super();

    }

    ngOnInit() {
        if (this.scrollTargetIsDocument) {
            this.el = new ElementRef(document);
        }
        super.ngOnInit();
    }

    protected getScrollContainer() {
        return document;
    }

    protected loadMoreItems() {
        this.conversation.replies.loadMore();
    }

    protected isLoading(): boolean {
        return this.conversation.replies.isLoading;
    }

    protected canLoadMore(): boolean {
        return this.conversation.replies.canLoadMore();
    }
}

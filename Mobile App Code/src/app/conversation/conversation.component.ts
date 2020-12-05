import {
    AfterViewInit,
    Component,
    ElementRef,
    Input,
    OnInit,
    ViewChild,
    ViewEncapsulation
} from '@angular/core';
import {Conversation} from './conversation.service';
import {ActivatedRoute} from '@angular/router';
import {UploadsApiService} from '@common/uploads/uploads-api.service';
import {CurrentUser} from '@common/auth/current-user';

@Component({
    selector: 'conversation',
    templateUrl: './conversation.component.html',
    styleUrls: ['./conversation.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class ConversationComponent implements OnInit, AfterViewInit {
    @ViewChild('scrollContainer') scrollContainer: ElementRef<HTMLElement>;
    @Input() hideBackButton = false;
    @Input() hideSidebar = false;

    constructor(
        private route: ActivatedRoute,
        public uploads: UploadsApiService,
        public currentUser: CurrentUser,
        public conversation: Conversation,
        private el: ElementRef<HTMLElement>,
    ) {}

    ngAfterViewInit() {
        this.conversation.scrollContainer = this.scrollContainer.nativeElement;
    }

    ngOnInit() {
        this.route.data.subscribe(data => {
            this.conversation.init(data.ticket);
            this.el.nativeElement.focus();
        });
    }
}

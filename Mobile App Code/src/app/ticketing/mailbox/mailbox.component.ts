import {Component, OnInit} from '@angular/core';
import {MailboxTagsService} from '../mailbox-tags.service';
import {ActivatedRoute} from '@angular/router';
import {Translations} from '@common/core/translations/translations.service';
import {BreakpointsService} from '@common/core/ui/breakpoints.service';
import {SKELETON_ANIMATIONS} from '../../../common/core/ui/skeleton/skeleton-animations';
import {distinctUntilChanged, map} from 'rxjs/operators';
import {Tag} from '../../../common/core/types/models/Tag';

@Component({
    selector: 'mailbox',
    templateUrl: './mailbox.component.html',
    styleUrls: ['./mailbox.component.scss'],
    animations: SKELETON_ANIMATIONS,
})
export class MailboxComponent implements OnInit {
    public leftColumnIsHidden = false;

    constructor(
        public mailboxTags: MailboxTagsService,
        private route: ActivatedRoute,
        public i18n: Translations,
        public breakpoints: BreakpointsService,
    ) {}

    ngOnInit() {
        this.leftColumnIsHidden = this.breakpoints.isMobile$.value;
        this.route.queryParams.pipe(map(params => params.tagId), distinctUntilChanged())
            .subscribe(tagId => {
                this.mailboxTags.setActiveTag(tagId);
            });
    }

    public toggleLeftSidebar() {
        this.leftColumnIsHidden = !this.leftColumnIsHidden;
    }

    public trackByFn = (i: number, tag: Tag) => tag.id;
}

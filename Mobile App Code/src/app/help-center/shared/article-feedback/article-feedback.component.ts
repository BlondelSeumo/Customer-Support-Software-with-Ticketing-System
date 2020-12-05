import {
    ChangeDetectionStrategy,
    Component,
    Input, OnChanges, SimpleChanges,
    ViewEncapsulation
} from '@angular/core';
import {HelpCenterService} from '../help-center.service';
import {BehaviorSubject} from 'rxjs';

@Component({
    selector: 'article-feedback',
    templateUrl: './article-feedback.component.html',
    styleUrls: ['./article-feedback.component.scss'],
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ArticleFeedbackComponent implements OnChanges {

    /**
     * Article feedback should be attached to.
     */
    @Input() private articleId: number;

    /**
     * True if user has already submitted feedback for this article.
     * For notification purposes only, will always be false after page refresh.
     * Backend will handle duplicate feedback from same user / Client IP.
     */
    public feedbackAlreadySubmitted$ = new BehaviorSubject(false);

    /**
     * Whether user marked article as helpful or not.
     */
    public wasHelpful$ = new BehaviorSubject(null);

    /**
     * ArticleFeedbackComponent Constructor.
     */
    constructor(private helpCenter: HelpCenterService) {}

    public ngOnChanges(changes: SimpleChanges): void {
        this.feedbackAlreadySubmitted$.next(false);
        this.wasHelpful$.next(null);
    }

    /**
     * Submit user feedback about currently open article.
     */
    public submitFeedback(wasHelpful: boolean) {
        this.wasHelpful$.next(wasHelpful);
        this.helpCenter.submitArticleFeedback(this.articleId, {was_helpful: wasHelpful}).subscribe(() => {
            this.feedbackAlreadySubmitted$.next(true);
        });
    }
}

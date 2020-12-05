import {
    AfterViewInit,
    ChangeDetectionStrategy,
    ChangeDetectorRef,
    Component,
    ComponentFactoryResolver,
    Inject,
    OnInit,
    Optional,
    ViewChild,
    ViewContainerRef
} from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Users } from '../auth/users.service';
import { AuthService } from '../auth/auth.service';
import { Toast } from '../core/ui/toast.service';
import { UploadsApiService } from '../uploads/uploads-api.service';
import { Settings } from '../core/config/settings.service';
import { Translations } from '../core/translations/translations.service';
import { Localizations } from '../core/translations/localizations.service';
import { openUploadWindow } from '../uploads/utils/open-upload-window';
import { AvatarValidator } from './avatar-validator';
import { UploadInputTypes } from '../uploads/upload-input-config';
import { CurrentUser } from '../auth/current-user';
import { ACCOUNT_SETTINGS_PANELS } from './account-settings-panels';
import { ComponentType } from '@angular/cdk/portal';
import { BehaviorSubject } from 'rxjs';
import { FormBuilder } from '@angular/forms';
import { finalize } from 'rxjs/operators';
import { AccountSettingsResolverData } from '@common/account-settings/account-settings-resolve.service';
import { SelectOptionLists } from '@common/core/services/value-lists.service';
import { User } from '@common/core/types/models/User';
import { BreakpointsService } from '@common/core/ui/breakpoints.service';
import {BackendErrorResponse} from '@common/core/types/backend-error-response';

@Component({
    selector: 'account-settings',
    templateUrl: './account-settings.component.html',
    styleUrls: ['./account-settings.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AccountSettingsComponent implements OnInit, AfterViewInit {
    @ViewChild('extraPanelRef', { read: ViewContainerRef }) extraPanelRef: ViewContainerRef;

    public loading$ = new BehaviorSubject<boolean>(false);
    public avatar$ = new BehaviorSubject(null);
    public initialUser$ = new BehaviorSubject<User>(null);

    public userErrors$ = new BehaviorSubject<{
        first_name?: string,
        last_name?: string,
        country?: string,
        language?: string,
        timezone?: string,
    }>({});

    public passwordErrors$ = new BehaviorSubject<{
        current_password?: string,
        new_password?: string,
        new_password_confirmation?: string,
    }>({});

    public userForm = this.fb.group({
        first_name: [''],
        last_name: [''],
        language: [''],
        timezone: [''],
        country: [''],
    });

    public passwordForm = this.fb.group({
        current_password: [''],
        new_password: [''],
        new_password_confirmation: [''],
    });

    public selects: SelectOptionLists = {
        timezones: {},
        countries: [],
        localizations: [],
    };

    constructor(
        public settings: Settings,
        private route: ActivatedRoute,
        private users: Users,
        public currentUser: CurrentUser,
        private toast: Toast,
        private uploads: UploadsApiService,
        private i18n: Translations,
        private localizations: Localizations,
        public auth: AuthService,
        private avatarValidator: AvatarValidator,
        private fb: FormBuilder,
        private componentFactoryResolver: ComponentFactoryResolver,
        public breakpoints: BreakpointsService,
        private cd: ChangeDetectorRef,
        @Optional() @Inject(ACCOUNT_SETTINGS_PANELS) public extraPanels: {component: ComponentType<any>}[]
    ) {}

    ngOnInit() {
        this.route.data.subscribe((data: {api: AccountSettingsResolverData}) => {
            this.initialUser$.next(data.api.user);
            this.userForm.patchValue(data.api.user);
            this.avatar$.next(data.api.user.avatar);
            this.selects = data.api.selects;
        });
    }

    ngAfterViewInit() {
        this.loadExtraPanels();
    }

    public updateAccountSettings() {
        this.loading$.next(true);
        this.users.update(this.currentUser.get('id'), this.userForm.value)
            .pipe(finalize(() => this.loading$.next(false)))
            .subscribe(() => {
                this.toast.open('Account settings updated');
                this.userErrors$.next({});
            }, (errResponse: BackendErrorResponse) => this.userErrors$.next(errResponse.errors));
    }

    public openAvatarUploadDialog() {
        this.loading$.next(true);
        openUploadWindow({types: [UploadInputTypes.image]}).then(files => {
            if (this.avatarValidator.validateWithToast(files[0]).failed) return;
            this.users.uploadAvatar(this.currentUser.get('id'), files)
                .pipe(finalize(() => this.loading$.next(false)))
                .subscribe(response => {
                    this.userForm.patchValue({avatar: response.user.avatar});
                    this.currentUser.set('avatar', response.user.avatar);
                    this.avatar$.next(response.user.avatar);
                    this.toast.open('Avatar updated');
                }, (errResponse: BackendErrorResponse) => {
                    const key = Object.keys(errResponse.errors)[0];
                    this.toast.open(errResponse.errors[key]);
                });
        });
    }

    public deleteAvatar() {
        this.loading$.next(true);
        this.users.deleteAvatar(this.currentUser.get('id'))
            .pipe(finalize(() => this.loading$.next(false)))
            .subscribe(user => {
                this.userForm.patchValue({avatar: user.avatar});
                this.currentUser.set('avatar', user.avatar);
                this.avatar$.next(user.avatar);
                this.toast.open('Avatar removed');
            });
    }

    public changeUserPassword() {
        this.loading$.next(true);
        this.users.changePassword(this.currentUser.get('id'), this.passwordForm.value)
            .pipe(finalize(() => this.loading$.next(false)))
            .subscribe(() => {
                this.toast.open('Password updated');
                this.passwordErrors$.next({});
                this.passwordForm.reset();
                this.currentUser.set('has_password', true);
            }, (errResponse: BackendErrorResponse) => this.passwordErrors$.next(errResponse.errors));
    }

    public changeLanguage(name: string) {
        this.loading$.next(true);
        this.localizations.get(name)
            .pipe(finalize(() => this.loading$.next(false)))
            .subscribe(response => {
                this.i18n.setLocalization(response.localization);
            });
    }

    private loadExtraPanels() {
        if ( ! this.extraPanels || ! this.extraPanels.length) return;
        this.extraPanels.forEach((panelComp: {component: ComponentType<any>}) => {
            const componentFactory = this.componentFactoryResolver.resolveComponentFactory(panelComp.component);
            this.extraPanelRef.clear();
            const componentRef = this.extraPanelRef.createComponent(componentFactory);
            componentRef.instance.user = this.initialUser$.value;
            this.cd.detectChanges();
        });
    }

    public apiEnabled(): boolean {
        return this.settings.get('api.integrated') && this.currentUser.hasPermission('api.access');
    }
}

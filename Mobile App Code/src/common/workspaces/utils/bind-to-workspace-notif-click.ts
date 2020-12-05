import { NotificationService } from '../../notifications/notification-list/notification.service';
import { filter } from 'rxjs/operators';
import { WORKSPACE_INVITE_NOTIF_TYPE, WorkspaceInviteNotif } from '../types/workspace-invite-notif';

export function bindToWorkspaceNotificationClick(notifications: NotificationService) {
    return notifications.clickedOnNotification$
        .pipe(filter(data => data.notification.type === WORKSPACE_INVITE_NOTIF_TYPE))
        .subscribe(data => {
            const inviteId = (data.notification as WorkspaceInviteNotif).data.inviteId;
            if (data.action.action === 'join') {
                this.workspaces.join(inviteId).subscribe(response => {
                    this.notifications.delete([data.notification]).subscribe();
                    this.workspaces.pushAndSelect(response.workspace);
                    this.toast.open('Joined workspace.');
                });
            } else if (data.action.action === 'decline') {
                this.workspaces.deleteInvite(inviteId).subscribe(() => {
                    this.notifications.delete([data.notification]).subscribe();
                    this.toast.open('Declined workspace invite.');
                });
            }
        });
}

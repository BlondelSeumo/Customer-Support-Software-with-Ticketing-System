import {NgModule} from '@angular/core';
import {Routes, RouterModule} from '@angular/router';
import {AuthGuard} from '@common/guards/auth-guard.service';
import {NOT_FOUND_ROUTES} from '@common/pages/not-found-routes';

const routes: Routes = [
    {path: '', redirectTo: 'help-center', pathMatch: 'full'},
    {path: 'mailbox', loadChildren: () => import('src/app/ticketing/ticketing.module').then(m => m.TicketingModule)},
    {path: 'admin', loadChildren: () => import('src/app/admin/app-admin.module').then(m => m.AppAdminModule), canLoad: [AuthGuard]},
    {path: 'notifications', loadChildren: () => import('@common/notifications/notifications.module').then(m => m.NotificationsModule), canLoad: [AuthGuard]},
    {path: 'billing', loadChildren: () => import('@common/billing/billing.module').then(m => m.BillingModule)},
    ...NOT_FOUND_ROUTES,
];

@NgModule({
    imports: [RouterModule.forRoot(routes)],
    exports: [RouterModule]
})
export class AppRoutingModule {
}

import {HttpEvent, HttpHandler, HttpInterceptor, HttpRequest} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable} from 'rxjs';
import {EchoService} from './echo.service';

/**
 * An http interceptor to automatically add the laravel echo socket ID header, use this as something like
 *
 * ```js
 * @NgModule({
 *   ...
 *   providers: [
 *     ...
 *     { provide: HTTP_INTERCEPTORS, useClass: EchoInterceptor, multi: true }
 *     ...
 *   ]
 *   ...
 * })
 * ```
 */
@Injectable({
    providedIn: 'root',
})
export class EchoInterceptor implements HttpInterceptor {
    constructor(private backendEvents: EchoService) {
    }

    intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
        const socketId = this.backendEvents.connected && this.backendEvents.echo.socketId();
        if (socketId) {
            req = req.clone({headers: req.headers.append('X-Socket-ID', socketId)});
        }

        return next.handle(req);
    }
}

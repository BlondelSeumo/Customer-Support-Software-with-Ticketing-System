<?php

namespace App\Http\Middleware;

use Common\Core\BaseVerifyCsrfToken;

class VerifyCsrfToken extends BaseVerifyCsrfToken
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'update',
        'secure/update/run',
        'secure/auth/login',
        'secure/auth/register',
        'secure/auth/logout',
        'secure/broadcasting/auth',
        'secure/auth/password/email',
        'tickets/mail/incoming',
        'tickets/mail/failed',

        //TODO: temp. figure out why token mismatch error
        //is sometimes thrown when trying to create a new ticket
        'secure/tickets'
    ];
}

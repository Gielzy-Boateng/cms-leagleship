<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateOnceWithBasicAuth
{
    public function __construct(protected AuthFactory $auth) {}

    /**
     * Authenticate this request with HTTP basic credentials, without a session.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, ?string $guard = null, ?string $field = null): Response
    {
        $sessionGuard = $this->auth->guard($guard);

        if (! $sessionGuard instanceof SessionGuard) {
            abort(401);
        }

        if ($response = $sessionGuard->onceBasic($field ?: 'email')) {
            return $response;
        }

        return $next($request);
    }
}

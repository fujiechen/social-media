<?php

namespace App\Admin\Middlewares;

use Closure;
use Illuminate\Http\Request;

class AuthenticateAccount
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $accountId = $request->session()->get('account_id');
        if (is_null($accountId)) {
            return redirect('/admin/accounts/available');
        }

        return $next($request);
    }
}

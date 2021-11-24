<?php

namespace App\Http\Middleware;

use MongoDB\Client as DB;
use Closure;
use Illuminate\Http\Request;


class UserAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $jwt = $request->bearerToken();
        $db = (new DB)->SocialSite->users;

        if ($db->findOne(['jwt_token' => $jwt])) {
            return $next($request);
        } else {
            return response()->error(['message' => 'UnAuthorized User'], 401);
        }
    }
}

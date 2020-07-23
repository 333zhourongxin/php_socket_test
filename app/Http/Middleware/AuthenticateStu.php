<?php

namespace App\Http\Middleware;
use Illuminate\Http\Request;

use Closure;


class AuthenticateStu
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function handle(Request$request, Closure $next)
    {
        if ($request->session()->get('stu_info')) {
            return $next($request);
        }
        return redirect(route('stulogin', ['examin_id'=>$request['examin_id']]));
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class CheckUserSession
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
//        dd(env('MARKETPLACE_URL'));
        if (Session::has('user_id')) {
            return $next($request);
        } else {
            $url = env('MARKETPLACE_URL', 'https://takeofflite.com/');
            return Redirect::to($url);
        }
    }
}

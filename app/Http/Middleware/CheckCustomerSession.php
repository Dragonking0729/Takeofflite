<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class CheckCustomerSession
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
        $url = env('MARKETPLACE_URL', 'https://takeofflite.com/');
        if (session('share_id') && session('type') === 'customer_portal') {
            if (session('share_id') == session('passcode')) {
                return $next($request);
            } else {
                Redirect::to($url);
            }
        } else {
            return Redirect::to($url);
        }
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\UserTeams;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class CheckIsUserAuthorized
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
//        dd(env('JWT_SECRET_KEY'));
        $secret = env('JWT_SECRET_KEY', 'abc123');
        $url = env('MARKETPLACE_URL', 'https://takeofflite.com');

        $token = $request->route('token');
        // test token token user_id = 1, secret = abc123
//        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoxfQ.xpCS8TTq1a53OIps1ByTdm6Sh-A1ZoCId3e2YYWjapU';
        // invalid token
//        $token = 'invalidtoken';
//        dd($token);

        // split the token
        $tokenParts = explode('.', $token);
        if (count($tokenParts) === 3) {
            $header = $this->base64url_decode($tokenParts[0]);
            $payload = $this->base64url_decode($tokenParts[1]);
            $signatureProvided = $tokenParts[2];

            // get user id from payload
            $user_id = json_decode($payload)->user_id;

            // build a signature based on the header and payload using the secret
            $base64UrlHeader = $this->base64url_encode($header);
            $base64UrlPayload = $this->base64url_encode($payload);
            $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
            $base64UrlSignature = $this->base64url_encode($signature);

            $signatureValid = ($base64UrlSignature === $signatureProvided);
            if ($signatureValid) {
                $updated_user_id = $this->checkTeamMember($user_id);
                $user = User::find($updated_user_id);
                $company_name = $user->company_name ? $user->company_name : '';
                Session::put('user_id', $updated_user_id);
                Session::put('company_name', $company_name);
                return $next($request);
            } else {
                return Redirect::to($url);
            }
        } else {
            return Redirect::to($url);
        }


    }

    // multi-user idea
    private function checkTeamMember($user_id)
    {
        $team_member = UserTeams::where('user_id', $user_id)->first();
        if ($team_member === null || $team_member->is_master) {
            // not exists in team or master - owner of db
            return $user_id;
        } else {
            // team member
            $master = UserTeams::where('team_id', $team_member->team_id)->where('is_master', '1')->first();
            $master_id = $master->user_id;
            return $master_id;
        }
    }

    public function base64url_encode($s)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($s));
    }

    public function base64url_decode($s)
    {
        return base64_decode(str_replace(['-', '_', ''], ['+', '/', '='], $s));
    }

}

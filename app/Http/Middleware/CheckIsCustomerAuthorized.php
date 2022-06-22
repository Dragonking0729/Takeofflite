<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class CheckIsCustomerAuthorized
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
        // $secret = env('CUSTOMER_SECRET_KEY');
        // $url = env('MARKETPLACE_URL', 'https://takeofflite.com');

        // $token = $request->route('token');

        // // split the token
        // $tokenParts = explode('.', $token);
        // if (count($tokenParts) === 3) {
        //     $header = base64url_decode($tokenParts[0]);
        //     $payload = base64url_decode($tokenParts[1]);
        //     $signatureProvided = $tokenParts[2];

        //     // get user id from payload
        //     $type = json_decode($payload)->type;
        //     $user_id = json_decode($payload)->user_id;
        //     $project_id = json_decode($payload)->project_id;

        //     // build a signature based on the header and payload using the secret
        //     $base64UrlHeader = base64url_encode($header);
        //     $base64UrlPayload = base64url_encode($payload);
        //     $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        //     $base64UrlSignature = base64url_encode($signature);

        //     $signatureValid = ($base64UrlSignature === $signatureProvided);
        //     if ($signatureValid) {
        //         Session::put([
        //             'type' => $type, // customer_portal
        //             'share_id' => $user_id,
        //             'project_id' => $project_id,
        //             'passcode' => ''
        //         ]);
        //         return $next($request);
        //     } else {
        //         return Redirect::to($url);
        //     }
        // } else {
        //     return Redirect::to($url);
        // }
    }
}

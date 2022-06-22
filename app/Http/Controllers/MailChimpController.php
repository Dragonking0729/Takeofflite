<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;

class MailChimpController extends Controller
{
    private $MAILCHIMP_API_KEY = '';
    private $MAILCHIMP_SERVER_PREFIX = '';

    public function __construct()
    {
        $this->MAILCHIMP_API_KEY = env('MAILCHIMP_API_KEY');
        $this->MAILCHIMP_SERVER_PREFIX = env('MAILCHIMP_SERVER_PREFIX');
    }

    public function index(Request $request)
    {
        try {
            $mailchimp = new \MailchimpMarketing\ApiClient();

            $mailchimp->setConfig([
                'apiKey' => $this->MAILCHIMP_API_KEY,
                'server' => $this->MAILCHIMP_SERVER_PREFIX,
            ]);

            $response = $mailchimp->ping->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Created successfully',
                'data' =>$response
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


}

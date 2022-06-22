<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AtomApi
{

    public function fetchAtomProperty($address1 = '', $address2 = '')
    {
        $curl = curl_init();
        $api_key = env('ATOM_API_KEY');
        $url = "https://api.gateway.attomdata.com/propertyapi/v1.0.0/property/detail?address1=" . urlencode($address1) . "&address2=" . urlencode($address2);
//        Log::info("ATOM API URL..." . $url);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                'apikey: ' . $api_key
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "Error #:" . $err;
        } else {
            return json_decode($response, true);
        }
    }


    // comparable sales
    public function comparableSales($param)
    {
        $curl = curl_init();
        $api_key = env('ATOM_API_KEY');
        $url = "https://api.gateway.attomdata.com/propertyapi/v1.0.0/sale/detail?postalCode=" . $param;
//        Log::info("ATOM COMPARABLE SALES API URL..." . $url);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                'apikey: ' . $api_key
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            Log::error("ERROR ATTOM comparableSales API..." . $err);
            return "Error #:" . $err;
        } else {
            return json_decode($response, true);
        }
    }

}
